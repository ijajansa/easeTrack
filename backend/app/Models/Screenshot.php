<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Screenshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'image_path',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return Storage::disk(config('easetrack.upload_disk'))->url($this->image_path);
    }

    public function getDeviceFolderAttribute(): string
    {
        return $this->image_path;
    }
}
