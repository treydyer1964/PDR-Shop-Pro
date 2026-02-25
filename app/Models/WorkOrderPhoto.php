<?php

namespace App\Models;

use App\Enums\PhotoCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WorkOrderPhoto extends Model
{
    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'uploaded_by',
        'path',
        'disk',
        'original_filename',
        'mime_type',
        'size',
        'category',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'category' => PhotoCategory::class,
        'size'     => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function deleteFile(): void
    {
        Storage::disk($this->disk)->delete($this->path);
    }

    public function formattedSize(): string
    {
        if (!$this->size) return '';
        if ($this->size < 1024) return $this->size . ' B';
        if ($this->size < 1048576) return round($this->size / 1024, 1) . ' KB';
        return round($this->size / 1048576, 1) . ' MB';
    }
}
