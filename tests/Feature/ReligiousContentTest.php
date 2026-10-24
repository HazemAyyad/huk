<?php

namespace Tests\Feature;

use App\Services\Quran\QuranCatalog;
use App\Services\Religious\ReligiousCatalog;
use App\Services\Religious\ReligiousSync;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** File-backed contracts only: no live DB, migrations, or external requests. */
final class ReligiousContentTest extends TestCase
{
    private string $directory;

    private ReligiousCatalog $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/religious-'.bin2hex(random_bytes(8)));
        mkdir($this->directory, 0775, true);
        $this->catalog = new ReligiousCatalog($this->directory.'/catalog.json');
        $this->app->instance(ReligiousCatalog::class, $this->catalog);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_guest_catalog_checksum_matches_the_exact_dataset_bytes(): void
    {
        $manifest = $this->getJson('/api/v1/religious/manifest')->assertOk();
        $dataset = $this->get('/api/v1/religious/dataset')->assertOk();
        $this->assertSame($manifest['data']['sha256'], hash('sha256', $dataset->getContent()));
        $this->assertSame(42, count(array_filter($dataset->json('items'), fn ($i) => in_array('nawawi', $i['categories']))));
        $this->assertSame(99, count(array_filter($dataset->json('items'), fn ($i) => in_array('names', $i['categories']))));
        $this->assertSame(14, count(array_filter($dataset->json('categories'), fn ($c) => $c['parent'] === null)));
    }

    public function test_arabic_search_filters_and_unknown_content_are_handled(): void
    {
        $this->getJson('/api/v1/religious/content?category=friday&tag='.urlencode('بعد الجمعة'))->assertOk();
        $this->getJson('/api/v1/religious/content?category=quran-duas&q='.urlencode('ربنا اتنا'))->assertOk()
            ->assertJsonPath('data.0.id', 'qdua-goodness');
        $this->getJson('/api/v1/religious/content?category=nawawi&per_page=100')->assertJsonCount(42, 'data');
        $this->getJson('/api/v1/religious/content?category=unknown')->assertUnprocessable();
        $this->getJson('/api/v1/religious/content/unknown')->assertNotFound();
        $this->getJson('/api/v1/religious/content?per_page=10000')->assertUnprocessable();
        $this->postJson('/api/v1/religious/sync', ['operations' => []])->assertUnauthorized();
    }

    public function test_curated_moods_ramadan_and_library_are_explicit_and_hide_unpublished_references(): void
    {
        $data = $this->catalog->dataset();
        $this->assertCount(17, $data['moods']);
        $this->assertCount(30, $data['ramadan_days']);
        $this->assertStringContainsString('لا تثبت', $data['ramadan_days_note']);
        $items = array_column($data['items'], null, 'id');
        foreach ($data['moods'] as $mood) {
            $this->assertNotEmpty($mood['item_ids']);
            foreach ($mood['item_ids'] as $id) {
                $this->assertContains('mood-'.$mood['id'], $items[$id]['categories']);
            }
        }
        $this->assertCount(42, array_filter($items, fn ($item) => in_array('hadith-comprehensive', $item['categories'], true)));
        $hidden = $items['hisn-034'];
        $hidden['published'] = false;
        $this->catalog->saveItem($hidden);
        $published = $this->catalog->dataset();
        foreach ($published['moods'] as $mood) {
            $this->assertNotContains('hisn-034', $mood['item_ids']);
        }
    }

    public function test_old_admin_catalog_gets_new_features_without_reverting_edits_or_resurrecting_removed_items(): void
    {
        $bundle = $this->catalog->all();
        $added = $bundle['bundled_extensions'];
        $legacy = ['version' => 'old-admin', 'sources' => $bundle['sources'],
            'categories' => array_values(array_filter($bundle['categories'], fn ($category) => ! in_array($category['id'], $added['category_ids'], true))),
            'items' => array_values(array_filter($bundle['items'], fn ($item) => ! in_array($item['id'], $added['item_ids'], true) && $item['id'] !== 'hisn-034'))];
        foreach ($legacy['items'] as &$item) {
            $item['categories'] = array_values(array_diff($item['categories'], $added['category_ids']));
            if ($item['id'] === 'hisn-035') {
                $item['title'] = 'تحرير الإدارة القديم';
            }
        }
        unset($item);
        file_put_contents($this->directory.'/catalog.json', json_encode($legacy, JSON_THROW_ON_ERROR));
        $result = $this->catalog->dataset();
        $items = array_column($result['items'], null, 'id');
        $this->assertSame(2, $result['schema_version']);
        $this->assertCount(17, $result['moods']);
        $this->assertArrayNotHasKey('hisn-034', $items);
        $this->assertSame('تحرير الإدارة القديم', $items['hisn-035']['title']);
        $this->assertContains('mood-sad', $items['hisn-035']['categories']);
        $this->assertArrayHasKey('ramadan-qadr-dua', $items);
        $this->assertSame($result['version'], $this->catalog->dataset()['version']);
    }

    public function test_scripture_is_verbatim_and_raising_hands_has_its_correct_evidence(): void
    {
        $verses = array_column(app(QuranCatalog::class)->dataset()['verses'], null, 'key');
        foreach ($this->catalog->dataset()['items'] as $item) {
            if ($item['type'] === 'quran') {
                $this->assertSame(implode("\n", array_map(fn ($id) => $verses[$id]['text'], $item['verseKeys'])), $item['text']);
            }
        }
        $hands = $this->catalog->item('sunnah-hands');
        $this->assertStringContainsString('735', $hands['source']);
        $this->assertStringContainsString('مَنْكِبَيْهِ', $hands['text']);
        $this->assertStringNotContainsString('الْوُضُوءَ', $hands['text']);
    }

