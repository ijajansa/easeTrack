<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Screenshot;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = $this->employeeQuery($request)
            ->withCount('screenshots')
            ->orderBy('employee_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.employees.index', [
            'pageTitle' => 'Employee Board',
            'pageSubtitle' => 'Search employees, inspect live status, and export working and idle reports.',
            'employees' => $employees,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(Device $device): View
    {
        $activityPreview = $device->activityLogs()
            ->latest('recorded_at')
            ->limit(60)
            ->get()
            ->sortBy('recorded_at')
            ->values();

        $activityLogs = $device->activityLogs()
            ->latest('recorded_at')
            ->paginate(12, ['*'], 'activity_page')
            ->withQueryString();

        return view('admin.employees.show', [
            'pageTitle' => $device->employee_name,
            'pageSubtitle' => 'Employee profile, live status, screenshot history, and activity charts.',
            'device' => $device->loadCount('screenshots'),
            'screenshots' => Screenshot::query()
                ->where('device_id', $device->id)
                ->latest()
                ->paginate(12, ['*'], 'screenshots_page')
                ->withQueryString(),
            'activityLogs' => $activityLogs,
            'activitySeries' => [
                'labels' => $activityPreview->map(fn ($log) => $log->recorded_at->format('H:i'))->all(),
                'working' => $activityPreview->map(fn ($log) => round($log->working_seconds / 60, 2))->all(),
                'idle' => $activityPreview->map(fn ($log) => round($log->idle_seconds / 60, 2))->all(),
                'totalWorkingMinutes' => round($activityPreview->sum('working_seconds') / 60, 2),
                'totalIdleMinutes' => round($activityPreview->sum('idle_seconds') / 60, 2),
            ],
            'liveBadge' => [
                'label' => $device->last_seen_label,
                'state' => $device->last_seen_state,
                'lastPingIso' => optional($device->last_ping_at)->toIso8601String(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'employee-working-idle-report-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Employee',
                'Device ID',
                'Status',
                'Working Hours',
                'Idle Hours',
                'Last Seen',
                'Last Activity',
                'Last Ping',
                'Screenshot Count',
            ]);

            $this->employeeQuery($request)
                ->withCount('screenshots')
                ->orderBy('employee_name')
                ->chunk(100, function ($employees) use ($handle): void {
                    foreach ($employees as $employee) {
                        fputcsv($handle, [
                            $employee->employee_name,
                            $employee->device_id,
                            ucfirst($employee->current_status ?? $employee->status),
                            number_format($employee->working_hours, 2),
                            number_format($employee->idle_hours, 2),
                            $employee->last_seen_label,
                            optional($employee->last_activity_at)->format('Y-m-d H:i:s'),
                            optional($employee->last_ping_at)->format('Y-m-d H:i:s'),
                            $employee->screenshots_count,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function employeeQuery(Request $request): Builder
    {
        $query = Device::query();

        if ($request->filled('search')) {
            $term = trim((string) $request->string('search'));
            $query->where(function (Builder $subQuery) use ($term): void {
                $subQuery->where('employee_name', 'like', '%' . $term . '%')
                    ->orWhere('device_id', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query;
    }
}
