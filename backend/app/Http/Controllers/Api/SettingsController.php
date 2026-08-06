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
        $globalEnabled = AppSetting::booleanValue('tracking_enabled', true);
        $globalInterval = (int) AppSetting::getValue('default_interval_seconds', config('easetrack.default_interval_seconds'));

        return response()->json([
            'interval' => $globalInterval,
            'enabled' => $globalEnabled,
            'default_interval_seconds' => $globalInterval,
            'activity_interval_seconds' => (int) AppSetting::getValue('activity_report_interval_seconds', config('easetrack.activity_report_interval_seconds')),
            'idle_threshold_seconds' => (int) AppSetting::getValue('idle_threshold_seconds', config('easetrack.idle_threshold_seconds')),
            'timeout_seconds' => (int) AppSetting::getValue('timeout_seconds', config('easetrack.timeout_seconds', 20)),
        ]);
    }
}
