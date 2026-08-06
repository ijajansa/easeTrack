@extends('layouts.admin')

@section('content')
    <div class="hero">
        <section class="hero-main">
            <span class="badge" style="background: rgba(255,255,255,0.16); color: white;">Live monitoring</span>
            <h2>See your team's work, idle time, and screenshots in one calm, powerful view.</h2>
            <p>
                Track employee devices, compare working and idle patterns, and jump into details without hunting through tables.
                The interface is designed to feel premium enough for investor demos and internal leadership reviews.
            </p>

            <div class="actions" style="margin-top:18px;">
                <a class="primary" href="{{ route('admin.employees.index') }}">Open employee board</a>
                <a class="ghost" href="{{ route('admin.screenshots.index') }}">Review screenshots</a>
            </div>
        </section>

        <div class="hero-side">
            <div class="mini-stat">
                <div class="muted">Total employees</div>
                <strong>{{ $totalEmployees }}</strong>
            </div>
            <div class="mini-stat">
                <div class="muted">Active devices</div>
                <strong>{{ $activeDevices }}</strong>
            </div>
            <div class="mini-stat">
                <div class="muted">Total screenshots</div>
                <strong>{{ $totalScreenshots }}</strong>
            </div>
        </div>
    </div>

    <div class="grid stats">
        <div class="card soft">
            <div class="muted">Total Employees</div>
            <h2>{{ $totalEmployees }}</h2>
        </div>
        <div class="card soft">
            <div class="muted">Active Devices</div>
            <h2>{{ $activeDevices }}</h2>
        </div>
        <div class="card soft">
            <div class="muted">Inactive Devices</div>
            <h2>{{ $inactiveDevices }}</h2>
        </div>
        <div class="card soft">
            <div class="muted">Total Screenshots</div>
            <h2>{{ $totalScreenshots }}</h2>
        </div>
    </div>

    <div class="section-title" style="margin-top:18px;">
        <h3>Health KPIs</h3>
        <span class="badge">Live system pulse</span>
    </div>
    <div class="grid stats">
        <div class="card soft">
            <div class="muted">Devices healthy</div>
            <h2 style="color:#15803d;">{{ $widgetDevicesHealthy }}</h2>
            <div class="muted">Tracker and screenshots are current</div>
        </div>
        <div class="card soft">
            <div class="muted">Devices delayed</div>
            <h2 style="color:#b45309;">{{ $widgetDevicesDelayed }}</h2>
            <div class="muted">Ping or screenshot flow is lagging</div>
        </div>
        <div class="card soft">
            <div class="muted">Devices offline</div>
            <h2 style="color:#b91c1c;">{{ $widgetDevicesOffline }}</h2>
            <div class="muted">No recent response from the client</div>
        </div>
        <div class="card soft">
            <div class="muted">Employees under target</div>
            <h2 style="color:#b91c1c;">{{ $widgetEmployeesUnderTarget }}</h2>
            <div class="muted">Working time is below 8 hours</div>
        </div>
        <div class="card soft">
            <div class="muted">Employees with excessive idle</div>
            <h2 style="color:#b91c1c;">{{ $widgetEmployeesExcessiveIdle }}</h2>
            <div class="muted">Idle time is 2 hours or more</div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: 1.25fr 1fr; margin-top: 18px;">
        <section class="card soft">
            <div class="section-title">
                <h3>Monitoring State</h3>
                <span class="badge">{{ $trackingEnabled ? 'Enabled' : 'Disabled' }}</span>
            </div>
            <p class="muted">Default interval: {{ $defaultInterval }} seconds</p>
            <p class="muted">The admin panel is wired for screenshot activity, working hours, idle minutes, and device state in real time.</p>
            <div class="actions">
                <a class="primary" href="{{ route('admin.employees.index') }}">View employees</a>
                <a class="ghost" href="{{ route('admin.screenshots.index') }}">Browse screenshots</a>
            </div>
        </section>

        <section class="card soft">
            <div class="section-title">
                <h3>Recent Activity</h3>
                <span class="badge">Latest</span>
            </div>

            @forelse ($recentScreenshots as $screenshot)
                <div style="display:flex; gap:12px; align-items:flex-start; padding:12px 0; border-bottom:1px solid rgba(148,163,184,0.16);">
                    <div style="width:44px; height:44px; border-radius:14px; background: linear-gradient(135deg, rgba(37,99,235,0.16), rgba(124,58,237,0.18)); display:grid; place-items:center; font-weight:800; color:#1d4ed8;">
                        {{ strtoupper(substr($screenshot->device?->employee_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:700;">{{ $screenshot->device?->employee_name ?? 'Unknown device' }}</div>
                        <div class="muted">{{ $screenshot->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            @empty
                <p class="muted">No screenshots uploaded yet.</p>
            @endforelse

            <div class="pagination">
                {{ $recentScreenshots->links('components.pagination.compact') }}
            </div>
        </section>
    </div>

    <div class="card soft" style="margin-top:18px;">
        <div class="section-title">
            <h3>Employee Overview</h3>
            <a class="btn" href="{{ route('admin.employees.export', request()->query()) }}">Export CSV</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Working Hours</th>
                    <th>Idle Hours</th>
                    <th>Last Seen</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>
                            <strong>{{ $employee->employee_name }}</strong>
                            <div class="muted">{{ $employee->device_id }}</div>
                        </td>
                        <td>
                            <span style="font-weight:800; color: {{ $employee->working_status_color }};">
                                {{ $employee->working_duration_label }}
                            </span>
                            <div class="muted">{{ number_format($employee->working_hours, 2) }} hrs &middot; {{ $employee->working_status_label }}</div>
                        </td>
                        <td>
                            <span style="font-weight:800; color: {{ $employee->idle_status_color }};">
                                {{ $employee->idle_duration_label }}
                            </span>
                            <div class="muted">{{ number_format($employee->idle_hours, 2) }} hrs &middot; {{ $employee->idle_status_label }}</div>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:6px;">
                                <span
                                    class="status-pill {{ $employee->last_seen_state }}"
                                    data-live-last-seen
                                    data-last-ping-at="{{ optional($employee->last_ping_at)->toIso8601String() }}"
                                    data-state="{{ $employee->last_seen_state }}"
                                >
                                    <span class="live-dot {{ $employee->last_seen_state }}" data-live-dot></span>
                                    <span data-live-label>{{ $employee->last_seen_label }}</span>
                                </span>
                                <span
                                    class="badge"
                                    style="background: {{
                                        $employee->tracking_health_state === 'success'
                                            ? 'rgba(22,163,74,0.12)'
                                            : ($employee->tracking_health_state === 'warning'
                                                ? 'rgba(217,119,6,0.12)'
                                                : 'rgba(220,38,38,0.12)')
                                    }}; color: {{
                                        $employee->tracking_health_state === 'success'
                                            ? '#15803d'
                                            : ($employee->tracking_health_state === 'warning'
                                                ? '#b45309'
                                                : '#b91c1c')
                                    }}; width: fit-content;">
                                    {{ $employee->tracking_health_label }}
                                </span>
                                <span class="muted" style="font-size:12px;">Screenshot: {{ $employee->last_screenshot_label }}</span>
                            </div>
                        </td>
                        <td><a class="btn" href="{{ route('admin.employees.show', $employee) }}">Open</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $employees->links('components.pagination.compact') }}
        </div>
    </div>
@endsection
