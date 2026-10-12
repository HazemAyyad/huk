<?php

namespace Tests\Unit;

use App\Services\Quran\QuranCatalog;
use PHPUnit\Framework\TestCase;

final class QuranCatalogTest extends TestCase
{
    private function catalog(): QuranCatalog
    {
        return new QuranCatalog(dirname(__DIR__, 2) . '/resources/quran/quran.json');
    }

    public function test_canonical_counts_and_distinct_boundaries(): void
    {
        $catalog = $this->catalog();
        self::assertCount(114, $catalog->surahs());
        self::assertCount(6236, $catalog->verses());
        self::assertSame('5:82', $catalog->juzs()[6]['startKey']);
        self::assertSame('2:253', $catalog->juzs()[2]['startKey']);
        self::assertSame('2:1', $catalog->verses(page: 2)[0]['key']);
        self::assertSame('114:6', array_slice($catalog->verses(page: 604), -1)[0]['key']);
    }

    public function test_search_normalizes_only_index_not_original_text(): void
    {
        $catalog = $this->catalog();
        $text = $catalog->verses(surah: 1)[0]['text'];
        $results = $catalog->search('بسم الله', surah: 1);
        self::assertSame('1:1', $results[0]['key']);
        self::assertSame($text, $results[0]['text']);
        self::assertSame([], $catalog->search('   '));
        self::assertSame([], $catalog->search('كلمةغيرموجودة'));
        foreach ($catalog->search('الله', juz: 7) as $verse) {
            self::assertSame(7, $verse['juz']);
        }
    }

    public function test_version_and_text_source_are_explicit(): void
    {
        $manifest = $this->catalog()->manifest();
        self::assertSame('tanzil-uthmani-1.1-metadata-1.0', $manifest['version']);
        self::assertSame('https://tanzil.net', $manifest['source_url']);
        self::assertSame(hash_file('sha256', dirname(__DIR__, 2) . '/resources/quran/quran.json'), $manifest['sha256']);
        self::assertFalse($manifest['capabilities']['audio']);
    }
}
