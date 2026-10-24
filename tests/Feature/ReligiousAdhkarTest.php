<?php

namespace Tests\Feature;

use App\Services\Quran\QuranCatalog;
use Tests\TestCase;

/** File-backed public catalog only; no DB or network. */
final class ReligiousAdhkarTest extends TestCase
{
    public function test_public_dataset_checksum_and_all_group_members_are_valid(): void
    {
        $manifest = $this->getJson('/api/v1/religious/adhkar/manifest')->assertOk();
        $response = $this->get('/api/v1/religious/adhkar/dataset')->assertOk();
        $this->assertSame(hash('sha256', $response->getContent()), $manifest['data']['sha256']);
        $data = $response->json();
        $this->assertCount(14, $data['groups']);
        $this->assertCount(267, $data['items']);
        $items = array_column($data['items'], null, 'id');
        foreach ($data['groups'] as $group) {
            $this->assertNotEmpty($group['item_ids']);
            $this->assertSame(count(array_unique($group['item_ids'])), count($group['item_ids']));
            foreach ($group['item_ids'] as $id) {
                $this->assertArrayHasKey($id, $items);
            }
        }
        $this->assertSame(7, $items['hisn-api-083']['target']);
        $this->assertSame(3, $items['hisn-api-130']['target']);
        $this->assertSame(7, $items['hisn-api-148']['target']);
        $this->assertNull($items['hisn-api-153']['audio_url']);
    }

    public function test_every_embedded_scripture_fragment_is_verbatim_from_haq_quran(): void
    {
        $data = json_decode(file_get_contents(resource_path('religious/adhkar.json')), true, flags: JSON_THROW_ON_ERROR);
        $verses = array_column(app(QuranCatalog::class)->dataset()['verses'], 'text', 'key');
        foreach ($data['items'] as $item) {
            foreach ($item['scripture'] as $fragment) {
                $canonical = implode(' ', array_map(fn ($key) => $verses[$key], $fragment['keys']));
                $this->assertStringContainsString($fragment['text'], $canonical, $item['id']);
                $this->assertStringContainsString('﴿'.$fragment['text'].'﴾', $item['text'], $item['id']);
            }
        }
        $groups = array_column($data['groups'], 'item_ids', 'id');
        $this->assertNotContains('hisn-api-097', $groups['daily:morning']);
        foreach (['hisn-api-093', 'hisn-api-094', 'hisn-api-095'] as $id) {
            $this->assertNotContains($id, $groups['daily:evening']);
        }
    }
}
