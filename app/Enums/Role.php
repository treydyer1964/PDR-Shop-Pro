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
