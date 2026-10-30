<?php

return [
    // Enable only after the dedicated migration and initial admin grant.
    'enabled' => (bool) env('HAQ_APP_ADMIN_ENABLED', false),
    'admin_minutes' => 30,
    // Ed25519 secret key, base64, kept outside source control. Public keys ship in Flutter.
    'signing_key' => env('HAQ_CONTENT_SIGNING_KEY', ''),
    'signing_key_id' => env('HAQ_CONTENT_SIGNING_KEY_ID', 'v1'),
    'max_package_bytes' => 8 * 1024 * 1024,
];
