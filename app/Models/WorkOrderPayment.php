<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'source',
        'method',
        'amount',
        'received_on',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'source'      => PaymentSource::class,
        'method'      => PaymentMethod::class,
        'received_on' => 'date',
        'amount'      => 'decimal:2',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
