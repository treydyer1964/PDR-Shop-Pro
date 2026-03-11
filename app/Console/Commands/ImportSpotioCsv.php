<?php

namespace App\Console\Commands;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\StormType;
use App\Models\Lead;
use App\Models\StormEvent;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportSpotioCsv extends Command
{
    protected $signature = 'leads:import-spotio
                            {file : Path to the Spotio CSV export}
                            {--tenant-id= : Tenant ID to import under (defaults to first tenant)}
                            {--storm= : Storm event name to assign (created if it does not exist)}
                            {--dry-run : Preview counts without inserting}
                            {--skip-dupes : Skip rows where lat+lng already exists for this tenant}';

    protected $description = 'Import Contract leads from a Spotio CSV export';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        // Resolve tenant ID
        $tenantId = (int) ($this->option('tenant-id') ?? Tenant::first()?->id);
        if (! $tenantId) {
            $this->error('No tenant found. Pass --tenant-id=X');
            return 1;
        }

        // Resolve a "created_by" user (first owner of the tenant)
        $createdBy = User::where('tenant_id', $tenantId)->orderBy('id')->value('id');

        // Resolve / create storm event
        $stormEventId = null;
        if ($stormName = $this->option('storm')) {
            $storm = StormEvent::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $stormName],
                [
                    'event_date' => now()->toDateString(),
                    'storm_type' => StormType::Hail->value,
                    'city'       => 'Abilene',
                    'state'      => 'TX',
                ]
            );
            $stormEventId = $storm->id;
            $verb = $storm->wasRecentlyCreated ? 'Created' : 'Found';
            $this->info("{$verb} storm event \"{$stormName}\" (ID {$stormEventId})");
        }

        $dryRun    = $this->option('dry-run');
        $skipDupes = $this->option('skip-dupes');

        // Load existing lat/lng pairs if deduping
        $existingCoords = [];
        if ($skipDupes) {
            Lead::where('tenant_id', $tenantId)
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->select(['lat', 'lng'])
                ->each(function ($r) use (&$existingCoords) {
                    $existingCoords[round($r->lat, 5) . ',' . round($r->lng, 5)] = true;
                });
        }

        $handle = fopen($file, 'r');

        // Skip Excel's "sep=," hint line if present
        $firstLine = fgets($handle);
        if (! str_starts_with(trim($firstLine), 'sep=')) {
            // Not a sep line — rewind and re-read as CSV
            rewind($handle);
        }

        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);
        $col     = array_flip($headers);

        $total    = 0;
        $imported = 0;
        $skipped  = 0;
        $dupes    = 0;

        $now  = now();
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $total++;
            $get = fn (string $key) => isset($col[$key]) ? trim($row[$col[$key]] ?? '') : '';

            if (strtolower($get('Last Visit Result')) !== 'contract') {
                $skipped++;
                continue;
            }

            $lat = (float) $get('Latitude');
            $lng = (float) $get('Longitude');

            if ($skipDupes && isset($existingCoords[round($lat, 5) . ',' . round($lng, 5)])) {
                $dupes++;
                continue;
            }

            $address = $get('Address (house # & street name)');
            if (empty($address)) {
                $address = trim($get('House #') . ' ' . $get('Street'));
            }

            $createdAt = $this->parseDate($get('Creation date'));
            $updatedAt = $this->parseDate($get('Updated date')) ?? $createdAt;

            $rows[] = [
                'tenant_id'         => $tenantId,
                'status'            => LeadStatus::Contract->value,
                'source'            => LeadSource::DoorToDoor->value,
                'storm_event_id'    => $stormEventId,
                'lat'               => $lat ?: null,
                'lng'               => $lng ?: null,
                'address'           => $address,
                'city'              => $get('City'),
                'state'             => $this->normalizeState($get('State')),
                'zip'               => $get('Zip'),
                'first_name'        => $get('Contact 1 First Name') ?: null,
                'last_name'         => $get('Contact 1 Last Name') ?: null,
                'phone'             => $get('Contact 1 Field Phone') ? substr(preg_replace('/\D/', '', $get('Contact 1 Field Phone')), 0, 20) ?: null : null,
                'email'             => $get('Contact 1 Field Email') ?: null,
                'notes'             => $get('Field Notes') ?: null,
                'vehicle_year'      => $get('Field Vehicle Year') ? substr($get('Field Vehicle Year'), 0, 4) ?: null : null,
                'vehicle_make'      => $get('Field Vehicle Make') ? substr($get('Field Vehicle Make'), 0, 100) ?: null : null,
                'vehicle_model'     => $get('Field Vehicle Model') ? substr($get('Field Vehicle Model'), 0, 100) ?: null : null,
                'damage_level'      => $this->mapDamageLevel($get('Field Hail Severity')),
                'job_type_interest' => 'insurance',
                'created_by'        => $createdBy,
                'created_at'        => $createdAt ?? $now,
                'updated_at'        => $updatedAt ?? $now,
            ];

            $imported++;
        }

        fclose($handle);

        $this->info("Total rows:   {$total}");
        $this->info("Contract:     {$imported}");
        $this->info("Skipped:      {$skipped}");
        if ($skipDupes) {
            $this->info("Dupes:        {$dupes}");
        }

        if ($dryRun) {
            $this->warn('Dry run — nothing inserted.');
            return 0;
        }

        if ($imported === 0) {
            $this->warn('No Contract rows found — nothing to import.');
            return 0;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Lead::insert($chunk);
        }

        $this->info("Inserted {$imported} leads into tenant {$tenantId}.");

        return 0;
    }

    private function parseDate(string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Exception) {
            return null;
        }
    }

    private function mapDamageLevel(string $severity): ?string
    {
        // Spotio can export multi-value like "Light |Medium" or "Small |Medium |Large"
        // Pick the highest severity present
        $order = ['smoked' => 5, 'severe' => 4, 'large' => 4, 'medium' => 3, 'small' => 2, 'light' => 2, 'no damage' => 1];
        $map   = ['smoked' => 'smoked', 'severe' => 'severe', 'large' => 'severe', 'medium' => 'medium', 'small' => 'light', 'light' => 'light', 'no damage' => 'none'];

        $parts = array_map('trim', explode('|', strtolower($severity)));
        $best  = null;
        $bestScore = 0;

        foreach ($parts as $part) {
            $score = $order[$part] ?? 0;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $map[$part] ?? null;
            }
        }

        return $best;
    }

    private function normalizeState(string $state): string
    {
        $states = [
            'alabama' => 'AL', 'alaska' => 'AK', 'arizona' => 'AZ', 'arkansas' => 'AR',
            'california' => 'CA', 'colorado' => 'CO', 'connecticut' => 'CT', 'delaware' => 'DE',
            'florida' => 'FL', 'georgia' => 'GA', 'hawaii' => 'HI', 'idaho' => 'ID',
            'illinois' => 'IL', 'indiana' => 'IN', 'iowa' => 'IA', 'kansas' => 'KS',
            'kentucky' => 'KY', 'louisiana' => 'LA', 'maine' => 'ME', 'maryland' => 'MD',
            'massachusetts' => 'MA', 'michigan' => 'MI', 'minnesota' => 'MN', 'mississippi' => 'MS',
            'missouri' => 'MO', 'montana' => 'MT', 'nebraska' => 'NE', 'nevada' => 'NV',
            'new hampshire' => 'NH', 'new jersey' => 'NJ', 'new mexico' => 'NM', 'new york' => 'NY',
            'north carolina' => 'NC', 'north dakota' => 'ND', 'ohio' => 'OH', 'oklahoma' => 'OK',
            'oregon' => 'OR', 'pennsylvania' => 'PA', 'rhode island' => 'RI', 'south carolina' => 'SC',
            'south dakota' => 'SD', 'tennessee' => 'TN', 'texas' => 'TX', 'utah' => 'UT',
            'vermont' => 'VT', 'virginia' => 'VA', 'washington' => 'WA', 'west virginia' => 'WV',
            'wisconsin' => 'WI', 'wyoming' => 'WY',
        ];

        $trimmed = trim($state);
        return $states[strtolower($trimmed)] ?? strtoupper($trimmed);
    }
}
