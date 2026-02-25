<?php

namespace App\Enums;

enum PaymentSource: string
{
    case Insurance = 'INSURANCE';
    case Customer  = 'CUSTOMER';
    case Other     = 'OTHER';

    public function label(): string
    {
        return match($this) {
            self::Insurance => 'Insurance',
            self::Customer  => 'Customer',
            self::Other     => 'Other',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Insurance => 'bg-blue-100 text-blue-800',
            self::Customer  => 'bg-green-100 text-green-800',
            self::Other     => 'bg-gray-100 text-gray-700',
        };
    }
}
