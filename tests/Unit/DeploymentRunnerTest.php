<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\DeploymentRunner;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DeploymentRunnerTest extends TestCase
{
    public function test_failed_command_stops_before_cache_build_and_queue_restart(): void
    {
        $commands = [];
        $messages = [];
        $success = DeploymentRunner::run('deploy', function ($command, $parameters) use (&$commands) {
            $commands[] = $command;
            if ($command === 'migrate') {
                $this->assertSame(['--force' => true], $parameters);

                return [1, 'migration failed'];
            }

            return [0, 'ok'];
        }, function ($message) use (&$messages) {
            $messages[] = $message;
        });

        $this->assertFalse($success);
        $this->assertSame('migrate', end($commands));
        $this->assertNotContains('cache:clear', $commands);
        $this->assertNotContains('config:cache', $commands);
        $this->assertNotContains('queue:restart', $commands);
        $this->assertContains('Exit code: 1', $messages);
    }

    public function test_exception_stops_execution(): void
    {
        $commands = [];
        $success = DeploymentRunner::run('migrate', function ($command) use (&$commands) {
            $commands[] = $command;
            throw new RuntimeException('test failure');
        }, static function (): void {});

        $this->assertFalse($success);
        $this->assertSame(['config:clear'], $commands);
    }

    public function test_success_runs_the_plan_in_order(): void
    {
        $commands = [];
        $success = DeploymentRunner::run('deploy', function ($command) use (&$commands) {
            $commands[] = $command;

            return [0, 'ok'];
        }, static function (): void {});

        $this->assertTrue($success);
        $this->assertSame(array_column(DeploymentRunner::actions()['deploy']['commands'], 0), $commands);
    }

    public function test_arbitrary_commands_are_rejected_before_execution(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DeploymentRunner::run('migrate:fresh --seed', static function () {
            self::fail('Unapproved commands must never execute.');
        }, static function (): void {});
    }

    public function test_console_is_disabled_without_explicit_enablement(): void
    {
        [$output, $status] = $this->requestConsole('GET', [], false);
        $this->assertSame(404, $status);
        $this->assertSame('Not Found', $output);
    }

    public function test_console_rejects_an_invalid_token_before_bootstrapping_laravel(): void
    {
        [$output, $status] = $this->requestConsole('GET', ['token' => 'wrong']);
        $this->assertSame(403, $status);
        $this->assertSame('Forbidden', $output);
    }

    public function test_valid_get_displays_form_without_running_commands(): void
    {
        [$output, $status] = $this->requestConsole('GET', ['token' => str_repeat('a', 64)]);
        $this->assertSame(200, $status);
        $this->assertStringContainsString('method="post"', $output);
        $this->assertStringContainsString('name="csrf"', $output);
        $this->assertStringNotContainsString('Exit code:', $output);
        $this->assertStringNotContainsString('اكتمل التنفيذ بنجاح', $output);
    }

    public function test_post_without_csrf_cannot_run_migrations(): void
    {
        [$output, $status] = $this->requestConsole('POST', [
            'token' => str_repeat('a', 64), 'action' => 'migrate',
        ]);
        $this->assertSame(419, $status);
        $this->assertStringNotContainsString('Exit code:', $output);
    }

    /** @return array{0: string, 1: int} */
    private function requestConsole(string $method, array $input, bool $enabled = true): array
    {
        // Isolated PHP processes, never Laravel bootstrap or a database connection.
        $script = dirname(__DIR__, 2).'/public/deploy_once.php';
        $code = 'putenv('.var_export('DEPLOY_ONCE_ENABLED='.($enabled ? 'true' : 'false'), true).');'
            .'putenv('.var_export('DEPLOY_ONCE_TOKEN='.str_repeat('a', 64), true).');'
            .'$_SERVER["REQUEST_METHOD"]='.var_export($method, true).';'
            .($method === 'POST' ? '$_POST=' : '$_GET=').var_export($input, true).';'
            .'register_shutdown_function(static function () { fwrite(STDERR, "HTTP_STATUS=".(http_response_code() ?: 200)); });'
            .'require '.var_export($script, true).';';
        $process = proc_open([PHP_BINARY, '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertMatchesRegularExpression('/HTTP_STATUS=\d+$/', $errors);
        preg_match('/HTTP_STATUS=(\d+)$/', $errors, $matches);

        return [$output, (int) $matches[1]];
    }
}
