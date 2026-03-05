<?php

namespace App\Notifications;

use App\Models\HailAlertSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HailAlertNotification extends Notification
{
    use Queueable;

    /**
     * @param HailAlertSubscription $subscription
     * @param array $events  Formatted event data — shape:
     *   [max_size_inches, size_label, location, report_count, distance_miles, event_date, tracker_url]
     */
    public function __construct(
        public readonly HailAlertSubscription $subscription,
        public readonly array $events,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $maxSize  = collect($this->events)->max('max_size_inches');
        $location = collect($this->events)->first()['location'] ?? 'your area';
        $date     = collect($this->events)->first()['event_date'] ?? now()->toDateString();
        $count    = count($this->events);
        $subject  = sprintf(
            'Hail Alert — %.2f" hail near %s',
            $maxSize,
            $location
        );

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.hail-alert', [
                'events'          => $this->events,
                'homeAddress'     => $this->subscription->home_address,
                'radiusMiles'     => $this->subscription->radius_miles,
                'minSizeInches'   => $this->subscription->min_size_inches,
                'cooldownHours'   => $this->subscription->alert_cooldown_hours,
                'date'            => $date,
                'eventCount'      => $count,
                'trackerUrl'      => route('hail-tracker.index') . '?selectedDate=' . $date,
            ]);
    }
}
