<?php

namespace Tests\Feature;

use App\Services\Quran\QuranCatalog;
use App\Services\Religious\QuranTranslationStager;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class HaqAppSourcesTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/haq-source-'.bin2hex(random_bytes(8)));
        mkdir($this->directory, 0700, true);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function source(bool $incomplete = false): void
    {
        $verses = app(QuranCatalog::class)->dataset()['verses'];
        Http::fake(function ($request) use ($verses, $incomplete) {
            if (str_contains($request->url(), 'translations/list')) {
                return Http::response(['translations' => [['key' => 'english_test', 'language_iso_code' => 'en',
                    'version' => '1.0.1', 'last_update' => 100, 'title' => 'Test published translation', 'description' => 'Full original metadata']]]);
            }
            preg_match('~/translation/sura/english_test/(\d+)$~', $request->url(), $match);
            $surah = (int) ($match[1] ?? 0);
            $rows = [];
            foreach ($verses as $verse) {
                [$s, $a] = explode(':', $verse['key']);
                if ((int) $s === $surah) {
                    $rows[] = ['id' => $verse['key'], 'sura' => $s, 'aya' => $a, 'arabic_text' => $verse['text'],
                        'translation' => 'Source text '.$verse['key'], 'footnotes' => '  Source footnote <b>unchanged</b>  '];
                }
            }
            if ($incomplete) {
                array_pop($rows);
            }

            return Http::response(['result' => $rows]);
        });
    }

    public function test_source_import_keeps_exact_rows_and_footnotes_and_is_never_automatically_reviewed(): void
    {
        $this->source();
        $original = app(ReligiousCatalog::class)->dataset();
        $result = app(QuranTranslationStager::class)->stage('en', 'english_test', $this->directory);
        $raw = file_get_contents($result['path']);
        $package = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($package['reviewed']);
        $this->assertSame(hash('sha256', $raw), $result['sha256']);
        $this->assertSame('1.0.1', $package['source']['version']);
        $this->assertSame('Full original metadata', $package['source']['description']);
        foreach ($package['surahs'] as $sura) {
            $this->assertSame(hash('sha256', $sura['raw_json']), $sura['sha256']);
            $rows = json_decode($sura['raw_json'], true)['result'];
            $this->assertSame('  Source footnote <b>unchanged</b>  ', $rows[0]['footnotes']);
        }
        $this->assertSame($original, app(ReligiousCatalog::class)->dataset());
    }

    public function test_missing_source_verse_leaves_no_staged_package(): void
    {
        $this->source(true);
        try {
            app(QuranTranslationStager::class)->stage('en', 'english_test', $this->directory);
            $this->fail('Incomplete translations cannot be staged.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Incomplete surah', $exception->getMessage());
        }
        $this->assertSame([], glob($this->directory.'/*'));
    }

    public function test_provider_keys_and_language_mapping_cannot_fetch_arbitrary_urls(): void
    {
        $this->source();
        $this->expectException(\RuntimeException::class);
        app(QuranTranslationStager::class)->stage('fr', 'english_test', $this->directory);
    }
}
