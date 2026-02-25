<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled  = 'scheduled';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case NoShow     = 'no_show';

    public function label(): string
    {
        return match($this) {
            self::Scheduled => 'Scheduled',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::NoShow    => 'No-Show',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Scheduled => 'bg-blue-100 text-blue-700',
            self::Completed => 'bg-green-100 text-green-700',
            self::Cancelled => 'bg-slate-100 text-slate-500',
            self::NoShow    => 'bg-red-100 text-red-600',
        };
    }

    public function isTerminal(): bool
    {
        return match($this) {
            self::Completed, self::Cancelled, self::NoShow => true,
            default => false,
        };
    }
}
