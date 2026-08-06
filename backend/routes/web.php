<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\ScreenshotController as AdminScreenshotController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
        Route::get('/employees/{device}/setup', [EmployeeController::class, 'setup'])->name('employees.setup');
        Route::get('/employees/{device}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{device}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{device}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/employees/{device}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/screenshots', [AdminScreenshotController::class, 'index'])->name('screenshots.index');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    });
});
