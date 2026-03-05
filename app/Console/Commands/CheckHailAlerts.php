<?php

namespace App\Console\Commands;

use App\Models\HailAlertLog;
use App\Models\HailAlertSubscription;
use App\Models\HailEvent;
use App\Models\User;
use App\Notifications\HailAlertNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckHailAlerts extends Command
{
    protected $signature = 'hail:check-alerts
                            {--date= : Check a specific date (YYYY-MM-DD, defaults to today)}
                            {--dry-run : Show what would be sent without sending}';

    protected $description = 'Check hail events against tenant subscriptions and send alerts';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        $dryRun = (bool) $this->option('dry-run');

        $subscriptions = HailAlertSubscription::where('active', true)
            ->whereNotNull('home_lat')
            ->whereNotNull('home_lng')
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->line('No active subscriptions with home base set.');
            return self::SUCCESS;
        }

        $this->info("Checking alerts for {$date->toDateString()} ({$subscriptions->count()} subscriptions)...");

        // Load all hail events for the target date
        $events = HailEvent::whereDate('event_date', $date)->get();

        if ($events->isEmpty()) {
            $this->line("No hail events found for {$date->toDateString()}.");
            return self::SUCCESS;
        }

        foreach ($subscriptions as $sub) {
            $this->checkSubscription($sub, $events, $date, $dryRun);
        }

        return self::SUCCESS;
    }

    // ── Per-subscription check ────────────────────────────────────────────────────

    private function checkSubscription(
        HailAlertSubscription $sub,
        $events,
        Carbon $date,
        bool $dryRun
    ): void {
        $qualifying = [];

        foreach ($events as $event) {
            // Filter by min size
            if ($event->max_size_inches < $sub->min_size_inches) {
                continue;
            }

            // Filter by radius
            $distanceMiles = $this->haversine(
                $sub->home_lat, $sub->home_lng,
                $event->centroid_lat, $event->centroid_lng
            );

            if ($distanceMiles > $sub->radius_miles) {
                continue;
            }

            // Check cooldown — skip if already alerted within cooldown_hours for this event
            $alreadyAlerted = HailAlertLog::where('tenant_id', $sub->tenant_id)
                ->where('hail_event_id', $event->id)
                ->where('delivery_method', 'email')
                ->where('triggered_at', '>=', now()->subHours($sub->alert_cooldown_hours))
                ->exists();

            if ($alreadyAlerted) {
                $this->line("  [tenant:{$sub->tenant_id}] Already alerted for event #{$event->id} within cooldown.");
                continue;
            }

            $qualifying[] = [
                'max_size_inches' => $event->max_size_inches,
                'size_label'      => $event->sizeLabel(),
                'location'        => $event->locationLabel(),
                'report_count'    => $event->report_count,
                'distance_miles'  => round($distanceMiles, 1),
                'event_date'      => $event->event_date->toDateString(),
                'tracker_url'     => route('hail-tracker.index') . '?selectedDate=' . $event->event_date->toDateString(),
                '_event_id'       => $event->id,
            ];
        }

        if (empty($qualifying)) {
            $this->line("  [tenant:{$sub->tenant_id}] No qualifying events.");
            return;
        }

        // Sort by size descending
        usort($qualifying, fn($a, $b) => $b['max_size_inches'] <=> $a['max_size_inches']);

        $this->info("  [tenant:{$sub->tenant_id}] {$qualifying[0]['max_size_inches']}\" hail within radius — " . count($qualifying) . " event(s).");

        if ($dryRun) {
            $this->warn("  [dry-run] Would send email alert.");
            return;
        }

        if ($sub->email_alerts) {
            $this->sendEmailAlerts($sub, $qualifying);
        }
    }

    // ── Email delivery ────────────────────────────────────────────────────────────

    private function sendEmailAlerts(HailAlertSubscription $sub, array $events): void
    {
        // Recipients: Owner, Bookkeeper, Sales Manager for this tenant
        $recipients = User::where('tenant_id', $sub->tenant_id)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['owner', 'bookkeeper', 'sales_manager']))
            ->where('active', true)
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn("  [tenant:{$sub->tenant_id}] No eligible recipients found.");
            return;
        }

        $eventIds = array_column($events, '_event_id');

        foreach ($recipients as $user) {
            try {
                $user->notify(new HailAlertNotification($sub, $events));

                // Log one entry per event per recipient
                foreach ($eventIds as $eventId) {
                    HailAlertLog::create([
                        'tenant_id'      => $sub->tenant_id,
                        'hail_event_id'  => $eventId,
                        'triggered_at'   => now(),
                        'delivery_method'=> 'email',
                        'recipient'      => $user->email,
                    ]);
                }

                $this->line("  Emailed {$user->email}");
            } catch (\Exception $e) {
                $this->error("  Failed to email {$user->email}: " . $e->getMessage());
            }
        }
    }

    // ── Haversine distance ────────────────────────────────────────────────────────

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 3958.8;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * asin(sqrt($a));
    }
}
