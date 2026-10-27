<?php

return [
    // Deferred activation: no service-account file or project is committed.
    'enabled' => (bool) env('RELIGIOUS_PUSH_ENABLED', false),
    'project' => env('RELIGIOUS_PUSH_PROJECT', ''),
    'credentials_path' => env('RELIGIOUS_PUSH_CREDENTIALS', ''),
    // Public, approved publications only. Never use a topic for private data.
    'topic' => 'haq_public_messages_v1',
];
