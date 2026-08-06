<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Screenshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScreenshotUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('device');

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png', 'max:' . config('easetrack.max_upload_kb')],
        ]);

        $file = $validated['file'];
        $dateFolder = now()->format('Y/m/d');
        $deviceFolder = trim(config('easetrack.upload_root'), '/') . '/' . $device->device_id . '/' . $dateFolder;
        $fileName = now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs($deviceFolder, $fileName, config('easetrack.upload_disk'));

        $screenshot = Screenshot::query()->create([
            'device_id' => $device->id,
            'image_path' => $storedPath,
        ]);

        return response()->json([
            'message' => 'Screenshot uploaded successfully.',
            'data' => [
                'id' => $screenshot->id,
                'path' => $screenshot->image_path,
            ],
        ]);
    }
}

