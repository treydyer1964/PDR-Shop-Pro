<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NoDamage       = 'no_damage';
    case NotContacted   = 'not_contacted';
    case NoAnswer       = 'no_answer';
    case Lead           = 'lead';
    case Lost           = 'lost';
    case NoSoliciting   = 'no_soliciting';
    case OldCashedCheck = 'old_cashed_check';
    case Contract       = 'contract';
    case NotInterested  = 'not_interested';
    case AnswerNoInfo   = 'answer_no_info';

    public function label(): string
    {
        return match($this) {
            self::NoDamage       => 'No Damage',
            self::NotContacted   => 'Not Contacted',
            self::NoAnswer       => 'No Answer',
            self::Lead           => 'Lead',
            self::Lost           => 'Lost',
            self::NoSoliciting   => 'No Soliciting',
            self::OldCashedCheck => 'Old/Cashed Check',
            self::Contract       => 'Contract',
            self::NotInterested  => 'Not Interested',
            self::AnswerNoInfo   => 'Answer + No Info',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::NoDamage       => 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',
            self::NotContacted   => 'bg-yellow-100 text-yellow-700',
            self::NoAnswer       => 'bg-blue-900 text-blue-100',
            self::Lead           => 'bg-orange-100 text-orange-700',
            self::Lost           => 'bg-red-100 text-red-700',
            self::NoSoliciting   => 'bg-amber-900 text-amber-100',
            self::OldCashedCheck => 'bg-purple-100 text-purple-700',
            self::Contract       => 'bg-green-100 text-green-700',
            self::NotInterested  => 'bg-pink-100 text-pink-700',
            self::AnswerNoInfo   => 'bg-sky-100 text-sky-700',
        };
    }

    public function dotClasses(): string
    {
        return match($this) {
            self::NoDamage       => 'bg-slate-300',
            self::NotContacted   => 'bg-yellow-400',
            self::NoAnswer       => 'bg-blue-800',
            self::Lead           => 'bg-orange-400',
            self::Lost           => 'bg-red-500',
            self::NoSoliciting   => 'bg-amber-900',
            self::OldCashedCheck => 'bg-purple-500',
            self::Contract       => 'bg-green-500',
            self::NotInterested  => 'bg-pink-500',
            self::AnswerNoInfo   => 'bg-sky-400',
        };
    }

    /** Hex color for Leaflet map pins */
    public function pinColor(): string
    {
        return match($this) {
            self::NoDamage       => '#e2e8f0',
            self::NotContacted   => '#eab308',
            self::NoAnswer       => '#1e40af',
            self::Lead           => '#f97316',
            self::Lost           => '#ef4444',
            self::NoSoliciting   => '#8B4513',
            self::OldCashedCheck => '#7c3aed',
            self::Contract       => '#16a34a',
            self::NotInterested  => '#ec4899',
            self::AnswerNoInfo   => '#38bdf8',
        };
    }

    /** Pin stroke color — dark on light pins, white on dark pins */
    public function pinStroke(): string
    {
        return match($this) {
            self::NoDamage, self::AnswerNoInfo => '#334155',
            default                            => '#ffffff',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::NotContacted,
            self::NoAnswer,
            self::Lead,
            self::AnswerNoInfo,
        ]);
    }
}
