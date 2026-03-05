<?php

namespace App\Enums;

enum HailEventStatus: string
{
    case Watching  = 'watching';
    case Passed    = 'passed';
    case Activated = 'activated';

    public function label(): string
    {
        return match($this) {
            self::Watching  => 'Watching',
            self::Passed    => 'Passed',
            self::Activated => 'Activated',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Watching  => 'bg-blue-100 text-blue-700',
            self::Passed    => 'bg-slate-100 text-slate-500',
            self::Activated => 'bg-green-100 text-green-700',
        };
    }
}
