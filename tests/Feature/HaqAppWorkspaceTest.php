<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppManagedContent;
use App\Services\Religious\AppSessionProof;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class HaqAppWorkspaceTest extends TestCase
{
    private string $directory;

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
}
