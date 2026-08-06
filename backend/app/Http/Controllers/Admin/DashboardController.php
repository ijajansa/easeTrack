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
        $devicesForKpis = Device::query()
            ->withMax('screenshots', 'created_at')
            ->get();

        $deviceHealthCounts = $devicesForKpis->countBy(fn (Device $device) => $device->tracking_health_state);
        $employeePerformanceCounts = $devicesForKpis->countBy(function (Device $device): string {
            if ($device->idle_seconds >= 2 * 3600) {
                return 'excessive_idle';
            }

            if ($device->working_seconds < 8 * 3600) {
                return 'under_target';
            }

            return 'healthy';
        });

        return view('admin.dashboard', [
            'pageTitle' => 'Command Dashboard',
            'pageSubtitle' => 'A high-level pulse on employees, devices, screenshots, and live activity.',
            'totalEmployees' => Device::query()->count(),
            'activeDevices' => Device::query()->where('status', 'active')->count(),
            'inactiveDevices' => Device::query()->where('status', 'inactive')->count(),
            'totalScreenshots' => Screenshot::query()->count(),
            'widgetDevicesHealthy' => $deviceHealthCounts->get('success', 0),
            'widgetDevicesDelayed' => $deviceHealthCounts->get('warning', 0),
            'widgetDevicesOffline' => $deviceHealthCounts->get('danger', 0),
            'widgetEmployeesUnderTarget' => $employeePerformanceCounts->get('under_target', 0),
            'widgetEmployeesExcessiveIdle' => $employeePerformanceCounts->get('excessive_idle', 0),
            'recentScreenshots' => Screenshot::query()->with('device')->latest()->paginate(6, ['*'], 'recent_screenshots_page')->withQueryString(),
            'employees' => Device::query()->withCount('screenshots')->withMax('screenshots', 'created_at')->orderBy('employee_name')->paginate(5, ['*'], 'dashboard_employees_page')->withQueryString(),
            'trackingEnabled' => $globalEnabled,
            'defaultInterval' => $globalInterval,
        ]);
    }
}
