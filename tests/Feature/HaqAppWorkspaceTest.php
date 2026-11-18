<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppManagedContent;
use App\Services\Religious\AppSessionProof;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class HaqAppWorkspaceTest extends TestCase
{
    public function test_audio_upload_requires_global_catalog_edit_permission(): void
    {
        Storage::fake('public');
        [$token] = $this->admin(['admin.enter', 'catalogs.review']);
        $this->withToken($token)->postJson($this->url('audio/media'), ['type' => 'dhikr', 'url' => 'https://127.0.0.1/a.mp3'])->assertForbidden();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_audio_upload_stays_a_draft_until_review_and_publication(): void
    {
        Storage::fake('public');
        [$token] = $this->admin();
        $before = app(AppManagedContent::class)->published()['audio'];
        $file = UploadedFile::fake()->createWithContent('my-voice.mp3', 'ID3'.str_repeat("\0", 1500));
        $uploaded = $this->withToken($token)->post($this->url('audio/media'), ['type' => 'dhikr', 'file' => $file])->assertOk()->json('data');
        $this->assertStringStartsWith('https://', $uploaded['url']);
        $this->assertSame(1503, $uploaded['bytes']);
        $this->assertSame($before, app(AppManagedContent::class)->published()['audio']);
        $record = ['id' => 'test-dhikr', 'title' => 'نداء خاص', 'type' => 'dhikr', 'url' => $uploaded['url'], 'source' => 'تسجيلي', 'source_url' => 'https://example.test/source', 'published' => true];
        $this->withToken($token)->postJson($this->url('audio'), ['record' => $record, 'revision' => 0])->assertOk();
        $this->assertSame($before, app(AppManagedContent::class)->published()['audio']);
        $this->withToken($token)->postJson($this->url('audio/catalog/action'), ['action' => 'review', 'revision' => 1])->assertOk();
        $this->withToken($token)->postJson($this->url('audio/catalog/action'), ['action' => 'publish', 'revision' => 1])->assertOk();
        $this->assertSame($uploaded['url'], collect(app(AppManagedContent::class)->published()['audio'])->firstWhere('id', 'test-dhikr')['url']);
        $this->assertCount(1, Storage::disk('public')->allFiles('haq-audio'));
    }

    public function test_recitation_uploads_share_a_folder_and_never_overwrite_completed_surahs(): void
    {
        Storage::fake('public');
        [$token] = $this->admin();
        $file = fn () => UploadedFile::fake()->createWithContent('002.mp3', 'ID3'.str_repeat("\0", 1500));
        $first = $this->withToken($token)->post($this->url('audio/media'), ['type' => 'recitation', 'surah' => 2, 'file' => $file()])->assertOk()->json('data');
        $second = $this->withToken($token)->post($this->url('audio/media'), ['type' => 'recitation', 'surah' => 1, 'file' => $file(), 'batch_token' => $first['batch_token']])->assertOk()->json('data');
        $this->assertSame($first['url'], $second['url']);
        $this->assertStringEndsWith('/002.mp3', $first['file_url']);
        $this->assertStringEndsWith('/001.mp3', $second['file_url']);
        $this->withToken($token)->post($this->url('audio/media'), ['type' => 'recitation', 'surah' => 1, 'file' => $file(), 'batch_token' => $first['batch_token']])->assertConflict();
        $this->withToken($token)->post($this->url('audio/media'), ['type' => 'adhan', 'file' => $file(), 'batch_token' => $first['batch_token']])->assertUnprocessable();
        $this->withToken($token)->post($this->url('audio/media'), ['type' => 'recitation', 'surah' => 3, 'file' => $file(), 'batch_token' => 'invalid'])->assertUnprocessable();
        $this->assertCount(2, Storage::disk('public')->allFiles('haq-audio'));
    }

    public function test_audio_download_is_bounded_and_rejects_untrusted_hosts_redirects_and_non_audio(): void
    {
        Storage::fake('public');
        Http::preventStrayRequests();
        [$token] = $this->admin();
        foreach (['https://127.0.0.1/private.mp3', 'https://user@cdn.aladhan.com/a.mp3', 'https://cdn.aladhan.com.evil.test/a.mp3'] as $url) {
            $this->withToken($token)->postJson($this->url('audio/media'), ['type' => 'adhan', 'url' => $url])->assertUnprocessable();
        }
        Http::assertNothingSent();
        Http::fake(['https://cdn.aladhan.com/audio/adhans/*' => Http::sequence()
            ->push('ID3'.str_repeat("\0", 1500), 200)
            ->push('', 302, ['Location' => 'https://127.0.0.1/secret'])
            ->push('<html>'.str_repeat('x', 1500), 200)
            ->push('', 200, ['Content-Length' => 21 * 1024 * 1024])]);
        $this->withToken($token)->postJson($this->url('audio/media'), ['type' => 'adhan', 'url' => 'https://cdn.aladhan.com/audio/adhans/test.mp3'])->assertOk();
        for ($i = 0; $i < 3; $i++) {
            $this->withToken($token)->postJson($this->url('audio/media'), ['type' => 'adhan', 'url' => 'https://cdn.aladhan.com/audio/adhans/test.mp3'])->assertUnprocessable();
        }
        $this->assertCount(1, Storage::disk('public')->allFiles('haq-audio'));
    }

    private string $directory;

    public function test_full_adhkar_recordings_require_explicit_groups_and_allow_larger_files(): void
    {
        Storage::fake('public');
        [$token] = $this->admin();
        $file = UploadedFile::fake()->createWithContent('full-reading.mp3', 'ID3'.str_repeat("\0", 1500));
        $handle = fopen($file->getRealPath(), 'r+b');
        ftruncate($handle, 21 * 1024 * 1024);
        fclose($handle);
        $uploaded = $this->withToken($token)->post($this->url('audio/media'), ['type' => 'adhkar_recitation', 'file' => $file])->assertOk()->json('data');
        $record = ['id' => 'reading', 'title' => 'قراءة الصباح والمساء', 'type' => 'adhkar_recitation', 'url' => $uploaded['url'], 'source' => 'القارئ', 'source_url' => 'https://example.test/source', 'published' => true];
        $this->withToken($token)->postJson($this->url('audio'), ['record' => $record, 'revision' => 0])->assertUnprocessable();
        $this->withToken($token)->postJson($this->url('audio'), ['record' => [...$record, 'groups' => ['other']], 'revision' => 0])->assertUnprocessable();
        $this->withToken($token)->postJson($this->url('audio'), ['record' => [...$record, 'groups' => ['morning', 'evening']], 'revision' => 0])->assertOk();
        $this->assertFalse(collect(app(AppManagedContent::class)->published()['audio'])->contains('id', 'reading'));
        $this->assertSame(['morning', 'evening'], collect(app(AppManagedContent::class)->state()['draft']['audio'])->firstWhere('id', 'reading')['groups']);
    }

    public function test_known_reading_sources_are_pinned_and_reject_mismatched_downloads(): void
    {
        Storage::fake('public');
        Http::preventStrayRequests();
        [$token] = $this->admin();
        $body = 'ID3'.str_repeat("\0", 1500);
        $url = 'https://d1.islamhouse.com/data/ar/ih_sounds/test-reading.mp3';
        $path = $this->directory.'/readers.json';
        file_put_contents($path, json_encode([['id' => 'test-reader', 'url' => $url, 'bytes' => strlen($body), 'sha256' => hash('sha256', $body)]]));
        config(['haq_app.dhikr_readers' => $path]);
        Http::fake([$url => Http::sequence()->push($body, 200)->push('ID3'.str_repeat('x', 1500), 200)]);
        $payload = ['type' => 'adhkar_recitation', 'url' => $url, 'source_id' => 'test-reader'];
        $this->withToken($token)->postJson($this->url('audio/media'), $payload)->assertOk();
        $this->withToken($token)->postJson($this->url('audio/media'), $payload)->assertUnprocessable();
        $this->withToken($token)->postJson($this->url('audio/media'), [...$payload, 'url' => 'https://d1.islamhouse.com/data/ar/ih_sounds/other.mp3'])->assertUnprocessable();
        $this->assertCount(1, Storage::disk('public')->allFiles('haq-audio'));
        Http::assertSentCount(2);
    }

    public function withToken($token, $type = 'Bearer')
    {
        $this->app['auth']->forgetGuards();

        return parent::withToken($token, $type);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'workspace_test', 'database.connections.workspace_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ], 'haq_app.enabled' => true, 'cache.default' => 'array', 'laratrust.cache.enabled' => false]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2025_07_04_180710_laratrust_setup_tables.php');
        (new \LaratrustSetupTables)->up();
        foreach (['0001_01_01_000000_create_users_table', '2026_10_13_120000_create_religious_accounts_and_quran_sync',
            '2026_01_12_150326_create_personal_access_tokens_table', '2026_10_26_120000_extend_religious_account_identity',
            '2026_10_30_120000_create_haq_app_localization', '2026_10_26_150000_create_religious_community',
            '2026_10_26_180000_create_religious_assistant_and_billing', '2026_10_27_120000_create_religious_push_outbox'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $this->directory = storage_path('framework/testing/workspace-'.Str::uuid());
        mkdir($this->directory, 0775, true);
        config(['haq_app.managed_path' => $this->directory.'/content.json']);
        $this->app->instance(ReligiousCatalog::class, new ReligiousCatalog($this->directory.'/catalog.json'));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->directory);
        DB::purge('workspace_test');
        parent::tearDown();
    }

    private function admin(?array $permissions = null, array $locales = []): array
    {
        $staff = User::create(['name' => 'Staff', 'email' => Str::uuid().'@example.test', 'password' => 'testing-password']);
        $role = DB::table('roles')->insertGetId(['name' => 'superadmin-'.Str::uuid(), 'created_at' => now(), 'updated_at' => now()]);
        // Reuse a single exact superadmin role for each fresh isolated fixture.
        DB::table('roles')->where('id', $role)->update(['name' => 'superadmin']);
        DB::table('role_user')->insert(['user_id' => $staff->id, 'role_id' => $role, 'user_type' => $staff->getMorphClass()]);
        $account = AppAccount::create(['user_id' => $staff->id, 'association_enabled' => true]);
        DB::table('app_admin_memberships')->insert(['app_account_id' => $account->id, 'permissions' => json_encode($permissions ?? array_keys(AppAdminAccess::PERMISSIONS)),
            'locales' => json_encode($locales), 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $personal = $account->createToken('app-session', app(AppSessionProof::class)->abilities($account, 'association'), now()->addDay())->plainTextToken;
        $token = $this->withToken($personal)->postJson('/api/v2/admin/session')->assertOk()->json('data.token');

        return [$token, $account];
    }

    private function url(string $section): string
    {
        return '/api/v2/admin/workspace/'.$section;
    }

    private function message(array $extra = []): array
    {
        $id = collect(app(ReligiousCatalog::class)->dataset()['items'])->firstWhere('type', 'dua')['id'];

        return ['title' => 'منشور اختبار', 'kind' => 'dua', 'content_id' => $id, 'status' => 'draft', ...$extra];
    }

    public function test_translation_grants_do_not_authorize_publication_or_global_library_management(): void
    {
        [$token] = $this->admin(['admin.enter', 'translations.edit', 'content.edit']);
        $this->withToken($token)->getJson($this->url('messages'))->assertForbidden();
        $this->withToken($token)->getJson($this->url('library'))->assertForbidden();
        $this->withToken($token)->postJson($this->url('messages'), $this->message())->assertForbidden();
        $this->assertSame(0, DB::table('religious_publications')->count());
    }

    public function test_editor_can_save_drafts_but_cannot_publish_or_edit_a_visible_publication(): void
    {
        [$token] = $this->admin(['admin.enter', 'publications.manage']);
        $this->withToken($token)->postJson($this->url('messages'), $this->message())->assertOk();
        $this->withToken($token)->postJson($this->url('messages'), $this->message(['status' => 'published', 'publish_at' => now()->toIso8601String()]))->assertForbidden();
        $row = $this->withToken($token)->getJson($this->url('messages'))->assertOk()->json('data.items.0');
        DB::table('religious_publications')->where('id', $row['id'])->update(['status' => 'published', 'publish_at' => now()]);
        $this->withToken($token)->postJson($this->url('messages'), $row)->assertForbidden();
    }

    public function test_publications_preserve_scriptural_source_validation_and_reject_stale_writes(): void
    {
        [$token] = $this->admin();
        $this->withToken($token)->postJson($this->url('messages'), $this->message(['body' => 'different text']))->assertUnprocessable();
        $this->withToken($token)->postJson($this->url('messages'), $this->message(['status' => 'scheduled']))->assertUnprocessable();
        $this->withToken($token)->postJson($this->url('messages'), $this->message())->assertOk();
        $row = $this->withToken($token)->getJson($this->url('messages'))->json('data.items.0');
        $this->withToken($token)->postJson($this->url('messages'), [...$row, 'title' => 'Updated'])->assertOk();
        $this->withToken($token)->postJson($this->url('messages'), [...$row, 'title' => 'Stale'])->assertConflict();
        $this->withToken($token)->postJson($this->url('stories'), $row)->assertNotFound();
        $this->assertSame('Updated', DB::table('religious_publications')->value('title'));
    }

    public function test_managed_drafts_are_not_public_until_reviewed_and_published(): void
    {
        [$token] = $this->admin();
        $data = $this->withToken($token)->getJson($this->url('sakinah'))->assertOk()->json('data');
        $row = $data['items'][0];
        $old = $row['tagline'];
        $row['tagline'] = 'وصف مراجَع للاختبار';
        $this->withToken($token)->postJson($this->url('sakinah'), ['record' => $row, 'revision' => 0])->assertOk();
        $this->assertSame($old, app(AppManagedContent::class)->published()['sakinah'][0]['tagline']);
        $this->withToken($token)->postJson($this->url('sakinah/catalog/action'), ['action' => 'publish', 'revision' => 1])->assertUnprocessable();
        $this->withToken($token)->postJson($this->url('sakinah/catalog/action'), ['action' => 'review', 'revision' => 1])->assertOk();
        $this->withToken($token)->postJson($this->url('sakinah/catalog/action'), ['action' => 'publish', 'revision' => 1])->assertOk();
        $public = $this->getJson('/api/v2/app-content/managed')->assertOk()->json('data');
        $this->assertSame(hash('sha256', $public['payload']), $public['sha256']);
        $snapshot = json_decode($public['payload'], true);
        $this->assertSame($row['tagline'], collect($snapshot['sakinah'])->firstWhere('id', $row['id'])['tagline']);
        $this->assertArrayNotHasKey('settings', $snapshot);
        $this->withToken($token)->postJson($this->url('sakinah'), ['record' => $row, 'revision' => 0])->assertConflict();
    }

    public function test_invalid_daily_references_and_peace_dhikr_are_rejected_without_changing_drafts(): void
    {
        [$token] = $this->admin();
        $this->withToken($token)->postJson($this->url('daily'), ['record' => ['id' => 'day-one', 'date' => '2026-11-03', 'verse_key' => '999:999', 'published' => true], 'revision' => 0])->assertUnprocessable();
        $row = collect(app(AppManagedContent::class)->state()['draft']['sakinah'])->firstWhere('id', 'peace');
        $row['dhikr'] = ['invalid addition'];
        $this->withToken($token)->postJson($this->url('sakinah'), ['record' => $row, 'revision' => 0])->assertUnprocessable();
        $this->assertSame(0, app(AppManagedContent::class)->state()['revision']);
    }

    public function test_library_uses_original_catalog_validation_and_optimistic_versions(): void
    {
        [$token] = $this->admin();
        $data = $this->withToken($token)->getJson($this->url('library'))->assertOk()->json('data');
        $row = collect($data['items'])->firstWhere('type', 'dua');
        $row['summary'] = 'ملخص اختبار';
        $this->withToken($token)->postJson($this->url('library'), ['record' => $row, 'version' => $data['version']])->assertOk();
        $this->withToken($token)->postJson($this->url('library'), ['record' => $row, 'version' => $data['version']])->assertConflict();
        $row = collect(app(ReligiousCatalog::class)->all()['items'])->firstWhere('type', 'quran');
        $row['text'] = 'altered scripture';
        $this->withToken($token)->postJson($this->url('library'), ['record' => $row, 'version' => app(ReligiousCatalog::class)->all()['version']])->assertUnprocessable();
    }

    public function test_service_reports_never_return_private_questions_or_store_proofs(): void
    {
        [$token] = $this->admin();
        $this->withToken($token)->getJson($this->url('assistant'))->assertOk()->assertJsonMissingPath('data.items.0.question');
        $this->withToken($token)->getJson($this->url('subscriptions'))->assertOk()->assertJsonMissingPath('data.items.0.proof');
        $this->withToken($token)->getJson($this->url('health'))->assertOk()->assertJsonCount(6, 'data.items');
        $this->withToken($token)->postJson($this->url('service-settings'), ['record' => ['daily_limit' => 5, 'monthly_limit' => 50], 'revision' => 0])->assertOk();
        $snapshot = $this->getJson('/api/v2/app-content/managed')->json('data.payload');
        $this->assertStringNotContainsString('daily_limit', $snapshot);
    }

    public function test_locale_scoped_staff_cannot_edit_global_managed_content(): void
    {
        [$token] = $this->admin(null, ['en']);
        $this->withToken($token)->getJson($this->url('sakinah'))->assertForbidden();
        $this->withToken($token)->getJson('/api/v2/admin/workspace')->assertOk()->assertJsonPath('data.sections', []);
    }

    public function test_tafsir_visibility_cannot_remove_the_default_or_invalidate_unrelated_adhkar(): void
    {
        [$token] = $this->admin();
        $version = app(AppManagedContent::class)->state()['draft']['adhkar']['version'];
        $data = $this->withToken($token)->getJson($this->url('tafsirs'))->assertOk()->json('data');
        $default = collect($data['items'])->firstWhere('id', '91');
        $this->withToken($token)->postJson($this->url('tafsirs'), ['record' => [...$default, 'published' => false], 'revision' => 0])->assertUnprocessable();
        $other = collect($data['items'])->firstWhere('id', '15');
        $this->withToken($token)->postJson($this->url('tafsirs'), ['record' => [...$other, 'published' => false], 'revision' => 0])->assertOk();
        $this->assertSame($version, app(AppManagedContent::class)->state()['draft']['adhkar']['version']);
        $this->assertTrue(collect(app(AppManagedContent::class)->published()['tafsirs'])->firstWhere('id', '15')['published']);
    }

    public function test_scheduling_normalizes_phone_offsets_to_database_dates_and_returns_the_offset(): void
    {
        [$token] = $this->admin();
        $this->withToken($token)->postJson($this->url('messages'), $this->message([
            'status' => 'scheduled', 'publish_at' => '2030-01-10T10:00:00Z', 'expires_at' => '2030-01-11T10:00:00Z',
        ]))->assertOk();
        $this->assertSame('2030-01-10 12:00:00', DB::table('religious_publications')->value('publish_at'));
        $this->withToken($token)->getJson($this->url('schedule'))->assertOk()
            ->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.publish_at', '2030-01-10T12:00:00+02:00');
    }

    public function test_catalog_reviewer_can_read_and_approve_without_editing_or_publishing(): void
    {
        [$token] = $this->admin(['admin.enter', 'catalogs.review']);
        $data = $this->withToken($token)->getJson($this->url('sakinah'))->assertOk()->assertJsonPath('data.can_edit', false)->json('data');
        $this->withToken($token)->postJson($this->url('sakinah'), ['record' => $data['items'][0], 'revision' => 0])->assertForbidden();
        $this->withToken($token)->postJson($this->url('sakinah/catalog/action'), ['action' => 'review', 'revision' => 0])->assertOk();
        $this->withToken($token)->postJson($this->url('sakinah/catalog/action'), ['action' => 'publish', 'revision' => 0])->assertForbidden();
    }

    public function test_language_review_and_publish_permissions_do_not_expand_into_global_catalogs(): void
    {
        [$token] = $this->admin(['admin.enter', 'review', 'publish']);
        $this->withToken($token)->getJson($this->url('sakinah'))->assertForbidden();
        $this->withToken($token)->postJson($this->url('sakinah/catalog/action'), ['action' => 'publish', 'revision' => 0])->assertForbidden();
    }

    public function test_publishing_one_reviewed_record_preserves_every_other_public_record_and_draft(): void
    {
        [$token] = $this->admin();
        $managed = app(AppManagedContent::class);
        $before = $managed->published();
        $first = $before['sakinah'][0]; $second = $before['sakinah'][1];
        $first['tagline'] = 'تعديل مستقل للنشر'; $second['tagline'] = 'مسودة أخرى لا تنشر';
        $this->withToken($token)->postJson($this->url('sakinah'), ['record' => $first, 'revision' => 0])->assertOk();
        $this->postJson($this->url('sakinah'), ['record' => $second, 'revision' => 1])->assertOk();
        $this->postJson($this->url('sakinah/'.$first['id'].'/action'), ['action' => 'publish_record', 'revision' => 2])->assertUnprocessable();
        $this->postJson($this->url('sakinah/'.$first['id'].'/action'), ['action' => 'review_record', 'revision' => 2])->assertOk();
        $this->getJson($this->url('sakinah'))->assertOk()->assertJsonPath('data.items.0._reviewed', true);
        $this->postJson($this->url('sakinah/'.$first['id'].'/action'), ['action' => 'publish_record', 'revision' => 2])->assertOk();
        $state = $managed->state();
        $this->assertSame($first, $state['published']['sakinah'][0]);
        $this->assertSame($before['sakinah'][1], $state['published']['sakinah'][1]);
        $this->assertSame($second, $state['draft']['sakinah'][1]);
        $this->assertSame($before['adhkar'], $state['published']['adhkar']);
        $this->assertNull($state['reviewed_revision']);
    }

    public function test_record_review_expires_when_the_record_changes_and_review_permission_cannot_publish(): void
    {
        [$token] = $this->admin();
        $record = app(AppManagedContent::class)->state()['draft']['tafsirs'][1];
        $path = 'tafsirs/'.$record['id'].'/action';
        $this->withToken($token)->postJson($this->url($path), ['action' => 'review_record', 'revision' => 0])->assertOk();
        $record['title'] = 'اسم معدل بعد المراجعة';
        $this->postJson($this->url('tafsirs'), ['record' => $record, 'revision' => 0])->assertOk();
        $this->postJson($this->url($path), ['action' => 'publish_record', 'revision' => 1])->assertUnprocessable();
        DB::table('app_admin_memberships')->update(['permissions' => json_encode(['admin.enter', 'catalogs.review'])]);
        $this->withToken($token)->postJson($this->url($path), ['action' => 'review_record', 'revision' => 1])->assertOk();
        $this->postJson($this->url($path), ['action' => 'publish_record', 'revision' => 1])->assertForbidden();
    }

    public function test_a_single_group_cannot_publish_its_unpublished_dhikr_dependencies(): void
    {
        [$token] = $this->admin();
        $managed = app(AppManagedContent::class);
        $item = ['id' => 'draft-only-dhikr', 'title' => 'ذكر موثق', 'text' => 'سبحان الله', 'target' => 1, 'source' => 'مصدر للتجربة', 'source_url' => 'https://example.test/source'];
        $this->withToken($token)->postJson($this->url('adhkar-items'), ['record' => $item, 'revision' => 0])->assertOk();
        $group = ['id' => 'new-group', 'title' => 'مجموعة جديدة', 'icon' => 'sun', 'item_ids' => [$item['id']]];
        $this->postJson($this->url('adhkar-groups'), ['record' => $group, 'revision' => 1])->assertOk();
        $this->postJson($this->url('adhkar-groups/new-group/action'), ['action' => 'review_record', 'revision' => 2])->assertOk();
        $this->postJson($this->url('adhkar-groups/new-group/action'), ['action' => 'publish_record', 'revision' => 2])->assertUnprocessable();
        $this->assertFalse(collect($managed->published()['adhkar']['items'])->contains('id', $item['id']));
        $this->assertFalse(collect($managed->published()['adhkar']['groups'])->contains('id', $group['id']));
    }
}
