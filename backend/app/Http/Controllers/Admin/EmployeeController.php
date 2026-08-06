<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Screenshot;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = $this->employeeQuery($request)
            ->withCount('screenshots')
            ->withMax('screenshots', 'created_at')
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
            'device' => $device->loadCount('screenshots')->loadMax('screenshots', 'created_at'),
            'screenshots' => Screenshot::query()
                ->where('device_id', $device->id)
                ->latest()
                ->paginate(10, ['*'], 'screenshots_page')
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
                ->withMax('screenshots', 'created_at')
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
}
