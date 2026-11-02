<?php

namespace App\Console\Commands;

use App\Services\Religious\PreparedContentCatalog;
use Illuminate\Console\Command;

final class VerifyPreparedContent extends Command
{
    protected $signature = 'haq-app:verify-prepared-content';

    protected $description = 'Verify bundled English/Turkish publisher snapshots without database access';

    public function handle(PreparedContentCatalog $catalog): int
    {
        foreach ($catalog->catalog()['locales'] as $locale => $kinds) {
            foreach ($kinds as $kind => $metadata) {
                $catalog->verify($locale, $kind);
                $this->line($locale.' '.$kind.': '.$metadata['records'].' records verified');
            }
        }

        return self::SUCCESS;
    }
}
