<?php

return [
    'enabled' => (bool) env('HAQ_ASSOCIATION_ENABLED', true),
    'enforce_devices' => (bool) env('HAQ_ASSOCIATION_ENFORCE_DEVICES', true),
    'attachment_max_kb' => env('HAQ_ASSOCIATION_ATTACHMENT_MAX_KB') === null || env('HAQ_ASSOCIATION_ATTACHMENT_MAX_KB') === ''
        ? null : (int) env('HAQ_ASSOCIATION_ATTACHMENT_MAX_KB'),
    'upload_chunk_bytes' => 512 * 1024,
    'offline_batch_limit' => 50,
    'import_row_limit' => 5000,
    'export_row_limit' => 50000,
    'presence_minutes' => 5,
    'event_duration_hours' => 24,
    'call_ring_seconds' => 60,
    'rtc' => [
        'driver' => env('HAQ_RTC_DRIVER', 'livekit'),
        'url' => env('LIVEKIT_URL'),
        'api_key' => env('LIVEKIT_API_KEY'),
        'api_secret' => env('LIVEKIT_API_SECRET'),
    ],
];
