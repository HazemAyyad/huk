<?php

declare(strict_types=1);

/**
 * Temporary deployment console for hosting without SSH.
 * URL: /deploy_once.php?token=YOUR_DEPLOY_ONCE_TOKEN
 * Explicitly enable in .env; GET never executes Artisan commands.
 * Actions can be repeated with the same token; concurrent execution is blocked.
 */

use App\Support\DeploymentRunner;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

function deployAbort(int $status, string $message): never
{
    http_response_code($status);
    echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    exit;
}

function deployEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$root = dirname(__DIR__);
if (! is_file($root.'/vendor/autoload.php')) {
    deployAbort(503, 'ارفع مجلد vendor أو نفّذ composer install على السيرفر أولًا.');
}
require $root.'/vendor/autoload.php';

try {
    // Read the current .env even when Laravel still has an old config cache.
    $environment = Dotenv::createArrayBacked($root)->safeLoad();
} catch (Throwable) {
    deployAbort(503, 'تعذر قراءة إعدادات النشر من .env.');
}
$setting = static function (string $key) use ($environment): string {
    $serverValue = getenv($key);

    return $serverValue !== false ? $serverValue : (string) ($environment[$key] ?? '');
};

if (! filter_var($setting('DEPLOY_ONCE_ENABLED'), FILTER_VALIDATE_BOOLEAN)) {
    deployAbort(404, 'Not Found');
}
$expectedToken = $setting('DEPLOY_ONCE_TOKEN');
if (strlen($expectedToken) < 32) {
    deployAbort(503, 'اضبط DEPLOY_ONCE_TOKEN بقيمة عشوائية لا تقل عن 32 حرفًا.');
}
$providedToken = $_POST['token'] ?? $_GET['token'] ?? '';
if (! is_string($providedToken) || ! hash_equals($expectedToken, $providedToken)) {
    deployAbort(403, 'Forbidden');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (! in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    deployAbort(405, 'Method Not Allowed');
}

// A file session avoids depending on database/session tables before migrations.
session_name('haq_deploy_once');
session_set_cookie_params([
    'httponly' => true,
    'secure' => ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
    'path' => '/',
]);
if (! session_start()) {
    deployAbort(503, 'تعذر إنشاء جلسة النشر. راجع إعدادات PHP session على السيرفر.');
}
$_SESSION['deploy_csrf'] ??= bin2hex(random_bytes(32));
$csrf = $_SESSION['deploy_csrf'];

$actions = DeploymentRunner::actions();
$action = $_POST['action'] ?? 'deploy';
if (! is_string($action) || ! isset($actions[$action])) {
    deployAbort(422, 'أمر غير مسموح.');
}
if ($method === 'POST') {
    $providedCsrf = $_POST['csrf'] ?? '';
    if (! is_string($providedCsrf) || ! hash_equals($csrf, $providedCsrf)) {
        deployAbort(419, 'انتهت جلسة النموذج. افتح رابط النشر مجددًا.');
    }
    $_SESSION['deploy_csrf'] = bin2hex(random_bytes(32));
    $csrf = $_SESSION['deploy_csrf'];
}
session_write_close();

$output = [];
$success = null;
if ($method === 'POST') {
    $directory = $root.'/storage/framework/deploy-once';
    if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
        deployAbort(503, 'مجلد storage غير قابل للكتابة.');
    }
    // One global lock prevents different deployment actions running concurrently.
    $lock = fopen($directory.'/execution.lock', 'c');
    if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
        deployAbort(409, 'يوجد تنفيذ جارٍ. انتظر اكتماله.');
    }
    try {
        set_time_limit(300);
        define('LARAVEL_START', microtime(true));
        $app = require $root.'/bootstrap/app.php';
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();

        $success = DeploymentRunner::run(
            $action,
            static function (string $command, array $parameters) use ($kernel, $root): array {
                if ($command === 'storage:link' && (file_exists($root.'/public/storage') || is_link($root.'/public/storage'))) {
                    $target = realpath($root.'/storage/app/public');
                    if ($target !== false && is_link($root.'/public/storage') && realpath($root.'/public/storage') === $target) {
                        return [0, 'رابط public/storage موجود وصحيح؛ تم تجاوزه.'];
                    }

                    return [1, 'public/storage موجود لكنه ليس الرابط الصحيح. راجعه قبل المتابعة.'];
                }
                $exitCode = $kernel->call($command, $parameters + ['--no-interaction' => true, '--no-ansi' => true]);

                return [$exitCode, $kernel->output()];
            },
            static function (string $line) use (&$output): void {
                $output[] = $line;
            },
        );
    } catch (Throwable $exception) {
        $success = false;
        $output[] = 'ERROR: '.$exception->getMessage();
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
    http_response_code($success ? 200 : 500);
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>حق — تشغيل أوامر السيرفر</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f4f6f7;color:#132d3a;margin:0;padding:24px}
        main{max-width:800px;margin:auto;background:white;border-radius:16px;padding:24px}
        select,button{font:inherit;padding:12px;margin-top:12px;width:100%;box-sizing:border-box;border-radius:8px}
        button{background:#007d85;color:white;border:0;cursor:pointer}
        pre{direction:ltr;text-align:left;white-space:pre-wrap;overflow-wrap:anywhere;background:#102d3a;color:#edf6f7;padding:16px;border-radius:10px}
        .success{color:#09633c}.failure{color:#a12b2b}li{margin:6px 0}
    </style>
</head>
<body><main>
    <h1>تشغيل أوامر السيرفر — حق</h1>
    <p>اختر العملية ثم اضغط تنفيذ. يمكنك تكرار أي خيار بنفس التوكن. عند الفشل يتوقف التنفيذ ويمكن إعادة المحاولة بعد إصلاح السبب.</p>
    <form method="post" action="deploy_once.php">
        <input type="hidden" name="token" value="<?= deployEscape($providedToken) ?>">
        <input type="hidden" name="csrf" value="<?= deployEscape($csrf) ?>">
        <label for="action">العملية المطلوبة</label>
        <select name="action" id="action">
            <?php foreach ($actions as $key => $plan) { ?>
                <option value="<?= deployEscape($key) ?>" <?= $key === $action ? 'selected' : '' ?>><?= deployEscape($plan['label']) ?></option>
            <?php } ?>
        </select>
        <button type="submit">تنفيذ العملية المختارة</button>
    </form>
    <details><summary>أوامر النشر الكامل</summary><ol>
        <?php foreach ($actions['deploy']['commands'] as [$command, $parameters]) { ?>
            <li><code dir="ltr"><?= deployEscape('php artisan '.$command.(isset($parameters['--force']) ? ' --force' : '')) ?></code></li>
        <?php } ?>
    </ol></details>
    <?php if ($success !== null) { ?>
        <h2 class="<?= $success ? 'success' : 'failure' ?>"><?= $success ? 'اكتمل التنفيذ بنجاح' : 'توقف التنفيذ بسبب خطأ' ?></h2>
        <pre><?= deployEscape(implode("\n", $output)) ?></pre>
    <?php } ?>
    <p>بعد الانتهاء اضبط <code dir="ltr">DEPLOY_ONCE_ENABLED=false</code> في .env. للنشر التالي فعّل الأداة ويمكنك استخدام نفس التوكن.</p>
</main></body></html>
