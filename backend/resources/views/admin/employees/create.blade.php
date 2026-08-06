@extends('layouts.admin')

@section('content')
    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Create Employee</h3>
            <span class="badge">Auto-generates credentials</span>
        </div>
        <p class="muted">
            Add the employee once and SnapTrack will generate a unique device ID and API token automatically after save.
        </p>
    </div>

    <div class="card soft">
        <form method="POST" action="{{ route('admin.employees.store') }}">
            @csrf

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap:16px;">
                <div style="grid-column: span 2;">
                    <label for="employee_name">Employee name</label>
                    <input id="employee_name" type="text" name="employee_name" value="{{ old('employee_name', $defaults['employee_name']) }}" placeholder="Full employee name">
                    @error('employee_name')
                        <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active" @selected(old('status', $defaults['status']) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $defaults['status']) === 'inactive')>Inactive</option>
                    </select>
                    @error('status')
                        <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="screenshot_interval_seconds">Screenshot interval seconds</label>
                    <input id="screenshot_interval_seconds" type="number" name="screenshot_interval_seconds" min="5" max="3600" value="{{ old('screenshot_interval_seconds', $defaults['screenshot_interval_seconds']) }}">
                    @error('screenshot_interval_seconds')
                        <div class="muted" style="color:#b91c1c; margin-top:8px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="actions" style="margin-top:18px;">
                <button type="submit" class="btn primary">Create employee</button>
                <a class="btn" href="{{ route('admin.employees.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
