@extends('layouts.admin')

@section('content')
    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Edit Employee</h3>
            <span class="badge">Device ID and token stay the same</span>
        </div>
        <p class="muted">
            Update the employee name, status, or screenshot interval without changing the generated device credentials.
        </p>
    </div>

    <div class="grid" style="grid-template-columns: 1.05fr 0.95fr; gap:18px;">
        <section class="card soft">
            <form method="POST" action="{{ route('admin.employees.update', $device) }}">
                @csrf
                @method('PUT')

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap:16px;">
                    <div style="grid-column: span 2;">
                        <label for="employee_name">Employee name</label>
                        <input id="employee_name" type="text" name="employee_name" value="{{ old('employee_name', $device->employee_name) }}">
                        @error('employee_name')
                            <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active" @selected(old('status', $device->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $device->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="screenshot_interval_seconds">Screenshot interval seconds</label>
                        <input id="screenshot_interval_seconds" type="number" name="screenshot_interval_seconds" min="5" max="3600" value="{{ old('screenshot_interval_seconds', $device->screenshot_interval_seconds ?? config('easetrack.default_interval_seconds')) }}">
                        @error('screenshot_interval_seconds')
                            <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="actions" style="margin-top:18px;">
                    <button type="submit" class="btn primary">Save changes</button>
                    <a class="btn" href="{{ route('admin.employees.show', $device) }}">Cancel</a>
                </div>
            </form>
        </section>

        <aside class="card soft">
            <div class="section-title">
                <h3>Current Credentials</h3>
                <span class="badge">Copy for setup</span>
            </div>

            <div class="mini-stat" style="margin-bottom:14px;">
                <div class="muted">Device ID</div>
                <div style="display:flex; gap:8px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                    <code>{{ $device->device_id }}</code>
                    <button type="button" class="btn" data-copy-value="{{ $device->device_id }}">Copy</button>
                </div>
            </div>

            <div class="mini-stat" style="margin-bottom:14px;">
                <div class="muted">API token</div>
                <div style="display:flex; gap:8px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                    <code style="word-break:break-all;">{{ $device->api_token }}</code>
                    <button type="button" class="btn" data-copy-value="{{ $device->api_token }}">Copy</button>
                </div>
            </div>

            <div class="mini-stat" style="margin-bottom:14px;">
                <div class="muted">Admin URL</div>
                <code data-copy-target>{{ $setupUrls['admin'] }}</code>
            </div>

            <div class="mini-stat">
                <div class="muted">Client/server URL</div>
                <code data-copy-target>{{ $setupUrls['server'] }}</code>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            document.querySelectorAll('[data-copy-value]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const value = button.getAttribute('data-copy-value') || '';
                    try {
                        await navigator.clipboard.writeText(value);
                        button.textContent = 'Copied';
                        setTimeout(() => button.textContent = 'Copy', 1200);
                    } catch (_) {
                        window.prompt('Copy this value:', value);
                    }
                });
            });
        })();
    </script>
@endpush