    public function test_draft_edits_do_not_leak_and_published_changes_have_new_version(): void
    {
        $old = $this->catalog->dataset()['version'];
        $item = $this->catalog->item('sunnah-wash');
        $item['published'] = false;
        $this->catalog->saveItem($item);
        $this->assertNotSame($old, $this->catalog->dataset()['version']);
        $this->getJson('/api/v1/religious/content/sunnah-wash')->assertNotFound();
        $this->assertNotNull($this->catalog->item('sunnah-wash', false));
        $item['published'] = true;
        $this->catalog->saveItem($item);
        $this->getJson('/api/v1/religious/content/sunnah-wash')->assertOk();
    }

    public function test_admin_cannot_save_an_altered_quran_verse(): void
    {
        $item = $this->catalog->item('qdua-goodness');
        $item['text'] .= ' نص إضافي';
        $this->expectException(ValidationException::class);
        $this->catalog->saveItem($item);
    }

    public function test_category_cycle_is_rejected(): void
    {
        $category = collect($this->catalog->all()['categories'])->firstWhere('id', 'sunnah-prayer');
        $category['parent'] = 'sunnah-prayer';
        $this->expectException(ValidationException::class);
        $this->catalog->saveCategory($category);
    }

    public function test_category_order_and_hidden_children_are_respected(): void
    {
        $category = collect($this->catalog->all()['categories'])->firstWhere('id', 'friday');
        $category['position'] = 1;
        $this->catalog->saveCategory($category);
        $this->assertSame('friday', $this->catalog->dataset()['categories'][0]['id']);
        $child = collect($this->catalog->all()['categories'])->firstWhere('id', 'sunnah-prayer');
        $child['published'] = false;
        $this->catalog->saveCategory($child);
        $this->getJson('/api/v1/religious/content/sunnah-hands')->assertNotFound();
        $this->getJson('/api/v1/religious/content/sunnah-wash')->assertOk()->assertJsonPath('data.categories', ['friday']);
    }

    public function test_administration_templates_compile_as_valid_php(): void
    {
        foreach (['admin/religious/index', 'admin/religious/edit'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view.'.blade.php'));
            $compiled = \Illuminate\Support\Facades\Blade::compileString($source);
            $this->assertNotEmpty(token_get_all($compiled, TOKEN_PARSE));
        }
    }

    public function test_sync_validates_identities_and_actual_audio_bounds_without_database(): void
    {
        $sync = new ReligiousSync($this->catalog);
        $sync->validateOperation(['key' => 'favorite:nawawi-01', 'deleted' => false, 'value' => true]);
        $sync->validateOperation(['key' => 'playback:ruqya-complete', 'deleted' => false, 'value' => ['seconds' => 15, 'index' => 3]]);
        $this->expectException(ValidationException::class);
        $sync->validateOperation(['key' => 'playback:ruqya-complete', 'deleted' => false, 'value' => ['seconds' => 15, 'index' => 9999]]);
    }

    public function test_sync_replays_conflicts_tombstones_and_account_isolation_in_memory_only(): void
    {
        config(['database.default' => 'religious_testing', 'database.connections.religious_testing' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $schema = \Illuminate\Support\Facades\Schema::connection('religious_testing');
        $this->assertSame(':memory:', config('database.connections.religious_testing.database'));
        $schema->create('religious_accounts', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('content_revision')->default(0);
            $table->timestamps();
        });
        $schema->create('religious_content_sync_entities', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('religious_account_id');
            $table->string('entity_key');
            $table->text('value')->nullable();
            $table->boolean('deleted');
            $table->unsignedBigInteger('revision');
            $table->unique(['religious_account_id', 'entity_key']);
        });
        $schema->create('religious_content_sync_operations', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('religious_account_id');
            $table->uuid('operation_id');
            $table->boolean('conflicted');
            $table->unique(['religious_account_id', 'operation_id']);
        });
        $first = \App\Models\ReligiousAccount::create(['name' => 'A', 'email' => 'a@example.test', 'password' => 'test-password']);
        $second = \App\Models\ReligiousAccount::create(['name' => 'B', 'email' => 'b@example.test', 'password' => 'test-password']);
        $sync = new ReligiousSync($this->catalog);
        $operation = ['id' => (string) \Illuminate\Support\Str::uuid(), 'key' => 'favorite:nawawi-01',
            'base_revision' => 0, 'value' => true, 'deleted' => false];
        $saved = $sync->exchange($first, [$operation]);
        $this->assertSame(1, $saved['revision']);
        $this->assertSame(1, $sync->exchange($first, [$operation])['revision']);
        $delete = [...$operation, 'id' => (string) \Illuminate\Support\Str::uuid(), 'base_revision' => 1, 'value' => null, 'deleted' => true];
        $this->assertSame(2, $sync->exchange($first, [$delete])['revision']);
        $stale = $sync->exchange($first, [[...$operation, 'id' => (string) \Illuminate\Support\Str::uuid()]]);
        $this->assertSame(['favorite:nawawi-01'], $stale['conflicts']);
        $this->assertTrue($stale['entities']->{'favorite:nawawi-01'}['deleted']);
        $this->assertSame([], (array) $sync->exchange($second, [])['entities']);
        $this->assertSame(1, $sync->exchange($second, [$operation])['revision']);
    }

    public function test_staff_token_cannot_use_the_religious_account_sync(): void
    {
        $staff = new \App\Models\User;
        $staff->id = 999;
        $this->actingAs($staff, 'sanctum');
        $this->postJson('/api/v1/religious/sync', ['operations' => []])->assertForbidden();
    }
}
