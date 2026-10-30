<?php

namespace App\Console\Commands;

use App\Services\Religious\QuranTranslationStager;
use Illuminate\Console\Command;

final class StageQuranTranslations extends Command
{
    protected $signature = 'haq-app:stage-quranenc {translation : Exact key from the official catalog} {--locale= : Target app locale}';

    protected $description = 'Stage unmodified QuranEnc translations for current Quran content; no DB writes or publication';

    public function handle(QuranTranslationStager $stager): int
    {
        try {
            $result = $stager->stage((string) $this->option('locale'), (string) $this->argument('translation'));
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Staging failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
