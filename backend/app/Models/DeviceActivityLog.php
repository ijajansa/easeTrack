<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'working_seconds',
        'idle_seconds',
        'status',
        'recorded_at',
    ];

    protected $casts = [
        'working_seconds' => 'integer',
        'idle_seconds' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
