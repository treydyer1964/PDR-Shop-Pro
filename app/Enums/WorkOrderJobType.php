<?php

namespace App\Enums;

enum WorkOrderJobType: string
{
    case Insurance   = 'insurance';
    case CustomerPay = 'customer_pay';
    case Wholesale   = 'wholesale';

    public function label(): string
    {
        return match($this) {
            self::Insurance   => 'Insurance',
            self::CustomerPay => 'Customer Pay',
            self::Wholesale   => 'Wholesale',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::Insurance   => 'bg-blue-100 text-blue-700',
            self::CustomerPay => 'bg-green-100 text-green-700',
            self::Wholesale   => 'bg-purple-100 text-purple-700',
        };
    }

    public function isInsurance(): bool
    {
        return $this === self::Insurance;
    }
}
