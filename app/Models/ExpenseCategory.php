<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'is_system',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'is_system'  => 'boolean',
        'active'     => 'boolean',
        'sort_order' => 'integer',
    ];

    // ── System slug constants ───────────────────────────────────────────────────

    const SLUG_RENTAL     = 'rental';
    const SLUG_RI         = 'ri';
    const SLUG_PARTS      = 'parts';
    const SLUG_DETAIL     = 'detail';
    const SLUG_FUEL       = 'fuel';
    const SLUG_BODY_SHOP  = 'body_shop';
    const SLUG_GLASS      = 'glass';
    const SLUG_MISC       = 'misc';
    const SLUG_ADMIN_FEE  = 'admin_fee';
    const SLUG_PORTER_FEE = 'porter_fee';

    // ── Standard categories (seeded per tenant) ────────────────────────────────

    public static function standardCategories(): array
    {
        return [
            ['name' => 'Rental',     'slug' => self::SLUG_RENTAL,     'sort_order' => 1],
            ['name' => 'R&I',        'slug' => self::SLUG_RI,         'sort_order' => 2],
            ['name' => 'Parts',      'slug' => self::SLUG_PARTS,      'sort_order' => 3],
            ['name' => 'Detail',     'slug' => self::SLUG_DETAIL,     'sort_order' => 4],
            ['name' => 'Fuel',       'slug' => self::SLUG_FUEL,       'sort_order' => 5],
            ['name' => 'Body Shop',  'slug' => self::SLUG_BODY_SHOP,  'sort_order' => 6],
            ['name' => 'Glass',      'slug' => self::SLUG_GLASS,      'sort_order' => 7],
            ['name' => 'Misc',       'slug' => self::SLUG_MISC,       'sort_order' => 8],
            ['name' => 'Admin Fee',  'slug' => self::SLUG_ADMIN_FEE,  'sort_order' => 9],
            ['name' => 'Porter Fee', 'slug' => self::SLUG_PORTER_FEE, 'sort_order' => 10],
        ];
    }

    /**
     * Seed standard expense categories for a new tenant.
     */
    public static function seedForTenant(int $tenantId): void
    {
        $now = now();

        foreach (static::standardCategories() as $cat) {
            static::firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $cat['slug']],
                array_merge($cat, [
                    'tenant_id'  => $tenantId,
                    'is_system'  => true,
                    'active'     => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(WorkOrderExpense::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
