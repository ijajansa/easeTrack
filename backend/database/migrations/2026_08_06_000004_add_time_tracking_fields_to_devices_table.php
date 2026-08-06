<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('working_seconds')->default(0)->after('screenshot_interval_seconds');
            $table->unsignedBigInteger('idle_seconds')->default(0)->after('working_seconds');
            $table->string('current_status')->default('active')->after('idle_seconds');
            $table->timestamp('last_activity_at')->nullable()->after('current_status');
            $table->timestamp('last_ping_at')->nullable()->after('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'working_seconds',
                'idle_seconds',
                'current_status',
                'last_activity_at',
                'last_ping_at',
            ]);
        });
    }
};
