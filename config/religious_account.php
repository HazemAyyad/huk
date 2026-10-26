<?php

return [
    // Public OAuth client ID, never a client secret. Empty means unavailable.
    'google_client_id' => env('RELIGIOUS_GOOGLE_WEB_CLIENT_ID', ''),
    'recovery_enabled' => (bool) env('RELIGIOUS_ACCOUNT_RECOVERY_ENABLED', false),
];
