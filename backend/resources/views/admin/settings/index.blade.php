@extends('layouts.admin')

@section('content')
    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Global Settings</h3>
            <span class="badge">Applies to all employees</span>
        </div>
        <p class="muted">
            Change the client timing defaults here and every connected Python client will pick them up from the server.
        </p>

        @if (session('status'))
            <div class="card" style="margin:16px 0 18px; border-color: rgba(22,163,74,0.28); background: rgba(22,163,74,0.08); color:#166534;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div class="card soft">
                    <label for="default_interval_seconds">Default interval seconds</label>
                    <input
                        id="default_interval_seconds"
                        type="number"
                        name="default_interval_seconds"
                        min="5"
                        max="3600"
                        value="{{ old('default_interval_seconds', $settings['default_interval_seconds']) }}"
                    >
                    @error('default_interval_seconds')
                        <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card soft">
                    <label for="activity_report_interval_seconds">Activity report interval seconds</label>
                    <input
                        id="activity_report_interval_seconds"
                        type="number"
                        name="activity_report_interval_seconds"
                        min="10"
                        max="86400"
                        value="{{ old('activity_report_interval_seconds', $settings['activity_report_interval_seconds']) }}"
                    >
                    @error('activity_report_interval_seconds')
                        <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card soft">
                    <label for="idle_threshold_seconds">Idle threshold seconds</label>
                    <input
                        id="idle_threshold_seconds"
                        type="number"
                        name="idle_threshold_seconds"
                        min="1"
                        max="3600"
                        value="{{ old('idle_threshold_seconds', $settings['idle_threshold_seconds']) }}"
                    >
                    @error('idle_threshold_seconds')
                        <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="card soft">
                    <label for="timeout_seconds">Timeout seconds</label>
                    <input
                        id="timeout_seconds"
                        type="number"
                        name="timeout_seconds"
                        min="5"
                        max="300"
                        value="{{ old('timeout_seconds', $settings['timeout_seconds']) }}"
                    >
                    @error('timeout_seconds')
                        <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card soft" style="margin-top:16px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                <div>
                    <label style="margin-bottom:6px;">
                        <input type="checkbox" name="tracking_enabled" value="1" @checked(old('tracking_enabled', $settings['tracking_enabled']))>
                        Tracking enabled globally
                    </label>
                    <div class="muted">Disable this to pause all employee tracking from the server.</div>
                </div>

                <button type="submit" class="btn primary">Save global settings</button>
            </div>
        </form>
    </div>

    <div class="grid stats">
        <div class="card soft">
            <div class="muted">Default interval</div>
            <h2>{{ $settings['default_interval_seconds'] }}s</h2>
        </div>
        <div class="card soft">
            <div class="muted">Activity report</div>
            <h2>{{ $settings['activity_report_interval_seconds'] }}s</h2>
        </div>
        <div class="card soft">
            <div class="muted">Idle threshold</div>
            <h2>{{ $settings['idle_threshold_seconds'] }}s</h2>
        </div>
        <div class="card soft">
            <div class="muted">Timeout</div>
            <h2>{{ $settings['timeout_seconds'] }}s</h2>
        </div>
    </div>
@endsection
