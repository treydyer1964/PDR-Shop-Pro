<?php

namespace App\Console\Commands;

use App\Models\HailEvent;
use App\Models\HailReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchSpcReports extends Command
{
    protected $signature = 'hail:fetch-reports
                            {--date= : Specific date to fetch (YYYY-MM-DD)}
                            {--backfill=0 : Number of past days to backfill}
                            {--force : Re-fetch even if data already exists}';

    protected $description = 'Fetch SPC hail storm reports from NOAA and cluster into events';

    const CLUSTER_RADIUS_MILES = 20;
    const SPC_BASE_URL = 'https://www.spc.noaa.gov/climo/reports/';

    public function handle(): int
    {
        foreach ($this->getDatesToFetch() as $date) {
            $this->fetchDate($date);
        }

        return self::SUCCESS;
    }

    // ── SPC convective day ────────────────────────────────────────────────────────

    /**
     * SPC convective day runs 12Z–12Z (noon UTC to noon UTC).
     * Subtracting 12 hours maps midnight UTC to the correct SPC day.
     * e.g. 00:00 UTC March 6 → 12:00 UTC March 5 → SPC day = March 5
     */
    private function spcDay(): Carbon
    {
        return now()->subHours(12)->startOfDay();
    }

    /**
     * SPC URL for a given date.
     * - Current SPC day  → today_filtered_hail.csv   (live, updates continuously)
     * - Previous SPC day → yesterday_filtered_hail.csv (live until 12Z)
     * - Older dates      → YYMMDD_rpts_hail.csv       (finalized archive)
     */
    private function spcUrl(Carbon $date): string
    {
        $spcToday     = $this->spcDay();
        $spcYesterday = (clone $spcToday)->subDay();

        if ($date->isSameDay($spcToday)) {
            return self::SPC_BASE_URL . 'today_filtered_hail.csv';
        }

        if ($date->isSameDay($spcYesterday)) {
            return self::SPC_BASE_URL . 'yesterday_filtered_hail.csv';
        }

        return self::SPC_BASE_URL . $date->format('ymd') . '_rpts_hail.csv';
    }

    // ── Date resolution ───────────────────────────────────────────────────────────

    private function getDatesToFetch(): array
    {
        if ($days = (int) $this->option('backfill')) {
            $spcToday = $this->spcDay();
            return collect(range(0, $days - 1))
                ->map(fn($d) => (clone $spcToday)->subDays($d))
                ->all();
        }

        if ($dateStr = $this->option('date')) {
            return [Carbon::parse($dateStr)->startOfDay()];
        }

        return [$this->spcDay()];
    }

    // ── Fetch a single date ───────────────────────────────────────────────────────

    private function fetchDate(Carbon $date): void
    {
        $dateStr  = $date->format('Y-m-d');
        $spcToday = $this->spcDay();
        $isToday  = $date->isSameDay($spcToday);

        // Current SPC day always re-fetches (updates continuously).
        // Historical dates skip if already stored, unless --force.
        if (!$isToday && !$this->option('force') && HailReport::whereDate('report_date', $date)->exists()) {
            $this->line("Skipping {$dateStr} — already in DB");
            return;
        }

        $url = $this->spcUrl($date);

        $this->info("Fetching {$url}");

        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'PDRShopPro/2.0 hail-tracker (contact@pdrshoppro.com)'])
                ->get($url);

            if ($response->failed()) {
                $this->warn("No data for {$dateStr} (HTTP {$response->status()})");
                return;
            }

            $body = trim($response->body());
            if (empty($body)) {
                $this->warn("Empty response for {$dateStr}");
                return;
            }

            $parsed  = [];
            $skipped = 0;
            $lines   = array_filter(explode("\n", $body));

            foreach ($lines as $i => $line) {
                if ($i === 0) continue; // header row

                $cols = str_getcsv(trim($line));
                // Actual SPC columns: Time, Size, Location, County, State, Lat, Lon[, Comments]
                if (count($cols) < 7) {
                    $skipped++;
                    continue;
                }

                $time     = $cols[0];
                $size     = $cols[1];
                $location = $cols[2] ?? null;
                $county   = $cols[3] ?? null;
                $state    = $cols[4] ?? null;
                $lat      = $cols[5];
                $lng      = $cols[6];

                $latF   = (float) $lat;
                $lngF   = (float) $lng;
                $sizeIn = round((float) $size / 100, 2);

                // Skip zero-coordinate or zero/negative-size rows
                if (($latF === 0.0 && $lngF === 0.0) || $sizeIn <= 0) {
                    $skipped++;
                    continue;
                }

                $parsed[] = [
                    'hail_event_id' => null,
                    'report_date'   => $dateStr,
                    'report_time'   => $this->parseTime($time),
                    'lat'           => $latF,
                    'lng'           => $lngF,
                    'size_inches'   => $sizeIn,
                    'location_name' => trim($location) ?: null,
                    'county'        => trim($county) ?: null,
                    'state'         => strtoupper(trim($state)) ?: null,
                    'source'        => 'spc',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (empty($parsed)) {
                $this->warn("No valid hail reports for {$dateStr}");
                return;
            }

            // Replace existing reports for this date and re-cluster
            $existingEventIds = HailEvent::whereDate('event_date', $date)->pluck('id');
            HailReport::whereIn('hail_event_id', $existingEventIds)->update(['hail_event_id' => null]);
            HailEvent::whereIn('id', $existingEventIds)->delete();
            HailReport::whereDate('report_date', $date)->delete();

            HailReport::insert($parsed);

            $count = count($parsed);
            $this->info("Stored {$count} reports for {$dateStr}" . ($skipped ? " ({$skipped} skipped)" : ''));

            $this->clusterDate($date);

        } catch (\Exception $e) {
            $this->error("Failed to fetch {$dateStr}: " . $e->getMessage());
        }
    }

    // ── Spatial clustering ────────────────────────────────────────────────────────

    private function clusterDate(Carbon $date): void
    {
        $reports = HailReport::whereDate('report_date', $date)
            ->orderByDesc('size_inches')
            ->get(['id', 'lat', 'lng', 'size_inches', 'county', 'state'])
            ->toArray();

        if (empty($reports)) return;

        $clusters = [];

        foreach ($reports as $report) {
            $assigned = false;

            foreach ($clusters as &$cluster) {
                $dist = $this->haversine($report['lat'], $report['lng'], $cluster['lat'], $cluster['lng']);

                if ($dist <= self::CLUSTER_RADIUS_MILES) {
                    $cluster['lats'][]      = $report['lat'];
                    $cluster['lngs'][]      = $report['lng'];
                    $cluster['lat']         = array_sum($cluster['lats']) / count($cluster['lats']);
                    $cluster['lng']         = array_sum($cluster['lngs']) / count($cluster['lngs']);
                    $cluster['min_size']    = min($cluster['min_size'], $report['size_inches']);
                    $cluster['report_ids'][] = $report['id'];
                    // Track dominant state/county by frequency
                    $cluster['states'][]    = $report['state'];
                    $assigned = true;
                    break;
                }
            }
            unset($cluster);

            if (!$assigned) {
                $clusters[] = [
                    'lat'        => $report['lat'],
                    'lng'        => $report['lng'],
                    'lats'       => [$report['lat']],
                    'lngs'       => [$report['lng']],
                    'max_size'   => $report['size_inches'],
                    'min_size'   => $report['size_inches'],
                    'states'     => [$report['state']],
                    'county'     => $report['county'],
                    'report_ids' => [$report['id']],
                ];
            }
        }

        foreach ($clusters as $cluster) {
            $count = count($cluster['report_ids']);

            // Coverage radius = max distance from centroid to any member
            $coverageRadius = 0.0;
            foreach (array_map(null, $cluster['lats'], $cluster['lngs']) as [$rLat, $rLng]) {
                $d = $this->haversine($cluster['lat'], $cluster['lng'], $rLat, $rLng);
                $coverageRadius = max($coverageRadius, $d);
            }

            // Dominant state
            $stateCounts  = array_count_values(array_filter($cluster['states']));
            $primaryState = $stateCounts ? array_key_first($stateCounts) : null;

            $event = HailEvent::create([
                'event_date'            => $date->toDateString(),
                'centroid_lat'          => round($cluster['lat'], 5),
                'centroid_lng'          => round($cluster['lng'], 5),
                'max_size_inches'       => $cluster['max_size'],
                'min_size_inches'       => $cluster['min_size'],
                'report_count'          => $count,
                'coverage_radius_miles' => round(max($coverageRadius, 1.0), 1),
                'primary_state'         => $primaryState,
                'primary_county'        => $cluster['county'],
            ]);

            HailReport::whereIn('id', $cluster['report_ids'])->update(['hail_event_id' => $event->id]);
        }

        $this->info("Clustered into " . count($clusters) . " event(s) for {$date->toDateString()}");
    }

    // ── Utilities ─────────────────────────────────────────────────────────────────

    private function parseTime(string $raw): ?string
    {
        $raw = trim($raw);
        if (preg_match('/^\d{4}$/', $raw)) {
            return substr($raw, 0, 2) . ':' . substr($raw, 2) . ':00';
        }
        return null;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 3958.8; // Earth radius in miles
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * asin(sqrt($a));
    }
}
