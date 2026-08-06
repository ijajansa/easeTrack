<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Device;
use Illuminate\Database\Seeder;

class TestDeviceSeeder extends Seeder
{
    public function run(): void
    {
        Device::query()->updateOrCreate(
            ['device_id' => 'device-001'],
            [
                'employee_name' => 'Test Device',
                'api_token' => 'your-token-here',
                'status' => 'active',
                'screenshot_interval_seconds' => 10,
                'working_seconds' => 0,
                'idle_seconds' => 0,
                'current_status' => 'active',
                'last_activity_at' => null,
                'last_ping_at' => null,
            ]
        );

        AppSetting::query()->updateOrCreate(
            ['key' => 'tracking_enabled'],
            ['value' => '1']
        );

        AppSetting::query()->updateOrCreate(
            ['key' => 'screenshot_interval_seconds'],
            ['value' => '10']
        );

        AppSetting::query()->updateOrCreate(
            ['key' => 'activity_report_interval_seconds'],
            ['value' => '60']
        );

        AppSetting::query()->updateOrCreate(
            ['key' => 'idle_threshold_seconds'],
            ['value' => '10']
        );
    }
}
