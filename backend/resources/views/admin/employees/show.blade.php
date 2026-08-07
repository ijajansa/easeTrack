@extends('layouts.admin')

@section('content')
    @if (session('status'))
        <div class="card soft" style="margin-bottom:18px; border-color: rgba(22,163,74,0.28); background: rgba(22,163,74,0.08); color:#166534;">
            {{ session('status') }}
        </div>
    @endif

    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Report Filters</h3>
            <span class="badge">Applied to this profile</span>
        </div>
        <form method="GET" action="{{ route('admin.employees.show', $device) }}" class="form-row">
            <div>
                <label for="report_date">Report date</label>
                <input id="report_date" type="date" name="report_date" value="{{ $filters['report_date'] ?? '' }}">
            </div>
            <div>
                <label for="screenshot_from">Screenshot from</label>
                <input id="screenshot_from" type="date" name="screenshot_from" value="{{ $filters['screenshot_from'] ?? '' }}">
            </div>
            <div>
                <label for="screenshot_to">Screenshot to</label>
                <input id="screenshot_to" type="date" name="screenshot_to" value="{{ $filters['screenshot_to'] ?? '' }}">
            </div>
            <div>
                <label for="activity_from">Activity from</label>
                <input id="activity_from" type="date" name="activity_from" value="{{ $filters['activity_from'] ?? '' }}">
            </div>
            <div>
                <label for="activity_to">Activity to</label>
                <input id="activity_to" type="date" name="activity_to" value="{{ $filters['activity_to'] ?? '' }}">
            </div>
            <div>
                <label for="activity_status">Activity status</label>
                <select id="activity_status" name="activity_status">
                    <option value="">All</option>
                    <option value="active" @selected(($filters['activity_status'] ?? '') === 'active')>Active</option>
                    <option value="idle" @selected(($filters['activity_status'] ?? '') === 'idle')>Idle</option>
                </select>
            </div>
            <div style="align-self:end; display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" class="btn primary">Apply filters</button>
                <a class="btn" href="{{ route('admin.employees.show', $device) }}">Reset</a>
            </div>
        </form>
    </div>

    <div class="hero">
        <section class="hero-main">
            <div class="actions" style="margin-bottom:12px;">
                <span
                    class="status-pill {{ $liveBadge['state'] }}"
                    data-live-last-seen
                    data-last-ping-at="{{ $liveBadge['lastPingIso'] }}"
                    data-state="{{ $liveBadge['state'] }}"
                >
                    <span class="live-dot {{ $liveBadge['state'] }}" data-live-dot></span>
                    <span data-live-label>{{ $liveBadge['label'] }}</span>
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.16); color: white;">{{ ucfirst($device->status) }}</span>
                <span class="badge" style="background: rgba(255,255,255,0.16); color: white;">{{ $device->screenshots_count }} screenshots</span>
            </div>

            <div
                class="badge"
                style="display:inline-flex; align-items:center; gap:8px; margin-bottom:12px; padding:10px 14px; border-radius:999px; background: {{
                    $device->tracking_health_state === 'success'
                        ? 'rgba(22,163,74,0.18)'
                        : ($device->tracking_health_state === 'warning'
                            ? 'rgba(217,119,6,0.18)'
                            : 'rgba(220,38,38,0.18)')
                }}; color: {{
                    $device->tracking_health_state === 'success'
                        ? '#d1fae5'
                        : ($device->tracking_health_state === 'warning'
                            ? '#fef3c7'
                            : '#fee2e2')
                }};">
                <span style="width:10px; height:10px; border-radius:999px; background: {{
                    $device->tracking_health_state === 'success'
                        ? '#22c55e'
                        : ($device->tracking_health_state === 'warning'
                            ? '#f59e0b'
                            : '#ef4444')
                }};"></span>
                {{ $device->tracking_health_label }}
            </div>

            <h2>{{ $device->employee_name }}</h2>
            <p>
                Device ID: <strong>{{ $device->device_id }}</strong>
                - Current state: {{ ucfirst($device->current_status ?? $device->status) }}
                - Last activity: {{ optional($device->last_activity_at)->format('M d, Y h:i A') ?? 'Never' }}
            </p>
            <p class="muted">{{ $device->tracking_health_detail }}</p>
            <p class="muted">Last screenshot: <strong>{{ $device->last_screenshot_label }}</strong></p>
            <p class="muted">Selected report date: <strong>{{ $reportDate->format('M d, Y') }}</strong></p>

            <div class="actions" style="margin-top:18px;">
                <a class="ghost" href="{{ route('admin.employees.index') }}">Back to employees</a>
                <a class="ghost" href="{{ route('admin.screenshots.index', ['device_id' => $device->device_id]) }}">Open screenshots</a>
                <a class="ghost" href="{{ route('admin.employees.edit', $device) }}">Edit employee</a>
                <a class="ghost" href="{{ route('admin.employees.setup', $device) }}">Setup sheet</a>
            </div>
        </section>

        <div class="hero-side">
            <div class="mini-stat">
                <div class="muted">Working hours for {{ $reportDate->format('M d') }}</div>
                <strong style="color: {{ ($todayStats['working_seconds'] ?? 0) < (7 * 3600) ? '#dc2626' : '#16a34a' }};">
                    {{ $todayStats['working_duration_label'] ?? '00:00:00' }}
                </strong>
                <div class="muted">{{ $todayStats['working_duration_label'] ?? '00:00:00' }} &middot; {{ ($todayStats['working_seconds'] ?? 0) < (7 * 3600) ? 'Under target' : 'Target met' }}</div>
            </div>
            <div class="mini-stat">
                <div class="muted">Idle hours for {{ $reportDate->format('M d') }}</div>
                <strong style="color: {{ ($todayStats['idle_seconds'] ?? 0) < (2 * 3600) ? '#16a34a' : '#dc2626' }};">
                    {{ $todayStats['idle_duration_label'] ?? '00:00:00' }}
                </strong>
                <div class="muted">{{ $todayStats['idle_duration_label'] ?? '00:00:00' }} &middot; {{ ($todayStats['idle_seconds'] ?? 0) < (2 * 3600) ? 'Healthy' : 'Too much idle' }}</div>
            </div>
            <div class="mini-stat">
                <div class="muted">Screenshots for {{ $reportDate->format('M d') }}</div>
                <strong>{{ $todayStats['screenshots'] ?? 0 }}</strong>
                <div class="muted">Screens captured on the selected date</div>
            </div>
        </div>
    </div>

    <div class="grid stats" style="margin-bottom:18px;">
        <div class="card soft">
            <div class="muted">Working today</div>
            <h2 style="color: {{ ($todayStats['working_seconds'] ?? 0) < (7 * 3600) ? '#dc2626' : '#16a34a' }};">
                {{ $todayStats['working_duration_label'] ?? '00:00:00' }}
            </h2>
            <div class="muted">{{ $reportDate->format('M d, Y') }} &middot; {{ ($todayStats['working_seconds'] ?? 0) < (7 * 3600) ? 'Under target' : 'Target met' }}</div>
        </div>
        <div class="card soft">
            <div class="muted">Idle today</div>
            <h2 style="color: {{ ($todayStats['idle_seconds'] ?? 0) < (2 * 3600) ? '#16a34a' : '#dc2626' }};">
                {{ $todayStats['idle_duration_label'] ?? '00:00:00' }}
            </h2>
            <div class="muted">{{ $reportDate->format('M d, Y') }} &middot; {{ ($todayStats['idle_seconds'] ?? 0) < (2 * 3600) ? 'Healthy' : 'Too much idle' }}</div>
        </div>
        <div class="card soft">
            <div class="muted">Screenshots today</div>
            <h2>{{ $todayStats['screenshots'] ?? 0 }}</h2>
            <div class="muted">{{ $reportDate->format('M d, Y') }}</div>
        </div>
        <div class="card soft">
            <div class="muted">Current status</div>
            <h2>{{ ucfirst($device->current_status ?? $device->status) }}</h2>
        </div>
        <div class="card soft">
            <div class="muted">Last screenshot</div>
            <h2>{{ $device->last_screenshot_label }}</h2>
            <div class="muted">{{ $device->tracking_health_detail }}</div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: 1.2fr 0.8fr; margin-top: 18px;">
        <section class="card soft">
            <div class="section-title">
                <h3>Daily Trend</h3>
                <span class="badge">{{ $dailySeries['daysTracked'] }} days</span>
            </div>
            <div style="height:320px;">
                <canvas id="activityTrend"></canvas>
            </div>
            <div style="height:260px; margin-top:24px; max-width: 420px;">
                <canvas id="activityBreakdown"></canvas>
            </div>
        </section>

        <section class="card soft">
            <div class="section-title">
                <h3>Activity Summary</h3>
                <span class="badge">Daily totals</span>
            </div>
            <div class="mini-stat" style="margin-bottom:12px;">
                <div class="muted">Working hours in daily report</div>
                <strong>{{ $dailySeries['totalWorkingDuration'] }}</strong>
            </div>
            <div class="mini-stat" style="margin-bottom:12px;">
                <div class="muted">Idle hours in daily report</div>
                <strong>{{ $dailySeries['totalIdleDuration'] }}</strong>
            </div>
            <div class="mini-stat" style="margin-bottom:12px;">
                <div class="muted">Days tracked</div>
                <strong>{{ $dailySeries['daysTracked'] }}</strong>
            </div>
            <div class="mini-stat">
                <div class="muted">Last seen</div>
                <strong style="font-size: 20px;">{{ $liveBadge['label'] }}</strong>
            </div>
        </section>
    </div>

    <div class="card soft" style="margin-top:18px;">
        <div class="section-title">
            <h3>Daily Breakdown</h3>
            <span class="badge">Calendar view</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entries</th>
                    <th>Working</th>
                    <th>Idle</th>
                    <th>Working Status</th>
                    <th>Idle Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailySummary as $day)
                    <tr>
                        <td>
                            <strong>{{ $day->label }}</strong>
                        </td>
                        <td>{{ $day->entries }}</td>
                        <td>
                            <span style="font-weight:800; color: {{ $day->working_status_color }};">
                                {{ $day->working_duration_label }}
                            </span>
                        </td>
                        <td>
                            <span style="font-weight:800; color: {{ $day->idle_status_color }};">
                                {{ $day->idle_duration_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background: {{ $day->working_status_color === '#16a34a' ? 'rgba(22,163,74,0.12)' : 'rgba(220,38,38,0.12)' }}; color: {{ $day->working_status_color }};">
                                {{ $day->working_status_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background: {{ $day->idle_status_color === '#16a34a' ? 'rgba(22,163,74,0.12)' : 'rgba(220,38,38,0.12)' }}; color: {{ $day->idle_status_color }};">
                                {{ $day->idle_status_label }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No daily activity logs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="grid" style="/*grid-template-columns: 1.15fr 0.85fr;*/ margin-top:18px;">
        <section class="card soft">
            <div class="section-title">
                <h3>Screenshot Timeline</h3>
                <span class="badge">{{ $screenshots->total() }} items</span>
            </div>
            <form method="GET" action="{{ route('admin.employees.show', $device) }}" class="form-row" style="margin-bottom:16px;">
                <input type="hidden" name="report_date" value="{{ $filters['report_date'] ?? '' }}">
                <input type="hidden" name="activity_from" value="{{ $filters['activity_from'] ?? '' }}">
                <input type="hidden" name="activity_to" value="{{ $filters['activity_to'] ?? '' }}">
                <input type="hidden" name="activity_status" value="{{ $filters['activity_status'] ?? '' }}">
                <div>
                    <label for="screenshot_from_view">From</label>
                    <input id="screenshot_from_view" type="date" name="screenshot_from" value="{{ $filters['screenshot_from'] ?? '' }}">
                </div>
                <div>
                    <label for="screenshot_to_view">To</label>
                    <input id="screenshot_to_view" type="date" name="screenshot_to" value="{{ $filters['screenshot_to'] ?? '' }}">
                </div>
                <div style="align-self:end;">
                    <button type="submit" class="btn primary" style="width:100%;">Filter screenshots</button>
                </div>
            </form>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
                @forelse ($screenshots as $screenshot)
                    <article class="card" style="padding: 12px; border-radius: 20px; background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));">
                        <img
                            loading="lazy"
                            src="{{ asset('storage/' . $screenshot->image_path) }}"
                            alt="Screenshot preview"
                            style="width:100%; height:220px; object-fit:cover; border-radius: 16px; border: 1px solid rgba(148, 163, 184, 0.16); box-shadow: 0 12px 24px rgba(15, 23, 42, 0.10);"
                        >
                        <div style="margin-top: 10px; font-weight: 700;">{{ $screenshot->created_at->format('M d, Y h:i A') }}</div>
                    </article>
                @empty
                    <div class="muted">No screenshots for this employee.</div>
                @endforelse
            </div>
            <div class="pagination">
                {{ $screenshots->links('components.pagination.compact') }}
            </div>
        </section>

        <section class="card soft" style="display: none">
            <div class="section-title">
                <h3>Recent Activity</h3>
                <span class="badge">{{ $activityLogs->total() }} entries</span>
            </div>
            <form method="GET" action="{{ route('admin.employees.show', $device) }}" class="form-row" style="margin-bottom:16px;">
                <input type="hidden" name="report_date" value="{{ $filters['report_date'] ?? '' }}">
                <input type="hidden" name="screenshot_from" value="{{ $filters['screenshot_from'] ?? '' }}">
                <input type="hidden" name="screenshot_to" value="{{ $filters['screenshot_to'] ?? '' }}">
                <div>
                    <label for="activity_from_view">From</label>
                    <input id="activity_from_view" type="date" name="activity_from" value="{{ $filters['activity_from'] ?? '' }}">
                </div>
                <div>
                    <label for="activity_to_view">To</label>
                    <input id="activity_to_view" type="date" name="activity_to" value="{{ $filters['activity_to'] ?? '' }}">
                </div>
                <div>
                    <label for="activity_status_view">Status</label>
                    <select id="activity_status_view" name="activity_status">
                        <option value="">All</option>
                        <option value="active" @selected(($filters['activity_status'] ?? '') === 'active')>Active</option>
                        <option value="idle" @selected(($filters['activity_status'] ?? '') === 'idle')>Idle</option>
                    </select>
                </div>
                <div style="align-self:end;">
                    <button type="submit" class="btn primary" style="width:100%;">Filter activity</button>
                </div>
            </form>
            <table class="table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Recorded</th>
                        <th>Work</th>
                        <th>Idle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activityLogs as $log)
                        <tr>
                            <td>
                                <span class="badge" style="background: {{ $log->status === 'active' ? 'rgba(22,163,74,0.12)' : 'rgba(217,119,6,0.12)' }}; color: {{ $log->status === 'active' ? '#15803d' : '#b45309' }};">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>{{ $log->recorded_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $log->working_seconds }}s</td>
                            <td>{{ $log->idle_seconds }}s</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">No activity logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination">
                {{ $activityLogs->links('components.pagination.compact') }}
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const labels = @json($dailySeries['labels']);
        const working = @json($dailySeries['working']);
        const idle = @json($dailySeries['idle']);

        const trendCanvas = document.getElementById('activityTrend');
        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Working hours',
                            data: working,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.72)',
                            borderRadius: 10,
                        },
                        {
                            label: 'Idle hours',
                            data: idle,
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220, 38, 38, 0.66)',
                            borderRadius: 10,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => `${value}h`
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });
        }

        const breakdownCanvas = document.getElementById('activityBreakdown');
        if (breakdownCanvas) {
            new Chart(breakdownCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Working hours', 'Idle hours'],
                    datasets: [{
                        data: [
                            @json($dailySeries['totalWorkingDuration']),
                            @json($dailySeries['totalIdleDuration'])
                        ],
                        backgroundColor: ['#2563eb', '#dc2626'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
@endpush
