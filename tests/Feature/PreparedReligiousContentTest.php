<?php

namespace Tests\Feature;

use App\Services\Religious\PreparedContentCatalog;
use RuntimeException;
use Tests\TestCase;

final class PreparedReligiousContentTest extends TestCase
{
    public function test_real_sources_cover_the_complete_quran_and_tafsir_without_blank_continuations(): void
    {
        $library = new PreparedContentCatalog;
        foreach (['en', 'tr'] as $locale) {
            $meanings = $library->verify($locale, 'quran');
            $tafsir = $library->verify($locale, 'tafsir');
            $this->assertCount(6236, $meanings['verses']);
            $this->assertCount(6236, $tafsir['verses']);
            foreach (['1:1', '2:9', '2:255', '18:10', '114:6'] as $key) {
                $this->assertNotEmpty($meanings['verses'][$key]['text']);
                foreach ($tafsir['verses'][$key] as $id) {
                    $this->assertNotEmpty($tafsir['blocks'][$id]['text']);
                }
            }
            $this->assertSame($locale, $meanings['source']['language_iso_code']);
        }
    }

    public function test_every_name_and_dhikr_retains_its_original_identity_and_source(): void
    {
        $library = new PreparedContentCatalog;
        foreach (['en', 'tr'] as $locale) {
            $names = $library->verify($locale, 'names');
            $dhikr = $library->verify($locale, 'dhikr');
            $this->assertCount(99, $names['entries']);
            $this->assertCount(267, $dhikr['entries']);
            $this->assertSame([], $dhikr['unavailable']);
            $this->assertNotEmpty($names['entries']['name-67']['text']);
            foreach ($dhikr['entries'] as $entry) {
                $this->assertContains($entry['alignment'], ['arabic_exact', 'publisher_record_variant']);
                $this->assertStringStartsWith('https://', $entry['source_url']);
            }
        }
    }

    public function test_canonical_hadiths_keep_all_publisher_fields_and_document_version(): void
    {
        $library = new PreparedContentCatalog;
        foreach (['en', 'tr'] as $locale) {
            $data = $library->verify($locale, 'hadith');
            foreach (range(1, 42) as $number) {
                $id = sprintf('nawawi-%02d', $number);
                $this->assertNotEmpty($data['entries'][$id]['fields']['hadith_text']);
                $this->assertNotEmpty($data['entries'][$id]['source_arabic']);
            }
            $this->assertSame('4944', $data['entries']['ramadan-last-ten-hadith']['source_id']);
            $this->assertStringContainsString('# Source: https://hadeethenc.com/'.$locale, $data['source']['document']);
            $this->assertStringContainsString('(v'.$data['source']['version'].')', $data['source']['document']);
        }
    }

    public function test_unknown_or_traversal_paths_cannot_read_private_sources(): void
    {
        $this->expectException(RuntimeException::class);
        (new PreparedContentCatalog)->metadata('../private', '../env');
    }

    public function test_every_existing_textual_catalog_item_has_a_source_in_both_languages(): void
    {
        $library = new PreparedContentCatalog;
        foreach (['en', 'tr'] as $locale) {
            $quran = $library->verify($locale, 'quran');
            $dhikr = $library->verify($locale, 'dhikr');
            $names = $library->verify($locale, 'names');
            $hadith = $library->verify($locale, 'hadith');
            $mapped = array_unique([...array_keys($quran['references']), ...array_keys($dhikr['chapters']),
                ...array_keys($dhikr['additional_content']), ...array_keys($dhikr['aliases']),
                ...array_keys($names['entries']), ...array_keys($hadith['entries'])]);
            $this->assertCount(332, $mapped);
            foreach (app(\App\Services\Religious\ReligiousCatalog::class)->dataset()['items'] as $item) {
                if ($item['type'] === 'playlist') {
                    $this->assertSame('ruqya-complete', $item['id']);
                } else {
                    $this->assertContains($item['id'], $mapped, $locale.' '.$item['id']);
                }
            }
        }
    }

    public function test_prepared_sources_are_read_only_and_separate_from_signed_draft_releases(): void
    {
        config(['haq_app.enabled' => true]);
        $this->getJson('/api/v2/app-content/prepared')->assertOk()->assertJsonPath('data.schema', 1);
        $response = $this->get('/api/v2/app-content/prepared/tr/names');
        $response->assertOk()->assertHeader('Content-Type', 'application/gzip');
        $meta = (new PreparedContentCatalog)->metadata('tr', 'names');
        $this->assertSame($meta['sha256'], hash('sha256', $response->getContent()));
        $this->getJson('/api/v2/app-content/prepared/ar/names')->assertNotFound();
    }
}
