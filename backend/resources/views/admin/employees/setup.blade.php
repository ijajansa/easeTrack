@extends('layouts.admin')

@section('content')
    @if (session('status'))
        <div class="card soft" style="margin-bottom:18px; border-color: rgba(22,163,74,0.28); background: rgba(22,163,74,0.08); color:#166534;">
            {{ session('status') }}
        </div>
    @endif

    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Setup Sheet</h3>
            <span class="badge">Compact handoff</span>
        </div>
        <p class="muted">
            Share these credentials with the employee once. This page keeps only the setup details and admin actions.
        </p>
    </div>

    <div class="grid" style="grid-template-columns: 1.05fr 0.95fr; gap:18px;">
        <section class="card soft">
            <div class="section-title">
                <h3>{{ $device->employee_name }}</h3>
                <span class="badge">{{ ucfirst($device->status) }}</span>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap:12px;">
                <div class="mini-stat">
                    <div class="muted">Device ID</div>
                    <div style="display:flex; justify-content:space-between; gap:8px; align-items:center;">
                        <code>{{ $device->device_id }}</code>
                        <button type="button" class="btn" data-copy-value="{{ $device->device_id }}">Copy</button>
                    </div>
                </div>

                <div class="mini-stat">
                    <div class="muted">API token</div>
                    <div style="display:flex; justify-content:space-between; gap:8px; align-items:center;">
                        <code style="word-break:break-all;">{{ $device->api_token }}</code>
                        <button type="button" class="btn" data-copy-value="{{ $device->api_token }}">Copy</button>
                    </div>
                </div>

                <div class="mini-stat">
                    <div class="muted">Admin URL</div>
                    <div style="display:flex; justify-content:space-between; gap:8px; align-items:center;">
                        <code style="word-break:break-all;">{{ $setupUrls['admin'] }}</code>
                        <button type="button" class="btn" data-copy-value="{{ $setupUrls['admin'] }}">Copy</button>
                    </div>
                </div>

                <div class="mini-stat">
                    <div class="muted">Server URL</div>
                    <div style="display:flex; justify-content:space-between; gap:8px; align-items:center;">
                        <code style="word-break:break-all;">{{ $setupUrls['server'] }}</code>
                        <button type="button" class="btn" data-copy-value="{{ $setupUrls['server'] }}">Copy</button>
                    </div>
                </div>
            </div>

            <div class="mini-stat" style="margin-top:12px;">
                <div class="muted">What the employee enters</div>
                <div class="muted" style="margin-top:6px;">
                    Full name, device ID, API token, and server URL.
                </div>
            </div>
        </section>

        <aside class="card soft">
            <div class="section-title">
                <h3>Actions</h3>
                <span class="badge">{{ $device->last_seen_label }}</span>
            </div>

            <div class="actions" style="flex-direction:column; align-items:stretch;">
                <a class="btn primary" href="{{ route('admin.employees.show', $device) }}">Open employee profile</a>
                <a class="btn" href="{{ route('admin.employees.edit', $device) }}">Edit employee</a>
                <a class="btn" href="{{ route('admin.screenshots.index', ['device_id' => $device->device_id]) }}">Open screenshots</a>
            </div>

            <form method="POST" action="{{ route('admin.employees.destroy', $device) }}" onsubmit="return confirm('Delete this employee and all related tracking data?');" style="margin-top:18px;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="width:100%; background: rgba(220,38,38,0.10); color:#b91c1c; border-color: rgba(220,38,38,0.22);">Delete employee</button>
            </form>
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
