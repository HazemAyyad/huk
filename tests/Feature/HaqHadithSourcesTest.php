<?php

namespace Tests\Feature;

use App\Services\Religious\HadithSourceArchive;
use App\Services\Religious\HadithTranslationStager;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class HaqHadithSourcesTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/hadith-source-'.bin2hex(random_bytes(8)));
        mkdir($this->directory, 0700, true);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') as $path) {
            unlink($path);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function csv(string $locale = 'en', string $id = '123'): string
    {
        return \Tests\Support\HadithSourceFixture::csv($locale, $id);
    }

    private function stage(string $csv, array $mapping = ['nawawi-01' => '123']): array
    {
        $path = $this->directory.'/input.csv';
        file_put_contents($path, $csv);

        return app(HadithTranslationStager::class)->stageFromFile('en', $mapping, $path, $this->directory);
    }

    public function test_archive_preserves_full_publisher_fields_edition_and_original_content_without_approval(): void
    {
        $before = app(ReligiousCatalog::class)->dataset();
        $csv = $this->csv();
        $result = $this->stage($csv);
        $json = file_get_contents($result['path']);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $verified = app(HadithSourceArchive::class)->validate($json, 'en', $result['sha256']);
        $this->assertFalse($data['reviewed']);
        $this->assertSame('1.25.0', $verified['source']['version']);
        $this->assertSame('unspecified', $verified['source']['update_timezone']);
        $this->assertSame(hash('sha256', $csv), $verified['source']['archive_sha256']);
        $this->assertSame(strlen($csv), $verified['source']['archive_bytes']);
        $this->assertSame(1, $verified['source']['archive_records']);
        $this->assertCount(15, $verified['records'][123]);
        $this->assertSame("  Fixture text with \"quoted\" words, comma\nand a new line  ", $verified['records'][123]['hadith_text']);
        $this->assertSame('<p>Exact explanation &amp; formatting</p>', $verified['records'][123]['explanation']);
        $this->assertSame('Original benefits', $verified['records'][123]['benefits']);
        $this->assertSame('https://hadeethenc.com/en/browse/hadith/123', $verified['records'][123]['link']);
        $this->assertSame($before, app(ReligiousCatalog::class)->dataset());
        $this->assertSame(1, $result['items']);
    }

    public function test_missing_source_identity_cannot_produce_an_artifact(): void
    {
        $this->expectExceptionMessage('hadith_mapped_record_missing');
        $this->stage($this->csv('en', '999'));
    }

    public function test_wrong_source_language_is_rejected_without_accepting_a_title_match(): void
    {
        $this->expectException(RuntimeException::class);
        $this->stage($this->csv('fr'));
    }

    public function test_duplicate_ids_and_unknown_local_references_are_rejected(): void
    {
        try {
            $this->stage($this->csv(), ['not-a-current-hadith' => '123']);
            $this->fail('Unknown local reference was accepted.');
        } catch (RuntimeException $e) {
            $this->assertSame('hadith_reference_invalid', $e->getMessage());
        }
        $csv = $this->csv();
        $handle = fopen('php://temp', 'w+b');
        fwrite($handle, $csv);
        rewind($handle);
        fgetcsv($handle, 0, ',', '"', '');
        fgetcsv($handle, 0, ',', '"', '');
        $offset = ftell($handle);
        fclose($handle);
        $this->expectExceptionMessage('hadith_archive_record_invalid');
        $this->stage($csv.substr($csv, $offset));
    }

    public function test_changed_translation_cannot_pass_against_preserved_raw_csv_even_with_a_new_artifact_hash(): void
    {
        $result = $this->stage($this->csv());
        $data = json_decode(file_get_contents($result['path']), true, 512, JSON_THROW_ON_ERROR);
        $data['records'][123]['fields']['hadith_text'] = 'Changed content';
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $this->expectExceptionMessage('hadith_record_fields_changed');
        app(HadithSourceArchive::class)->validate($json, 'en', hash('sha256', $json));
    }

    public function test_changed_local_original_invalidates_the_mapping(): void
    {
        $result = $this->stage($this->csv());
        $data = json_decode(file_get_contents($result['path']), true, 512, JSON_THROW_ON_ERROR);
        $data['references']['nawawi-01']['original_sha256'] = str_repeat('0', 64);
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $this->expectExceptionMessage('hadith_original_changed');
        app(HadithSourceArchive::class)->validate($json, 'en', hash('sha256', $json));
    }

    public function test_fixed_host_download_streams_to_private_storage_and_rejects_redirects(): void
    {
        Http::fake(['https://hadeethenc.com/en/browse/download/en/csv' => Http::sequence()->push($this->csv())->push('', 302, ['Location' => 'https://untrusted.test/archive'])]);
        $stager = new HadithTranslationStager(app(HadithSourceArchive::class), $this->directory);
        $result = $stager->stage('en', ['nawawi-01' => '123']);
        $this->assertFileExists($result['path']);
        $this->assertCount(1, glob($this->directory.'/hadeethenc-en-*.csv'));
        $this->assertSame([], glob($this->directory.'/*.tmp'));
        Http::assertSent(fn ($request) => $request->url() === 'https://hadeethenc.com/en/browse/download/en/csv');
        $this->expectExceptionMessage('hadith_download_failed');
        $stager->stage('en', ['nawawi-01' => '123']);
    }
}
