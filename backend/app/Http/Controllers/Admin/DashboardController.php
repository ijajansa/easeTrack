<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Device;
use App\Models\Screenshot;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $globalEnabled = AppSetting::booleanValue('tracking_enabled', true);
        $globalInterval = (int) AppSetting::getValue('default_interval_seconds', config('easetrack.default_interval_seconds'));
        $reportDate = Carbon::today();

        $devicesForKpis = Device::query()
            ->withMax('screenshots', 'created_at')
            ->get();

        $devicesForKpis->transform(function (Device $device) use ($reportDate): Device {
            $stats = $this->dateStatsForDevice($device, $reportDate);
            $device->setAttribute('report_date', $reportDate->toDateString());
            $device->setAttribute('report_working_seconds', $stats['working_seconds']);
            $device->setAttribute('report_idle_seconds', $stats['idle_seconds']);
            $device->setAttribute('report_screenshots_count', $stats['screenshots']);
            $device->setAttribute('report_working_duration_label', $stats['working_duration_label']);
            $device->setAttribute('report_idle_duration_label', $stats['idle_duration_label']);
            $device->setAttribute('report_working_status_label', $stats['working_status_label']);
            $device->setAttribute('report_idle_status_label', $stats['idle_status_label']);
            $device->setAttribute('report_working_status_color', $stats['working_status_color']);
            $device->setAttribute('report_idle_status_color', $stats['idle_status_color']);

            return $device;
        });

        $deviceHealthCounts = $devicesForKpis->countBy(fn (Device $device) => $device->tracking_health_state);
        $employeePerformanceCounts = $devicesForKpis->countBy(function (Device $device): string {
            if ($device->report_idle_seconds >= 2 * 3600) {
                return 'excessive_idle';
            }

            if ($device->report_working_seconds < 7 * 3600) {
                return 'under_target';
            }

            return 'healthy';
        });

        $employees = Device::query()
            ->withCount('screenshots')
            ->withMax('screenshots', 'created_at')
            ->orderBy('employee_name')
            ->paginate(5, ['*'], 'dashboard_employees_page')
            ->withQueryString();

        $employees->getCollection()->transform(function (Device $employee) use ($reportDate): Device {
            $stats = $this->dateStatsForDevice($employee, $reportDate);
            $employee->setAttribute('report_date', $reportDate->toDateString());
            $employee->setAttribute('report_working_seconds', $stats['working_seconds']);
            $employee->setAttribute('report_idle_seconds', $stats['idle_seconds']);
            $employee->setAttribute('report_screenshots_count', $stats['screenshots']);
            $employee->setAttribute('report_working_duration_label', $stats['working_duration_label']);
            $employee->setAttribute('report_idle_duration_label', $stats['idle_duration_label']);
            $employee->setAttribute('report_working_status_label', $stats['working_status_label']);
            $employee->setAttribute('report_idle_status_label', $stats['idle_status_label']);
            $employee->setAttribute('report_working_status_color', $stats['working_status_color']);
            $employee->setAttribute('report_idle_status_color', $stats['idle_status_color']);

            return $employee;
        });

        return view('admin.dashboard', [
            'pageTitle' => 'Command Dashboard',
            'pageSubtitle' => 'A high-level pulse on employees, devices, screenshots, and live activity.',
            'reportDate' => $reportDate,
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
            'employees' => $employees,
            'trackingEnabled' => $globalEnabled,
            'defaultInterval' => $globalInterval,
        ]);
    }

    private function dateStatsForDevice(Device $device, Carbon $reportDate): array
    {
        $date = $reportDate->toDateString();

        $workingSeconds = (int) $device->activityLogs()
            ->whereDate('recorded_at', $date)
            ->sum('working_seconds');
        $idleSeconds = (int) $device->activityLogs()
            ->whereDate('recorded_at', $date)
            ->sum('idle_seconds');
        $screenshots = (int) Screenshot::query()
            ->where('device_id', $device->id)
            ->whereDate('created_at', $date)
            ->count();

        return [
            'working_seconds' => $workingSeconds,
            'idle_seconds' => $idleSeconds,
            'screenshots' => $screenshots,
            'working_duration_label' => $this->formatDuration($workingSeconds),
            'idle_duration_label' => $this->formatDuration($idleSeconds),
            'working_status_label' => $workingSeconds < (7 * 3600) ? 'Under target' : 'Target met',
            'idle_status_label' => $idleSeconds < (2 * 3600) ? 'Healthy' : 'Too much idle',
            'working_status_color' => $workingSeconds < (7 * 3600) ? '#dc2626' : '#16a34a',
            'idle_status_color' => $idleSeconds < (2 * 3600) ? '#16a34a' : '#dc2626',
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
