<?php

namespace App\Enums;

enum PhotoCategory: string
{
    case Before   = 'before';
    case During   = 'during';
    case After    = 'after';
    case Document = 'doc';
    case Other    = 'other';

    public function label(): string
    {
        return match($this) {
            self::Before   => 'Before',
            self::During   => 'During',
            self::After    => 'After',
            self::Document => 'Docs',
            self::Other    => 'Other',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Before   => 'bg-orange-100 text-orange-700',
            self::During   => 'bg-blue-100 text-blue-700',
            self::After    => 'bg-green-100 text-green-700',
            self::Document => 'bg-purple-100 text-purple-700',
            self::Other    => 'bg-slate-100 text-slate-600',
        };
    }

    public function tabActiveClasses(): string
    {
        return match($this) {
            self::Before   => 'border-orange-500 text-orange-600',
            self::During   => 'border-blue-500 text-blue-600',
            self::After    => 'border-green-500 text-green-600',
            self::Document => 'border-purple-500 text-purple-600',
            self::Other    => 'border-slate-500 text-slate-600',
        };
    }
}
