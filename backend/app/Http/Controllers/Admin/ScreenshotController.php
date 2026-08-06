<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Screenshot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScreenshotController extends Controller
{
    public function index(Request $request): View
    {
        $query = Screenshot::query()->with('device')->latest();

        if ($request->filled('device_id')) {
            $query->whereHas('device', function ($deviceQuery) use ($request): void {
                $deviceQuery->where('device_id', (string) $request->string('device_id'));
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date('date'));
        }

        return view('admin.screenshots.index', [
            'pageTitle' => 'Screenshot Vault',
            'pageSubtitle' => 'Filter screenshots by employee, inspect previews, and move through history quickly.',
            'screenshots' => $query->paginate(20)->withQueryString(),
            'devices' => Device::query()->orderBy('employee_name')->get(),
        ]);
    }
}
