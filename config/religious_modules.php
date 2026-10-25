<?php

return [
    'schema_version' => 3,
    // Enable only after the dedicated religious-module migration is deployed.
    'sync_enabled' => (bool) env('RELIGIOUS_MODULE_SYNC_ENABLED', false),
];
