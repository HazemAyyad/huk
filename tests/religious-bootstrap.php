<?php

// This suite may create only its dedicated, ephemeral SQLite tables.
// Seed both superglobals: dotenv must not restore a real DB_URL from .env.
$projectRoot = dirname(__DIR__);
if (is_file($projectRoot.'/bootstrap/cache/config.php')) {
    throw new RuntimeException('Run religious tests from an uncached checkout.');
}
$testConfigCache = $projectRoot.'/bootstrap/cache/religious-test-config-missing.php';
if (is_file($testConfigCache)) {
    throw new RuntimeException('Unexpected isolated-test config cache exists.');
}
foreach ([
    'APP_ENV' => 'testing',
    'APP_CONFIG_CACHE' => $testConfigCache,
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
] as $key => $value) {
    $_ENV[$key] = $_SERVER[$key] = $value;
    putenv($key.'='.$value);
}

require_once $projectRoot.'/vendor/autoload.php';
