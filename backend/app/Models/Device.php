<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'device_id',
        'api_token',
        'status',
        'screenshot_interval_seconds',
        'working_seconds',
        'idle_seconds',
        'current_status',
        'last_activity_at',
        'last_ping_at',
    ];

    protected $casts = [
        'screenshot_interval_seconds' => 'integer',
        'working_seconds' => 'integer',
        'idle_seconds' => 'integer',
        'status' => 'string',
        'last_activity_at' => 'datetime',
        'last_ping_at' => 'datetime',
    ];

    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(DeviceActivityLog::class);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getRouteKeyName(): string
    {
        return 'device_id';
    }

    public function getWorkingHoursAttribute(): float
    {
        return round($this->working_seconds / 3600, 2);
    }

    public function getIdleHoursAttribute(): float
    {
        return round($this->idle_seconds / 3600, 2);
    }

    public function getWorkingStatusLabelAttribute(): string
    {
        return $this->working_seconds < (8 * 3600) ? 'Under target' : 'Target met';
    }

    public function getIdleStatusLabelAttribute(): string
    {
        return $this->idle_seconds < (2 * 3600) ? 'Healthy' : 'Too much idle';
    }

    public function getWorkingStatusColorAttribute(): string
    {
        return $this->working_seconds < (8 * 3600) ? 'red' : 'green';
    }

    public function getIdleStatusColorAttribute(): string
    {
        return $this->idle_seconds < (2 * 3600) ? 'green' : 'red';
    }

    public function getLastSeenLabelAttribute(): string
    {
        if (! $this->last_ping_at instanceof Carbon) {
            return 'Never';
        }

        $diff = $this->last_ping_at->diffInSeconds(now());

        if ($diff < 60) {
            return $diff . 's ago';
        }

        if ($diff < 3600) {
            return max(1, (int) floor($diff / 60)) . 'm ago';
        }

        return max(1, (int) floor($diff / 3600)) . 'h ago';
    }

    public function getLastSeenStateAttribute(): string
    {
        if (! $this->last_ping_at instanceof Carbon) {
            return 'offline';
        }

        return $this->last_ping_at->diffInMinutes(now()) <= 2 ? 'online' : 'away';
    }
}
