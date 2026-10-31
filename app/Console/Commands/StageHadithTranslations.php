<?php

namespace App\Console\Commands;

use App\Services\Religious\HadithTranslationStager;
use Illuminate\Console\Command;

final class StageHadithTranslations extends Command
{
    protected $signature = 'haq-app:stage-hadeethenc {locale} {mapping : JSON file mapping local content IDs to quoted HadeethEnc IDs} {--archive= : An existing private publisher CSV}';

    protected $description = 'Stage exact, versioned hadith source records for explicit reference review; no database writes or publication';

    public function handle(HadithTranslationStager $stager): int
    {
        try {
            $path = (string) $this->argument('mapping');
            if (! is_file($path) || filesize($path) > 65536) {
                throw new \RuntimeException('hadith_mapping_file_invalid');
            }
            $mapping = json_decode(file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
            if (! is_array($mapping)) {
                throw new \RuntimeException('hadith_mapping_file_invalid');
            }
            $locale = (string) $this->argument('locale');
            $archive = $this->option('archive');
            $result = $archive ? $stager->stageFromFile($locale, $mapping, $archive) : $stager->stage($locale, $mapping);
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Hadith staging failed. Check the explicit references, source locale and archive format. No content was published.');

            return self::FAILURE;
        }
    }
}
