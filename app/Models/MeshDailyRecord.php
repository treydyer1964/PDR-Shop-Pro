<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeshDailyRecord extends Model
{
    protected $fillable = [
        'record_date',
        'png_path',
        'npy_path',
        'max_size_inches',
        'frame_count',
        'last_frame_at',
    ];

    protected $casts = [
        'record_date'     => 'date',
        'last_frame_at'   => 'datetime',
        'max_size_inches' => 'float',
        'frame_count'     => 'integer',
    ];

    /**
     * Public URL for the rendered MESH PNG (via Storage symlink).
     */
    public function pngUrl(): ?string
    {
        if (!$this->png_path) return null;
        $ts = $this->last_frame_at ? $this->last_frame_at->timestamp : 0;
        return asset('storage/' . ltrim($this->png_path, '/')) . '?v=' . $ts;
    }
}
