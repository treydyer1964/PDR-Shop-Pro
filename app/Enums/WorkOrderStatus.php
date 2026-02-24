<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case ToBeAcquired        = 'to_be_acquired';
    case Acquired            = 'acquired';
    case Inspected           = 'inspected';
    case WaitingOnInsurance  = 'waiting_on_insurance';
    case Approved            = 'approved';
    case InRepair            = 'in_repair';
    case Reassembly          = 'reassembly';
    case MakeReady           = 'make_ready';
    case Delivered           = 'delivered';

    public function label(): string
    {
        return match($this) {
            self::ToBeAcquired       => 'To Be Acquired',
            self::Acquired           => 'Acquired',
            self::Inspected          => 'Inspected',
            self::WaitingOnInsurance => 'Waiting on Insurance',
            self::Approved           => 'Approved',
            self::InRepair           => 'In Repair',
            self::Reassembly         => 'Reassembly',
            self::MakeReady          => 'Make Ready',
            self::Delivered          => 'Delivered',
        };
    }

    /** Tailwind color classes for status badges */
    public function badgeClasses(): string
    {
        return match($this) {
            self::ToBeAcquired       => 'bg-slate-100 text-slate-500',
            self::Acquired           => 'bg-slate-200 text-slate-700',
            self::Inspected          => 'bg-blue-100 text-blue-700',
            self::WaitingOnInsurance => 'bg-yellow-100 text-yellow-800',
            self::Approved           => 'bg-green-100 text-green-700',
            self::InRepair           => 'bg-indigo-100 text-indigo-700',
            self::Reassembly         => 'bg-purple-100 text-purple-700',
            self::MakeReady          => 'bg-orange-100 text-orange-700',
            self::Delivered          => 'bg-emerald-100 text-emerald-700',
        };
    }

    /** Dot color for pipeline step indicators */
    public function dotClasses(): string
    {
        return match($this) {
            self::ToBeAcquired       => 'bg-slate-300',
            self::Acquired           => 'bg-slate-500',
            self::Inspected          => 'bg-blue-500',
            self::WaitingOnInsurance => 'bg-yellow-500',
            self::Approved           => 'bg-green-500',
            self::InRepair           => 'bg-indigo-500',
            self::Reassembly         => 'bg-purple-500',
            self::MakeReady          => 'bg-orange-500',
            self::Delivered          => 'bg-emerald-500',
        };
    }

    public function next(): ?self
    {
        $all = self::cases();
        foreach ($all as $i => $case) {
            if ($case === $this) {
                return $all[$i + 1] ?? null;
            }
        }
        return null;
    }

    public function previous(): ?self
    {
        $all = self::cases();
        foreach ($all as $i => $case) {
            if ($case === $this && $i > 0) {
                return $all[$i - 1];
            }
        }
        return null;
    }

    /** Ordinal position (1-based) for pipeline display */
    public function position(): int
    {
        return match($this) {
            self::ToBeAcquired       => 1,
            self::Acquired           => 2,
            self::Inspected          => 3,
            self::WaitingOnInsurance => 4,
            self::Approved           => 5,
            self::InRepair           => 6,
            self::Reassembly         => 7,
            self::MakeReady          => 8,
            self::Delivered          => 9,
        };
    }
}
