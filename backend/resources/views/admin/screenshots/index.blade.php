@extends('layouts.admin')

@section('content')
    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Screenshot Monitoring</h3>
            <span class="badge">{{ $screenshots->total() }} records</span>
        </div>
        <p class="muted">Filter screenshots by employee device or date, then open a preview instantly.</p>

        <form method="GET" class="form-row">
            <div>
                <label for="device_id">Employee / Device</label>
                <select id="device_id" name="device_id">
                    <option value="">All devices</option>
                    @foreach ($devices as $device)
                        <option value="{{ $device->device_id }}" @selected(request('device_id') === $device->device_id)>
                            {{ $device->employee_name }} ({{ $device->device_id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date">Date</label>
                <input id="date" type="date" name="date" value="{{ request('date') }}">
            </div>
            <div style="align-self:end;">
                <button type="submit" class="btn primary" style="width:100%;">Filter</button>
            </div>
        </form>
    </div>

    <div class="card soft">
        <table class="table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Employee</th>
                    <th>Device</th>
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
                        <td>{{ $screenshot->device?->employee_name ?? 'Unknown' }}</td>
                        <td>{{ $screenshot->device?->device_id ?? 'Unknown' }}</td>
                        <td>{{ $screenshot->created_at->format('M d, Y h:i A') }}</td>
                        <td><code>{{ $screenshot->image_path }}</code></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No screenshots found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $screenshots->links('components.pagination.compact') }}
        </div>
    </div>
@endsection
