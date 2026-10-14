<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
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

    public function test_page_proxy_requests_the_tajweed_layout_and_never_reuses_v2_layout_cache(): void
    {
        $url = 'https://api.quran.com/api/v4/verses/by_page/2?mushaf=1&words=true&word_fields=code_v2,text_qpc_hafs,page_number,line_number&per_page=50';
        Cache::store('array')->put('quran-content-v1:'.hash('sha256', $url), ['verses' => [['verse_key' => 'stale-v2']]], now()->addDay());
        $data = ['verses' => [['verse_key' => '2:1', 'words' => [
            ['position' => 1, 'page_number' => 2, 'line_number' => 3, 'code_v2' => 'ﱁ'],
        ]]], 'pagination' => ['next_page' => null]];
        Http::fake(['api.quran.com/*' => Http::response($data)]);

        $this->getJson('/api/v1/quran/content/pages/2')->assertOk()
            ->assertJsonPath('data.verses.0.verse_key', '2:1')
            ->assertJsonPath('data.verses.0.words.0.code_v2', 'ﱁ');
        $this->getJson('/api/v1/quran/content/pages/2')->assertOk();
        Http::assertSent(fn ($request) => str_contains($request->url(), 'mushaf=19'));
        Http::assertSentCount(1);
    }

    public function test_page_with_invalid_physical_word_layout_is_rejected(): void
    {
        Http::fake(['api.quran.com/*' => Http::response(['verses' => [
            ['verse_key' => '2:1', 'words' => [['page_number' => 2, 'line_number' => 16, 'code_v2' => 'ﱁ']]],
        ], 'pagination' => ['next_page' => null]])]);
        $this->getJson('/api/v1/quran/content/pages/2')->assertStatus(502);
    }

    public function test_invalid_boundaries_never_call_a_provider(): void
    {
        $this->getJson('/api/v1/quran/content/pages/605')->assertUnprocessable();
        $this->getJson('/api/v1/quran/content/tafsirs/999/2/2')->assertUnprocessable();
        $this->getJson('/api/v1/quran/content/meanings/1/8')->assertUnprocessable();
        Http::assertNothingSent();
    }
}
