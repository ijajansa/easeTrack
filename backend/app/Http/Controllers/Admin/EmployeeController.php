<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Screenshot;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'report_date' => ['nullable', 'date'],
        ]);

        $reportDate = Carbon::parse($validated['report_date'] ?? now()->toDateString())->startOfDay();

        $employees = $this->employeeQuery($request)
            ->withCount('screenshots')
            ->withMax('screenshots', 'created_at')
            ->orderBy('employee_name')
            ->paginate(20)
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

        return view('admin.employees.index', [
            'pageTitle' => 'Employee Board',
            'pageSubtitle' => 'Search employees, inspect live status, and export working and idle reports.',
            'employees' => $employees,
            'reportDate' => $reportDate,
            'filters' => [
                'search' => $validated['search'] ?? $request->input('search', ''),
                'status' => $validated['status'] ?? $request->input('status', ''),
                'report_date' => $validated['report_date'] ?? $reportDate->toDateString(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.employees.create', [
            'pageTitle' => 'Create Employee',
            'pageSubtitle' => 'Add a new tracked device and generate secure credentials automatically.',
            'defaults' => [
                'employee_name' => old('employee_name', ''),
                'status' => old('status', 'active'),
                'screenshot_interval_seconds' => old('screenshot_interval_seconds', config('easetrack.default_interval_seconds')),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'screenshot_interval_seconds' => ['nullable', 'integer', 'min:5', 'max:3600'],
        ]);

        $device = Device::query()->create([
            'employee_name' => $validated['employee_name'],
            'device_id' => $this->generateUniqueDeviceId(),
            'api_token' => $this->generateUniqueApiToken(),
            'status' => $validated['status'],
            'screenshot_interval_seconds' => $validated['screenshot_interval_seconds'] ?? null,
            'working_seconds' => 0,
            'idle_seconds' => 0,
            'current_status' => $validated['status'] === 'active' ? 'active' : 'inactive',
        ]);

        return redirect()
            ->route('admin.employees.setup', $device)
            ->with('status', 'Employee created successfully. Share the setup details with the employee.');
    }

    public function show(Device $device, Request $request): View
    {
        $validated = $request->validate([
            'report_date' => ['nullable', 'date'],
            'screenshot_from' => ['nullable', 'date'],
            'screenshot_to' => ['nullable', 'date'],
            'activity_from' => ['nullable', 'date'],
            'activity_to' => ['nullable', 'date'],
            'activity_status' => ['nullable', 'in:active,idle'],
        ]);

        $reportDate = Carbon::parse($validated['report_date'] ?? now()->toDateString())->startOfDay();
        $reportDateString = $reportDate->toDateString();
        $screenshotFrom = $validated['screenshot_from'] ?? $reportDateString;
        $screenshotTo = $validated['screenshot_to'] ?? $reportDateString;
        $activityFrom = $validated['activity_from'] ?? $reportDateString;
        $activityTo = $validated['activity_to'] ?? $reportDateString;

        $activityPreview = $device->activityLogs()
            ->whereDate('recorded_at', '>=', $activityFrom)
            ->whereDate('recorded_at', '<=', $activityTo)
            ->latest('recorded_at')
            ->limit(60)
            ->get()
            ->sortBy('recorded_at')
            ->values();

        $activityLogsQuery = $device->activityLogs();
        if (! empty($activityFrom)) {
            $activityLogsQuery->whereDate('recorded_at', '>=', $activityFrom);
        }
        if (! empty($activityTo)) {
            $activityLogsQuery->whereDate('recorded_at', '<=', $activityTo);
        }
        if (! empty($validated['activity_status'])) {
            $activityLogsQuery->where('status', $validated['activity_status']);
        }

        $activityLogs = $activityLogsQuery
            ->latest('recorded_at')
            ->paginate(12, ['*'], 'activity_page')
            ->withQueryString();

        $dailySummaryQuery = $device->activityLogs();
        if (! empty($activityFrom)) {
            $dailySummaryQuery->whereDate('recorded_at', '>=', $activityFrom);
        }
        if (! empty($activityTo)) {
            $dailySummaryQuery->whereDate('recorded_at', '<=', $activityTo);
        }

        $dailySummary = $dailySummaryQuery
            ->selectRaw('DATE(recorded_at) as activity_date, SUM(working_seconds) as working_seconds, SUM(idle_seconds) as idle_seconds, COUNT(*) as entries')
            ->groupBy(DB::raw('DATE(recorded_at)'))
            ->orderBy('activity_date')
            ->get()
            ->map(function ($row): object {
                $date = Carbon::parse($row->activity_date);
                $workingSeconds = (int) $row->working_seconds;
                $idleSeconds = (int) $row->idle_seconds;

                return (object) [
                    'date' => $date,
                    'label' => $date->format('M d, Y'),
                    'working_seconds' => $workingSeconds,
                    'idle_seconds' => $idleSeconds,
                    'entries' => (int) $row->entries,
                    'working_duration_label' => $this->formatDuration($workingSeconds),
                    'idle_duration_label' => $this->formatDuration($idleSeconds),
                    'working_status_label' => $workingSeconds < (7 * 3600) ? 'Under target' : 'Target met',
                    'idle_status_label' => $idleSeconds < (2 * 3600) ? 'Healthy' : 'Too much idle',
                    'working_status_color' => $workingSeconds < (7 * 3600) ? '#dc2626' : '#16a34a',
                    'idle_status_color' => $idleSeconds < (2 * 3600) ? '#16a34a' : '#dc2626',
                ];
            });

        $screenshotsQuery = Screenshot::query()
            ->where('device_id', $device->id);
        if (! empty($screenshotFrom)) {
            $screenshotsQuery->whereDate('created_at', '>=', $screenshotFrom);
        }
        if (! empty($screenshotTo)) {
            $screenshotsQuery->whereDate('created_at', '<=', $screenshotTo);
        }

        $todayWorkingSeconds = (int) $device->activityLogs()
            ->whereDate('recorded_at', $reportDate->toDateString())
            ->sum('working_seconds');
        $todayIdleSeconds = (int) $device->activityLogs()
            ->whereDate('recorded_at', $reportDate->toDateString())
            ->sum('idle_seconds');
        $todayScreenshots = (int) Screenshot::query()
            ->where('device_id', $device->id)
            ->whereDate('created_at', $reportDate->toDateString())
            ->count();

        return view('admin.employees.show', [
            'pageTitle' => $device->employee_name,
            'pageSubtitle' => 'Employee profile, live status, screenshot history, and activity charts.',
            'device' => $device->loadCount('screenshots')->loadMax('screenshots', 'created_at'),
            'reportDate' => $reportDate,
            'todayStats' => [
                'working_seconds' => $todayWorkingSeconds,
                'idle_seconds' => $todayIdleSeconds,
                'screenshots' => $todayScreenshots,
                'working_duration_label' => $this->formatDuration($todayWorkingSeconds),
                'idle_duration_label' => $this->formatDuration($todayIdleSeconds),
            ],
            'filters' => [
                'report_date' => $validated['report_date'] ?? $reportDate->toDateString(),
                'screenshot_from' => $validated['screenshot_from'] ?? $reportDateString,
                'screenshot_to' => $validated['screenshot_to'] ?? $reportDateString,
                'activity_from' => $validated['activity_from'] ?? $reportDateString,
                'activity_to' => $validated['activity_to'] ?? $reportDateString,
                'activity_status' => $validated['activity_status'] ?? '',
            ],
            'screenshots' => $screenshotsQuery
                ->latest()
                ->paginate(18, ['*'], 'screenshots_page')
                ->withQueryString(),
            'activityLogs' => $activityLogs,
            'activitySeries' => [
                'labels' => $activityPreview->map(fn ($log) => $log->recorded_at->format('H:i'))->all(),
                'working' => $activityPreview->map(fn ($log) => round($log->working_seconds / 60, 2))->all(),
                'idle' => $activityPreview->map(fn ($log) => round($log->idle_seconds / 60, 2))->all(),
                'totalWorkingMinutes' => round($activityPreview->sum('working_seconds') / 60, 2),
                'totalIdleMinutes' => round($activityPreview->sum('idle_seconds') / 60, 2),
            ],
            'dailySummary' => $dailySummary,
            'dailySeries' => [
                'labels' => $dailySummary->map(fn ($row) => $row->date->format('M d'))->all(),
                'working' => $dailySummary->map(fn ($row) => round($row->working_seconds / 3600, 2))->all(),
                'idle' => $dailySummary->map(fn ($row) => round($row->idle_seconds / 3600, 2))->all(),
                'daysTracked' => $dailySummary->count(),
                'totalWorkingDuration' => $this->formatDuration($dailySummary->sum('working_seconds')),
                'totalIdleDuration' => $this->formatDuration($dailySummary->sum('idle_seconds')),
            ],
            'liveBadge' => [
                'label' => $device->last_seen_label,
                'state' => $device->last_seen_state,
                'lastPingIso' => optional($device->last_ping_at)->toIso8601String(),
            ],
        ]);
    }

    public function setup(Device $device, Request $request): View
    {
        return view('admin.employees.setup', [
            'pageTitle' => 'Employee Setup',
            'pageSubtitle' => 'Copy the generated credentials and share the employee setup details.',
            'device' => $device->loadCount('screenshots')->loadMax('screenshots', 'created_at'),
            'setupUrls' => $this->setupUrls($request),
        ]);
    }

    public function edit(Device $device, Request $request): View
    {
        return view('admin.employees.edit', [
            'pageTitle' => 'Edit Employee',
            'pageSubtitle' => 'Update employee identity, tracking status, and interval settings.',
            'device' => $device,
            'setupUrls' => $this->setupUrls($request),
        ]);
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'screenshot_interval_seconds' => ['nullable', 'integer', 'min:5', 'max:3600'],
        ]);

        $device->forceFill([
            'employee_name' => $validated['employee_name'],
            'status' => $validated['status'],
            'screenshot_interval_seconds' => $validated['screenshot_interval_seconds'] ?? null,
        ])->save();

        return redirect()
            ->route('admin.employees.show', $device)
            ->with('status', 'Employee updated successfully.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $device->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('status', 'Employee deleted successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'report_date' => ['nullable', 'date'],
        ]);

        $reportDate = Carbon::parse($validated['report_date'] ?? now()->toDateString())->startOfDay();
        $filename = 'employee-working-idle-report-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($request, $reportDate): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Employee',
                'Device ID',
                'Status',
                'Report Date',
                'Working Hours',
                'Idle Hours',
                'Screenshots',
                'Last Seen',
                'Last Activity',
                'Last Ping',
            ]);

            $this->employeeQuery($request)
                ->withCount('screenshots')
                ->withMax('screenshots', 'created_at')
                ->orderBy('employee_name')
                ->chunk(100, function ($employees) use ($handle, $reportDate): void {
                    foreach ($employees as $employee) {
                        $stats = $this->dateStatsForDevice($employee, $reportDate);

                        fputcsv($handle, [
                            $employee->employee_name,
                            $employee->device_id,
                            ucfirst($employee->current_status ?? $employee->status),
                            $reportDate->toDateString(),
                            number_format($stats['working_hours'], 2),
                            number_format($stats['idle_hours'], 2),
                            $stats['screenshots'],
                            $employee->last_seen_label,
                            optional($employee->last_activity_at)->format('Y-m-d H:i:s'),
                            optional($employee->last_ping_at)->format('Y-m-d H:i:s'),
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

    private function setupUrls(Request $request): array
    {
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        return [
            'admin' => $baseUrl . '/admin/login',
            'server' => $baseUrl,
        ];
    }

    private function generateUniqueDeviceId(): string
    {
        $maxSuffix = Device::query()
            ->where('device_id', 'like', 'device-%')
            ->pluck('device_id')
            ->map(function (string $deviceId): int {
                if (! preg_match('/^device-(\d+)$/', $deviceId, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return sprintf('device-%03d', $maxSuffix + 1);
    }

    private function generateUniqueApiToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Device::query()->where('api_token', $token)->exists());

        return $token;
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
