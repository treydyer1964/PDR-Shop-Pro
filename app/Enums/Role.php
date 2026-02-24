<?php

namespace App\Enums;

enum Role: string
{
    case OWNER          = 'owner';
    case PDR_TECH       = 'pdr_tech';
    case SALES_ADVISOR  = 'sales_advisor';
    case SALES_MANAGER  = 'sales_manager';
    case RI_TECH        = 'ri_tech';
    case PORTER         = 'porter';
    case BOOKKEEPER     = 'bookkeeper';

    public function label(): string
    {
        return match($this) {
            self::OWNER         => 'Owner',
            self::PDR_TECH      => 'PDR Tech',
            self::SALES_ADVISOR => 'Sales Advisor',
            self::SALES_MANAGER => 'Sales Manager',
            self::RI_TECH       => 'R&I Tech',
            self::PORTER        => 'Porter',
            self::BOOKKEEPER    => 'Bookkeeper',
        };
    }

    public function earnsCommission(): bool
    {
        return match($this) {
            self::PDR_TECH, self::SALES_ADVISOR,
            self::SALES_MANAGER, self::RI_TECH, self::PORTER => true,
            default => false,
        };
    }

    /** Whether this role uses a split % on work order assignments */
    public function usesSplitPct(): bool
    {
        return match($this) {
            self::PDR_TECH, self::SALES_ADVISOR => true,
            default => false,
        };
    }

    /** Roles that can be assigned to a work order */
    public static function assignable(): array
    {
        return [
            self::SALES_ADVISOR,
            self::PDR_TECH,
            self::RI_TECH,
            self::PORTER,
        ];
    }

    /** Badge color classes for role chips */
    public function badgeClasses(): string
    {
        return match($this) {
            self::OWNER         => 'bg-purple-100 text-purple-700',
            self::PDR_TECH      => 'bg-blue-100 text-blue-700',
            self::SALES_ADVISOR => 'bg-emerald-100 text-emerald-700',
            self::SALES_MANAGER => 'bg-amber-100 text-amber-700',
            self::RI_TECH       => 'bg-orange-100 text-orange-700',
            self::PORTER        => 'bg-slate-100 text-slate-600',
            self::BOOKKEEPER    => 'bg-pink-100 text-pink-700',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn($role) => [$role->value => $role->label()]
        )->all();
    }
}
