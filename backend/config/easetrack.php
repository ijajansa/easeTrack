<?php

return [
    'default_interval_seconds' => env('EASETRACK_DEFAULT_INTERVAL', 300),
    'activity_report_interval_seconds' => env('EASETRACK_ACTIVITY_REPORT_INTERVAL', 60),
    'idle_threshold_seconds' => env('EASETRACK_IDLE_THRESHOLD_SECONDS', 10),
    'upload_disk' => env('EASETRACK_UPLOAD_DISK', 'public'),
    'upload_root' => env('EASETRACK_UPLOAD_ROOT', 'screenshots'),
    'max_upload_kb' => env('EASETRACK_MAX_UPLOAD_KB', 5120),
    'allowed_mimes' => ['image/jpeg', 'image/png'],
];
