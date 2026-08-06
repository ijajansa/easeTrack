<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('device');

        $validated = $request->validate([
            'working_seconds' => ['required', 'integer', 'min:0'],
            'idle_seconds' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,idle'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($device, $validated): void {
            $device->forceFill([
                'working_seconds' => $device->working_seconds + (int) $validated['working_seconds'],
                'idle_seconds' => $device->idle_seconds + (int) $validated['idle_seconds'],
                'current_status' => $validated['status'],
                'last_ping_at' => now(),
                'last_activity_at' => $validated['status'] === 'active' ? now() : $device->last_activity_at,
            ])->save();

            DeviceActivityLog::query()->create([
                'device_id' => $device->id,
                'working_seconds' => (int) $validated['working_seconds'],
                'idle_seconds' => (int) $validated['idle_seconds'],
                'status' => $validated['status'],
                'recorded_at' => $validated['recorded_at'] ?? now(),
            ]);
        });

        return response()->json([
            'message' => 'Activity updated successfully.',
        ]);
    }
}
