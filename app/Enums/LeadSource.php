<?php

namespace App\Enums;

enum LeadSource: string
{
    case DoorToDoor = 'door_to_door';
    case Referral   = 'referral';
    case WalkIn     = 'walk_in';
    case Phone      = 'phone';
    case Online     = 'online';

    public function label(): string
    {
        return match($this) {
            self::DoorToDoor => 'Door-to-Door',
            self::Referral   => 'Referral',
            self::WalkIn     => 'Walk-In',
            self::Phone      => 'Phone',
            self::Online     => 'Online',
        };
    }
}
