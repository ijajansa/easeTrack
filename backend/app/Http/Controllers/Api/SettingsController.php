<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('device');
        $globalEnabled = (bool) AppSetting::getValue('tracking_enabled', true);
        $globalInterval = (int) AppSetting::getValue('screenshot_interval_seconds', config('easetrack.default_interval_seconds'));

        $interval = $device->screenshot_interval_seconds ?? $globalInterval;

        return response()->json([
            'interval' => (int) $interval,
            'enabled' => $globalEnabled && $device->is_active,
            'activity_interval_seconds' => (int) AppSetting::getValue('activity_report_interval_seconds', config('easetrack.activity_report_interval_seconds')),
            'idle_threshold_seconds' => (int) AppSetting::getValue('idle_threshold_seconds', config('easetrack.idle_threshold_seconds')),
        ]);
    }
}
