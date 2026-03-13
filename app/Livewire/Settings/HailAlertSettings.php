<?php

namespace App\Livewire\Settings;

use App\Models\HailAlertLog;
use App\Models\HailAlertSubscription;
use App\Notifications\HailAlertNotification;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HailAlertSettings extends Component
{
    public string $homeAddress       = '';
    public string $homeLat           = '';
    public string $homeLng           = '';
    public int    $radiusMiles       = 150;
    public string $minSizeInches     = '1.00';
    public bool   $emailAlerts       = true;
    public bool   $smsAlerts         = false;
    public int    $alertCooldownHours= 4;
    public bool   $active            = true;

    public ?string $savedMessage  = null;
    public ?string $geocodeError  = null;
    public bool    $geocoding     = false;
    public bool    $testSent      = false;

    public function mount(): void
    {
        $sub = HailAlertSubscription::where('tenant_id', auth()->user()->tenant_id)->first();

        if ($sub) {
            $this->homeAddress        = $sub->home_address        ?? '';
            $this->homeLat            = $sub->home_lat !== null   ? (string) $sub->home_lat : '';
            $this->homeLng            = $sub->home_lng !== null   ? (string) $sub->home_lng : '';
            $this->radiusMiles        = $sub->radius_miles;
            $this->minSizeInches      = (string) $sub->min_size_inches;
            $this->emailAlerts        = $sub->email_alerts;
            $this->smsAlerts          = $sub->sms_alerts;
            $this->alertCooldownHours = $sub->alert_cooldown_hours;
            $this->active             = $sub->active;
        }
    }

    // ── Geocode ───────────────────────────────────────────────────────────────────

    public function geocodeAddress(): void
    {
        $this->geocodeError = null;

        if (blank($this->homeAddress)) {
            $this->geocodeError = 'Enter an address first.';
            return;
        }

        $this->geocoding = true;

        try {
            $response = Http::timeout(6)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $this->homeAddress,
                'key'     => config('services.google.geocoding_key'),
            ]);

            $results = $response->json('results') ?? [];

            if (empty($results)) {
                $this->geocodeError = 'Address not found. Try a more specific address.';
            } else {
                $loc               = $results[0]['geometry']['location'];
                $this->homeLat     = $loc['lat'];
                $this->homeLng     = $loc['lng'];
                $this->homeAddress = $results[0]['formatted_address'];
            }
        } catch (\Exception $e) {
            $this->geocodeError = 'Geocoding failed. Check your connection and try again.';
        }

        $this->geocoding = false;
    }

    // ── Save ──────────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->savedMessage = null;

        $data = [
            'home_address'         => $this->homeAddress ?: null,
            'home_lat'             => $this->homeLat !== '' ? (float) $this->homeLat : null,
            'home_lng'             => $this->homeLng !== '' ? (float) $this->homeLng : null,
            'radius_miles'         => $this->radiusMiles,
            'min_size_inches'      => (float) $this->minSizeInches,
            'email_alerts'         => $this->emailAlerts,
            'sms_alerts'           => $this->smsAlerts,
            'alert_cooldown_hours' => $this->alertCooldownHours,
            'active'               => $this->active,
        ];

        HailAlertSubscription::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id],
            $data
        );

        $this->savedMessage = 'Alert settings saved.';
    }

    // ── Test alert ────────────────────────────────────────────────────────────────

    public function sendTestAlert(): void
    {
        $this->testSent = false;

        $sub = HailAlertSubscription::firstOrCreate(
            ['tenant_id' => auth()->user()->tenant_id],
            [
                'radius_miles'         => $this->radiusMiles,
                'min_size_inches'      => (float) $this->minSizeInches,
                'email_alerts'         => true,
                'alert_cooldown_hours' => $this->alertCooldownHours,
                'active'               => true,
            ]
        );

        $testEvents = [[
            'max_size_inches' => 1.75,
            'size_label'      => 'Golf Ball (Test)',
            'location'        => 'Test City, TX',
            'report_count'    => 7,
            'distance_miles'  => 42.3,
            'event_date'      => now()->toDateString(),
            'tracker_url'     => route('hail-tracker.index'),
        ]];

        auth()->user()->notify(new HailAlertNotification($sub, $testEvents));

        $this->testSent = true;
    }

    // ── Computed ──────────────────────────────────────────────────────────────────

    #[Computed]
    public function recentAlerts(): array
    {
        return HailAlertLog::where('tenant_id', auth()->user()->tenant_id)
            ->with('hailEvent')
            ->orderByDesc('triggered_at')
            ->limit(10)
            ->get()
            ->map(fn($log) => [
                'triggered_at'   => $log->triggered_at->format('M j, Y g:i a'),
                'delivery_method'=> $log->delivery_method,
                'recipient'      => $log->recipient,
                'size'           => $log->hailEvent?->max_size_inches,
                'location'       => $log->hailEvent?->locationLabel(),
            ])
            ->toArray();
    }

    #[Computed]
    public function hasHomeBase(): bool
    {
        return $this->homeLat !== '' && $this->homeLng !== '';
    }

    public function render()
    {
        return view('livewire.settings.hail-alert-settings');
    }
}
