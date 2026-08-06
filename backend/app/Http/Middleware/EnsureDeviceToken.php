<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->header('X-Device-Id', $request->input('device_id'));
        $apiToken = $request->header('X-Api-Token', $request->input('api_token'));

        if (! $deviceId || ! $apiToken) {
            return response()->json([
                'message' => 'device_id and api_token are required.',
            ], 401);
        }

        $device = Device::query()
            ->where('device_id', $deviceId)
            ->where('api_token', $apiToken)
            ->first();

        if (! $device || ! $device->is_active) {
            return response()->json([
                'message' => 'Invalid or inactive device.',
            ], 403);
        }

        $request->attributes->set('device', $device);

        return $next($request);
    }
}

