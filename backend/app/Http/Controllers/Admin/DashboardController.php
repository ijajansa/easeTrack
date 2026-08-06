<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Device;
use App\Models\Screenshot;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $globalEnabled = (bool) AppSetting::getValue('tracking_enabled', true);
        $globalInterval = (int) AppSetting::getValue('screenshot_interval_seconds', config('easetrack.default_interval_seconds'));

        return view('admin.dashboard', [
            'pageTitle' => 'Command Dashboard',
            'pageSubtitle' => 'A high-level pulse on employees, devices, screenshots, and live activity.',
            'totalEmployees' => Device::query()->count(),
            'activeDevices' => Device::query()->where('status', 'active')->count(),
            'inactiveDevices' => Device::query()->where('status', 'inactive')->count(),
            'totalScreenshots' => Screenshot::query()->count(),
            'recentScreenshots' => Screenshot::query()->with('device')->latest()->paginate(6, ['*'], 'recent_screenshots_page')->withQueryString(),
            'employees' => Device::query()->withCount('screenshots')->orderBy('employee_name')->paginate(5, ['*'], 'dashboard_employees_page')->withQueryString(),
            'trackingEnabled' => $globalEnabled,
            'defaultInterval' => $globalInterval,
        ]);
    }
}
