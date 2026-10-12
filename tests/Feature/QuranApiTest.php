<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Public file-backed API: no database traits, migrations, or fixtures. */
final class QuranApiTest extends TestCase
{
    public function test_manifest_and_catalog_are_available_to_guests(): void
    {
        $this->getJson('/api/v1/quran/manifest')->assertOk()
            ->assertJsonPath('data.verse_count', 6236)
            ->assertJsonPath('data.capabilities.audio', false);
        $this->getJson('/api/v1/quran/surahs')->assertOk()->assertJsonCount(114, 'data');
        $this->getJson('/api/v1/quran/juzs')->assertOk()->assertJsonPath('data.6.startKey', '5:82');
    }

    public function test_search_and_pagination_use_the_canonical_text(): void
    {
        $this->getJson('/api/v1/quran/search?q=' . urlencode('الصلاة') . '&surah=2')
            ->assertOk()->assertJsonPath('data.0.key', '2:3');
        $this->getJson('/api/v1/quran/verses?surah=2&limit=2')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.next_cursor', 2);
        $this->getJson('/api/v1/quran/verses?surah=2&limit=2&cursor=2')
            ->assertOk()->assertJsonPath('data.0.key', '2:3');
    }

    public function test_invalid_boundaries_are_rejected(): void
    {
        $this->getJson('/api/v1/quran/verses?page=605')->assertUnprocessable();
        $this->getJson('/api/v1/quran/verses?surah=0')->assertUnprocessable();
        $this->getJson('/api/v1/quran/search?q=' . urlencode('الله') . '&juz=31')->assertUnprocessable();
        $this->getJson('/api/v1/quran/search')->assertUnprocessable();
    }
}
