<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'pageTitle' => 'Global Settings',
            'pageSubtitle' => 'Control the default client timing values for every employee device.',
            'settings' => [
                'default_interval_seconds' => (int) AppSetting::getValue('default_interval_seconds', config('easetrack.default_interval_seconds')),
                'activity_report_interval_seconds' => (int) AppSetting::getValue('activity_report_interval_seconds', config('easetrack.activity_report_interval_seconds')),
                'idle_threshold_seconds' => (int) AppSetting::getValue('idle_threshold_seconds', config('easetrack.idle_threshold_seconds')),
                'timeout_seconds' => (int) AppSetting::getValue('timeout_seconds', config('easetrack.timeout_seconds', 20)),
                'tracking_enabled' => AppSetting::booleanValue('tracking_enabled', true),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_interval_seconds' => ['required', 'integer', 'min:5', 'max:3600'],
            'activity_report_interval_seconds' => ['required', 'integer', 'min:10', 'max:86400'],
            'idle_threshold_seconds' => ['required', 'integer', 'min:1', 'max:3600'],
            'timeout_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'tracking_enabled' => ['nullable', 'boolean'],
        ]);

        AppSetting::setValue('default_interval_seconds', $validated['default_interval_seconds']);
        AppSetting::setValue('activity_report_interval_seconds', $validated['activity_report_interval_seconds']);
        AppSetting::setValue('idle_threshold_seconds', $validated['idle_threshold_seconds']);
        AppSetting::setValue('timeout_seconds', $validated['timeout_seconds']);
        AppSetting::setValue('tracking_enabled', $request->boolean('tracking_enabled'));

        return back()->with('status', 'Global settings updated successfully.');
    }
}
