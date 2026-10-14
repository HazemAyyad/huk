<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;
use Throwable;

final class DeploymentRunner
{
    /** @return array<string, array{label: string, commands: list<array{0: string, 1: array<string, mixed>}>}> */
    public static function actions(): array
    {
        return [
            'deploy' => [
                'label' => 'النشر الكامل: تحديث قاعدة البيانات وتجهيز الملفات والكاش',
                'commands' => [
                    ['config:clear', []],
                    ['route:clear', []],
                    ['view:clear', []],
                    ['event:clear', []],
                    ['storage:link', []],
                    ['migrate', ['--force' => true]],
                    // Clear a database-backed cache only after its tables exist.
                    ['cache:clear', []],
                    ['config:cache', []],
                    ['view:cache', []],
                    ['queue:restart', []],
                ],
            ],
            'migrate' => [
                'label' => 'تشغيل المايجريشن فقط — migrate --force',
                'commands' => [['config:clear', []], ['migrate', ['--force' => true]]],
            ],
            'clear-caches' => [
                'label' => 'مسح الكاش — optimize:clear',
                'commands' => [['optimize:clear', []]],
            ],
            'config-cache' => [
                'label' => 'إعادة بناء كاش الإعدادات — config:cache',
                'commands' => [['config:cache', []]],
            ],
            'route-cache' => [
                'label' => 'إعادة بناء كاش الراوت — route:cache',
                'commands' => [['route:cache', []]],
            ],
            'storage-link' => [
                'label' => 'ربط مجلد التخزين — storage:link',
                'commands' => [['storage:link', []]],
            ],
            'queue-restart' => [
                'label' => 'طلب إعادة تشغيل عمال الطابور — queue:restart',
                'commands' => [['queue:restart', []]],
            ],
        ];
    }

    /**
     * The executor returns [exit code, output]. Never accept raw shell input.
     *
     * @param  callable(string, array<string, mixed>): array{0: int, 1: string}  $execute
     * @param  callable(string): void  $report
     */
    public static function run(string $action, callable $execute, callable $report): bool
    {
        $plan = self::actions()[$action] ?? throw new InvalidArgumentException('Unknown deployment action.');

        foreach ($plan['commands'] as [$command, $parameters]) {
            $report('>>> php artisan '.$command.(isset($parameters['--force']) ? ' --force' : ''));

            try {
                [$exitCode, $output] = $execute($command, $parameters);
                $report($output);
                $report('Exit code: '.$exitCode);
            } catch (Throwable $exception) {
                $report('ERROR: '.$exception->getMessage());
                $report('توقف التنفيذ؛ لم تُشغّل الأوامر التالية.');

                return false;
            }

            if ($exitCode !== 0) {
                $report('توقف التنفيذ؛ لم تُشغّل الأوامر التالية.');

                return false;
            }
        }

        return true;
    }
}
