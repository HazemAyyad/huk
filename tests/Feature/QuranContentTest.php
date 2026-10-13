<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class QuranContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['quran.content_cache_store' => 'array']);
        Http::preventStrayRequests();
    }

    public function test_sources_are_cached_without_mutating_original_payloads(): void
    {
        $data = ['tafsir' => ['resource_id' => 91, 'verses' => ['2:2' => ['id' => 9]], 'text' => '<p>نص المصدر</p>']];
        Http::fake(['api.quran.com/*' => Http::response($data)]);
        $this->getJson('/api/v1/quran/content/tafsirs/91/2/2')->assertOk()->assertJsonPath('data.tafsir.text', '<p>نص المصدر</p>');
        $this->getJson('/api/v1/quran/content/tafsirs/91/2/2')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_partial_pages_and_mismatched_references_are_not_served(): void
    {
        Http::fake(['api.quran.com/*' => Http::response(['verses' => [['verse_key' => '2:2']], 'pagination' => ['next_page' => 2]]), 'quranenc.com/*' => Http::response(['result' => ['sura' => 2, 'aya' => 3]])]);
        $this->getJson('/api/v1/quran/content/pages/2')->assertStatus(502);
        $this->getJson('/api/v1/quran/content/meanings/2/2')->assertStatus(502);
    }

    public function test_invalid_boundaries_never_call_a_provider(): void
    {
        $this->getJson('/api/v1/quran/content/pages/605')->assertUnprocessable();
        $this->getJson('/api/v1/quran/content/tafsirs/999/2/2')->assertUnprocessable();
        $this->getJson('/api/v1/quran/content/meanings/1/8')->assertUnprocessable();
        Http::assertNothingSent();
    }
}
