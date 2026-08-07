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
        return $this->working_seconds < (7 * 3600) ? 'Under target' : 'Target met';
    }

    public function getIdleStatusLabelAttribute(): string
    {
        return $this->idle_seconds < (2 * 3600) ? 'Healthy' : 'Too much idle';
    }

    public function getWorkingStatusColorAttribute(): string
    {
        return $this->working_seconds < (7 * 3600) ? 'red' : 'green';
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

    public function getTrackingHealthStateAttribute(): string
    {
        if (! $this->last_ping_at instanceof Carbon) {
            return 'danger';
        }

        if ($this->last_ping_at->diffInMinutes(now()) > 5) {
            return 'danger';
        }

        return match ($this->screenshotHealthState()) {
            'danger' => 'danger',
            'warning' => 'warning',
            default => $this->last_ping_at->diffInMinutes(now()) <= 2 ? 'success' : 'warning',
        };
    }

    public function getLastScreenshotAtAttribute(): ?Carbon
    {
        $value = $this->attributes['screenshots_max_created_at'] ?? null;

        if (! $value) {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    public function getLastScreenshotLabelAttribute(): string
    {
        if (! $this->last_screenshot_at instanceof Carbon) {
            return 'No screenshots yet';
        }

        $diff = $this->last_screenshot_at->diffInSeconds(now());

        if ($diff < 60) {
            return $diff . 's ago';
        }

        if ($diff < 3600) {
            return max(1, (int) floor($diff / 60)) . 'm ago';
        }

        return max(1, (int) floor($diff / 3600)) . 'h ago';
    }

    public function getTrackingHealthLabelAttribute(): string
    {
        return match ($this->tracking_health_state) {
            'success' => 'Tracker healthy',
            'warning' => 'Tracker delayed',
            default => 'Tracker not responding',
        };
    }

    public function getTrackingHealthDetailAttribute(): string
    {
        if (! $this->last_ping_at instanceof Carbon) {
            return 'No activity ping received yet, so the device may be offline or the client may not have started.';
        }

        $minutesSincePing = $this->last_ping_at->diffInMinutes(now());
        $lastScreenshotAt = $this->last_screenshot_at;

        if ($minutesSincePing > 5) {
            return 'No activity ping for ' . $minutesSincePing . ' minute(s). The client may have stopped responding.';
        }

        if ($this->screenshotHealthState() === 'danger') {
            return 'Activity pings are still coming in, but screenshots have stopped updating. The capture process may be broken.';
        }

        if ($this->screenshotHealthState() === 'warning') {
            return 'Activity pings are live, but screenshots are behind the expected interval.';
        }

        if ($minutesSincePing <= 2) {
            return 'Last ping and screenshots are recent. Tracking is healthy.';
        }

        return 'Tracking is active, but the last ping is slightly delayed.';
    }

    private function screenshotHealthState(): string
    {
        if (! $this->last_ping_at instanceof Carbon) {
            return 'danger';
        }

        $intervalSeconds = max(
            1,
            (int) ($this->screenshot_interval_seconds ?? config('easetrack.default_interval_seconds', 300))
        );

        $warningThreshold = max($intervalSeconds * 6, 120);
        $dangerThreshold = max($intervalSeconds * 12, 300);

        if (! $this->last_screenshot_at instanceof Carbon) {
            return $this->last_ping_at->diffInMinutes(now()) > 2 ? 'warning' : 'warning';
        }

        $secondsSinceScreenshot = $this->last_screenshot_at->diffInSeconds(now());

        if ($secondsSinceScreenshot >= $dangerThreshold) {
            return 'danger';
        }

        if ($secondsSinceScreenshot >= $warningThreshold) {
            return 'warning';
        }

        return 'success';
    }

    public function getWorkingDurationLabelAttribute(): string
    {
        return $this->formatDuration($this->working_seconds);
    }

    public function getIdleDurationLabelAttribute(): string
    {
        return $this->formatDuration($this->idle_seconds);
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
