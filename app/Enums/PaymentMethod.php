<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Check = 'CHECK';
    case Card  = 'CARD';
    case Cash  = 'CASH';
    case Ach   = 'ACH';
    case Other = 'OTHER';

    public function label(): string
    {
        return match($this) {
            self::Check => 'Check',
            self::Card  => 'Card',
            self::Cash  => 'Cash',
            self::Ach   => 'ACH',
            self::Other => 'Other',
        };
    }
}
