<?php

namespace App\Enums;

enum StormType: string
{
    case Hail  = 'hail';
    case Wind  = 'wind';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Hail  => 'Hail',
            self::Wind  => 'Wind',
            self::Other => 'Other',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Hail  => 'bg-blue-100 text-blue-700',
            self::Wind  => 'bg-yellow-100 text-yellow-800',
            self::Other => 'bg-slate-100 text-slate-600',
        };
    }
}
