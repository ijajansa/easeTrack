@extends('layouts.admin')

@section('content')
    @if (session('status'))
        <div class="card soft" style="margin-bottom:18px; border-color: rgba(22,163,74,0.28); background: rgba(22,163,74,0.08); color:#166534;">
            {{ session('status') }}
        </div>
    @endif

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

            <div class="actions" style="margin-top:18px;">
                <a class="ghost" href="{{ route('admin.employees.index') }}">Back to employees</a>
                <a class="ghost" href="{{ route('admin.screenshots.index', ['device_id' => $device->device_id]) }}">Open screenshots</a>
                <a class="ghost" href="{{ route('admin.employees.edit', $device) }}">Edit employee</a>
                <a class="ghost" href="{{ route('admin.employees.setup', $device) }}">Setup sheet</a>
            </div>
        </section>

        <div class="hero-side">
            <div class="mini-stat">
                <div class="muted">Working hours</div>
                <strong style="color: {{ $device->working_status_color }};">{{ $device->working_duration_label }}</strong>
                <div class="muted">{{ number_format($device->working_hours, 2) }} hrs &middot; {{ $device->working_status_label }}</div>
            </div>
            <div class="mini-stat">
                <div class="muted">Idle hours</div>
                <strong style="color: {{ $device->idle_status_color }};">{{ $device->idle_duration_label }}</strong>
                <div class="muted">{{ number_format($device->idle_hours, 2) }} hrs &middot; {{ $device->idle_status_label }}</div>
            </div>
        </div>
    </div>

    <div class="grid stats" style="margin-bottom:18px;">
        <div class="card soft">
            <div class="muted">Working</div>
            <h2 style="color: {{ $device->working_status_color }};">{{ $device->working_duration_label }}</h2>
            <div class="muted">{{ number_format($device->working_hours, 2) }} hrs &middot; {{ $device->working_duration_label }}</div>
        </div>
        <div class="card soft">
            <div class="muted">Idle</div>
            <h2 style="color: {{ $device->idle_status_color }};">{{ $device->idle_duration_label }}</h2>
            <div class="muted">{{ number_format($device->idle_hours, 2) }} hrs &middot; {{ $device->idle_duration_label }}</div>
        </div>
        <div class="card soft">
            <div class="muted">Screenshots</div>
            <h2>{{ $device->screenshots_count }}</h2>
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
                <h3>Activity Chart</h3>
                <span class="badge">Last 60 pings</span>
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
                <span class="badge">Minutes</span>
            </div>
            <div class="mini-stat" style="margin-bottom:12px;">
                <div class="muted">Working minutes in chart</div>
                <strong>{{ number_format($activitySeries['totalWorkingMinutes'], 2) }}</strong>
            </div>
            <div class="mini-stat" style="margin-bottom:12px;">
                <div class="muted">Idle minutes in chart</div>
                <strong>{{ number_format($activitySeries['totalIdleMinutes'], 2) }}</strong>
            </div>
            <div class="mini-stat">
                <div class="muted">Last seen</div>
                <strong style="font-size: 20px;">{{ $liveBadge['label'] }}</strong>
            </div>
        </section>
    </div>

    <div class="grid" style="grid-template-columns: 1.15fr 0.85fr; margin-top:18px;">
        <section class="card soft">
            <div class="section-title">
                <h3>Screenshot Timeline</h3>
                <span class="badge">{{ $screenshots->total() }} items</span>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Captured At</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($screenshots as $screenshot)
                        <tr>
                            <td>
                                <img
                                    class="preview"
                                    loading="lazy"
                                    src="{{ asset('storage/' . $screenshot->image_path) }}"
                                    alt="Screenshot preview"
                                >
                            </td>
                            <td>{{ $screenshot->created_at->format('M d, Y h:i A') }}</td>
                            <td><code>{{ $screenshot->image_path }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">No screenshots for this employee.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="pagination">
                {{ $screenshots->links('components.pagination.compact') }}
            </div>
        </section>

        <section class="card soft">
            <div class="section-title">
                <h3>Recent Activity</h3>
                <span class="badge">{{ $activityLogs->total() }} entries</span>
            </div>
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
        const labels = @json($activitySeries['labels']);
        const working = @json($activitySeries['working']);
        const idle = @json($activitySeries['idle']);

        const trendCanvas = document.getElementById('activityTrend');
        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Working minutes',
                            data: working,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.14)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 2,
                        },
                        {
                            label: 'Idle minutes',
                            data: idle,
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220, 38, 38, 0.12)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => `${value}m`
                            }
                        }
                    }
                }
            });
        }

        const breakdownCanvas = document.getElementById('activityBreakdown');
        if (breakdownCanvas) {
            new Chart(breakdownCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Working', 'Idle'],
                    datasets: [{
                        data: [
                            @json($activitySeries['totalWorkingMinutes']),
                            @json($activitySeries['totalIdleMinutes'])
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
