<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ScreenshotUploadController;
use App\Http\Controllers\Api\ActivityController;

Route::middleware('device.token')->group(function () {
    Route::get('/settings', [SettingsController::class, 'show']);
    Route::post('/activity', [ActivityController::class, 'store']);
    Route::post('/upload', [ScreenshotUploadController::class, 'store']);
});
