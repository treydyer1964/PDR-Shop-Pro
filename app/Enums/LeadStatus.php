<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New            = 'new';
    case Contacted      = 'contacted';
    case AppointmentSet = 'appointment_set';
    case NoAnswer       = 'no_answer';
    case NotInterested  = 'not_interested';
    case Converted      = 'converted';
    case Lost           = 'lost';

    public function label(): string
    {
        return match($this) {
            self::New            => 'New',
            self::Contacted      => 'Contacted',
            self::AppointmentSet => 'Appt Set',
            self::NoAnswer       => 'No Answer',
            self::NotInterested  => 'Not Interested',
            self::Converted      => 'Converted',
            self::Lost           => 'Lost',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::New            => 'bg-blue-100 text-blue-700',
            self::Contacted      => 'bg-yellow-100 text-yellow-700',
            self::AppointmentSet => 'bg-green-100 text-green-700',
            self::NoAnswer       => 'bg-slate-100 text-slate-600',
            self::NotInterested  => 'bg-red-100 text-red-700',
            self::Converted      => 'bg-purple-100 text-purple-700',
            self::Lost           => 'bg-slate-200 text-slate-500',
        };
    }

    public function dotClasses(): string
    {
        return match($this) {
            self::New            => 'bg-blue-500',
            self::Contacted      => 'bg-yellow-500',
            self::AppointmentSet => 'bg-green-500',
            self::NoAnswer       => 'bg-slate-400',
            self::NotInterested  => 'bg-red-500',
            self::Converted      => 'bg-purple-500',
            self::Lost           => 'bg-slate-500',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Converted, self::Lost, self::NotInterested]);
    }
}
