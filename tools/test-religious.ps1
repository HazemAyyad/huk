param(
    [string]$Php = 'php'
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
if (Test-Path -LiteralPath (Join-Path $projectRoot 'bootstrap/cache/config.php')) {
    throw 'Cached application configuration found. Use a separate uncached checkout for isolated tests.'
}

# Override inherited DB_URL too: it can otherwise take precedence over DB_DATABASE.
$testEnvironment = @{
    APP_ENV = 'testing'
    APP_CONFIG_CACHE = (Join-Path $projectRoot 'bootstrap/cache/religious-test-config-missing.php')
    DB_CONNECTION = 'sqlite'
    DB_DATABASE = ':memory:'
    DB_URL = ''
    CACHE_STORE = 'array'
    SESSION_DRIVER = 'array'
    QUEUE_CONNECTION = 'sync'
    MAIL_MAILER = 'array'
}
$previousEnvironment = @{}
foreach ($key in $testEnvironment.Keys) {
    $previousEnvironment[$key] = [Environment]::GetEnvironmentVariable($key, 'Process')
}
$exitCode = 1
Push-Location $projectRoot
try {
    foreach ($key in $testEnvironment.Keys) {
        [Environment]::SetEnvironmentVariable($key, $testEnvironment[$key], 'Process')
    }
    & $Php (Join-Path $projectRoot 'vendor/bin/phpunit') --bootstrap tests/religious-bootstrap.php --filter '(Quran|ReligiousContent|DeploymentRunner)'
    $exitCode = $LASTEXITCODE
} finally {
    Pop-Location
    foreach ($key in $testEnvironment.Keys) {
        [Environment]::SetEnvironmentVariable($key, $previousEnvironment[$key], 'Process')
    }
}
exit $exitCode
