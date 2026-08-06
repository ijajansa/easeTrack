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

        $compareScreenshots = (clone $query)
            ->limit(2)
            ->get();

        return view('admin.screenshots.index', [
            'pageTitle' => 'Screenshot Vault',
            'pageSubtitle' => 'Filter screenshots by employee, inspect previews, and move through history quickly.',
            'screenshots' => $query->paginate(10)->withQueryString(),
            'compareScreenshots' => $compareScreenshots,
            'compareScreenshotsPayload' => $compareScreenshots->map(function (Screenshot $screenshot): array {
                return [
                    'url' => asset('storage/' . $screenshot->image_path),
                    'title' => $screenshot->device?->employee_name ?? 'Unknown',
                    'meta' => $screenshot->created_at->format('M d, Y h:i A') . ' · ' . ($screenshot->device?->device_id ?? 'Unknown device'),
                ];
            })->values(),
            'devices' => Device::query()->orderBy('employee_name')->get(),
        ]);
    }
}
