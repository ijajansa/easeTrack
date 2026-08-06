@extends('layouts.admin')

@section('content')
    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Employee Board</h3>
            <div class="actions">
                <a class="btn primary" href="{{ route('admin.employees.export', request()->query()) }}">Export CSV</a>
                <a class="btn" href="{{ route('admin.employees.index') }}">Reset</a>
            </div>
        </div>

        <form method="GET" class="form-row">
            <div>
                <label for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Employee name or device id">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div style="align-self:end;">
                <button type="submit" class="btn primary" style="width:100%;">Apply</button>
            </div>
        </form>
    </div>

    <div class="grid stats" style="margin-bottom:18px;">
        <div class="card soft">
            <div class="muted">Employees shown</div>
            <h2>{{ $employees->total() }}</h2>
        </div>
        <div class="card soft">
            <div class="muted">Page size</div>
            <h2>{{ $employees->perPage() }}</h2>
        </div>
        <div class="card soft">
            <div class="muted">Ready for export</div>
            <h2>CSV</h2>
        </div>
        <div class="card soft">
            <div class="muted">Live badges</div>
            <h2>On</h2>
        </div>
    </div>

    <div class="card soft">
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Device</th>
                    <th>Working Hours</th>
                    <th>Idle Hours</th>
                    <th>Last Seen</th>
                    <th>Screenshots</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>
                            <strong>{{ $employee->employee_name }}</strong>
                            <div class="muted">{{ $employee->status === 'active' ? 'Tracked device' : 'Paused device' }}</div>
                        </td>
                        <td>
                            <code>{{ $employee->device_id }}</code>
                        </td>
                        <td>
                            <span style="font-weight:800; color: {{ $employee->working_status_color }};">
                                {{ number_format($employee->working_hours, 2) }} hrs
                            </span>
                            <div class="muted">{{ $employee->working_status_label }}</div>
                        </td>
                        <td>
                            <span style="font-weight:800; color: {{ $employee->idle_status_color }};">
                                {{ number_format($employee->idle_hours, 2) }} hrs
                            </span>
                            <div class="muted">{{ $employee->idle_status_label }}</div>
                        </td>
                        <td>
                            <span
                                class="status-pill {{ $employee->last_seen_state }}"
                                data-live-last-seen
                                data-last-ping-at="{{ optional($employee->last_ping_at)->toIso8601String() }}"
                                data-state="{{ $employee->last_seen_state }}"
                            >
                                <span class="live-dot {{ $employee->last_seen_state }}" data-live-dot></span>
                                <span data-live-label>{{ $employee->last_seen_label }}</span>
                            </span>
                        </td>
                        <td>{{ $employee->screenshots_count }}</td>
                        <td>
                            <span class="badge" style="background: {{ $employee->status === 'active' ? 'rgba(22,163,74,0.12)' : 'rgba(220,38,38,0.12)' }}; color: {{ $employee->status === 'active' ? '#15803d' : '#b91c1c' }};">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td><a class="btn" href="{{ route('admin.employees.show', $employee) }}">Open</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $employees->links('components.pagination.compact') }}
        </div>
    </div>
@endsection
