<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ReligiousCommunityAdminController;
use App\Models\ReligiousAccount;
use App\Models\User;
use App\Services\Religious\ReligiousCommunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ReligiousCommunityTest extends TestCase
{
    private ReligiousAccount $owner;

    private ReligiousAccount $other;

    private int $circle;

    private string $content;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'community_test', 'database.connections.community_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true], 'religious_community.enabled' => true]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (['2026_10_13_120000_create_religious_accounts_and_quran_sync', '2026_10_26_120000_extend_religious_account_identity', '2026_10_26_150000_create_religious_community'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $this->owner = ReligiousAccount::create(['name' => 'One', 'username' => 'readerone', 'email' => 'one@example.test', 'password' => 'test-password']);
        $this->other = ReligiousAccount::create(['name' => 'Two', 'username' => 'readertwo', 'email' => 'two@example.test', 'password' => 'test-password']);
        $items = app(\App\Services\Religious\ReligiousCatalog::class)->dataset()['items'];
        $this->content = collect($items)->first(fn ($i) => $i['type'] === 'dua' && $i['text'] !== '')['id'];
        $this->circle = DB::table('religious_circles')->insertGetId(['title' => 'حلقة اختبار', 'content_id' => $this->content, 'goal' => 5, 'published' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function tearDown(): void
    {
        DB::purge('community_test');
        parent::tearDown();
    }

    private function sign(?ReligiousAccount $owner = null): void
    {
        Sanctum::actingAs($owner ?? $this->owner, ['quran:sync']);
    }

    private function url(string $path): string
    {
        return '/api/v1/religious/community/'.$path;
    }

    private function createPost(string $body = 'دعاء شخصي', ?int $parent = null): int
    {
        $this->sign();

        return $this->postJson($this->url('entries'), ['operation_id' => (string) Str::uuid(), 'body' => $body, 'parent_id' => $parent])->assertCreated()->assertJsonPath('data.status', 'pending')->json('data.id');
    }

    private function approved(string $body = 'مشاركة'): int
    {
        $id = $this->createPost($body);
        DB::table('religious_community_entries')->where('id', $id)->update(['status' => 'approved']);

        return $id;
    }

    public function test_disabled_missing_schema_and_staff_are_not_a_working_community(): void
    {
        $this->getJson($this->url('capabilities'))->assertOk()->assertJsonPath('data.enabled', true);
        $this->getJson($this->url('circles'))->assertUnauthorized();
        $this->actingAs(new User(['name' => 'Staff']), 'sanctum');
        $this->getJson($this->url('circles'))->assertForbidden();
        $this->sign();
        config(['religious_community.enabled' => false]);
        $this->getJson($this->url('capabilities'))->assertJsonPath('data.enabled', false);
        $this->getJson($this->url('circles'))->assertStatus(503);
        $this->getJson($this->url('publications?channel=message'))->assertStatus(503);
        config(['religious_community.enabled' => true]);
        \Illuminate\Support\Facades\Schema::drop('religious_publications');
        $this->getJson($this->url('capabilities'))->assertJsonPath('data.enabled', false);
    }

    public function test_membership_count_receipts_goal_and_account_aggregate_are_exact(): void
    {
        $this->sign();
        $id = (string) Str::uuid();
        $path = $this->url('circles/'.$this->circle);
        $this->postJson($path.'/counts', ['operation_id' => $id, 'amount' => 3])->assertForbidden();
        $this->putJson($path.'/membership', ['joined' => true])->assertOk()->assertJsonPath('data.participants', 1);
        $this->putJson($path.'/membership', ['joined' => true])->assertJsonPath('data.participants', 1);
        for ($i = 0; $i < 2; $i++) {
            $this->postJson($path.'/counts', ['operation_id' => $id, 'amount' => 3])->assertOk()->assertJsonPath('data.total', 3)->assertJsonPath('data.mine', 3);
        }
        $this->postJson($path.'/counts', ['operation_id' => $id, 'amount' => 1])->assertStatus(409);
        $this->sign($this->other);
        $this->putJson($path.'/membership', ['joined' => true])->assertOk();
        $this->postJson($path.'/counts', ['operation_id' => (string) Str::uuid(), 'amount' => 3])->assertStatus(409);
        $this->postJson($path.'/counts', ['operation_id' => (string) Str::uuid(), 'amount' => 2])->assertOk()->assertJsonPath('data.total', 5)->assertJsonPath('data.mine', 2)->assertJsonPath('data.participants', 2);
        $this->postJson($path.'/counts', ['operation_id' => (string) Str::uuid(), 'amount' => 1])->assertStatus(409);
        $this->assertSame(2, DB::table('religious_circle_counts')->count());
        $this->sign();
        $this->putJson($path.'/membership', ['joined' => false])->assertJsonPath('data.participants', 1)->assertJsonPath('data.total', 5);
        $this->postJson($path.'/counts', ['operation_id' => $id, 'amount' => 3])->assertOk()->assertJsonPath('data.total', 5);
    }

    public function test_circle_cursor_pagination_has_no_duplicates_or_unpublished_items(): void
    {
        $this->sign();
        foreach (range(1, 4) as $i) {
            DB::table('religious_circles')->insert(['title' => 'حلقة'.$i, 'content_id' => $this->content, 'goal' => 20, 'published' => $i !== 4]);
        }
        $first = $this->getJson($this->url('circles?limit=2'))->assertOk()->assertJsonCount(2, 'data.items');
        $cursor = $first->json('data.next_cursor');
        $this->assertNotNull($cursor);
        $next = $this->getJson($this->url('circles?limit=2&cursor='.$cursor))->assertOk()->assertJsonCount(2, 'data.items');
        $ids = [...array_column($first->json('data.items'), 'id'), ...array_column($next->json('data.items'), 'id')];
        $this->assertCount(4, array_unique($ids));
        $this->getJson($this->url('circles?limit=500'))->assertUnprocessable();
    }

    public function test_posts_require_review_and_idempotent_retries_cannot_republish_deleted_posts(): void
    {
        $this->sign();
        $operation = (string) Str::uuid();
        $data = ['operation_id' => $operation, 'body' => 'دعاء شخصي'];
        $first = $this->postJson($this->url('entries'), $data)->assertCreated();
        $id = $first->json('data.id');
        $this->postJson($this->url('entries'), $data)->assertCreated()->assertJsonPath('data.id', $id);
        $this->postJson($this->url('entries'), [...$data, 'body' => 'تغيير'])->assertStatus(409);
        $this->getJson($this->url('entries'))->assertJsonCount(0, 'data.items');
        $this->getJson($this->url('entries?mine=1'))->assertJsonCount(1, 'data.items');
        $this->deleteJson($this->url('entries/'.$id))->assertOk();
        $this->postJson($this->url('entries'), $data)->assertCreated()->assertJsonPath('data.status', 'deleted');
        $this->assertSame(1, DB::table('religious_community_entries')->count());
        $this->getJson($this->url('entries?mine=1'))->assertJsonCount(0, 'data.items');
    }

    public function test_comments_likes_blocks_and_deletion_respect_visibility_and_ownership(): void
    {
        $parent = $this->approved();
        $comment = $this->createPost('تعليق', $parent);
        $this->sign($this->other);
        $this->getJson($this->url('entries?parent_id='.$parent))->assertJsonCount(0, 'data.items');
        DB::table('religious_community_entries')->where('id', $comment)->update(['status' => 'approved']);
        $this->getJson($this->url('entries?parent_id='.$parent))->assertJsonCount(1, 'data.items');
        for ($i = 0; $i < 2; $i++) {
            $this->putJson($this->url('entries/'.$parent.'/like'), ['liked' => true])->assertOk()->assertJsonPath('data.likes', 1);
        }
        $this->deleteJson($this->url('entries/'.$parent))->assertNotFound();
        $this->putJson($this->url('blocks/'.$this->owner->id), ['blocked' => true])->assertOk();
        $this->getJson($this->url('entries'))->assertJsonCount(0, 'data.items');
        $this->getJson($this->url('entries/'.$parent))->assertNotFound();
        $this->postJson($this->url('entries'), ['body' => 'تعليق', 'parent_id' => $parent, 'operation_id' => (string) Str::uuid()])->assertNotFound();
        $this->sign();
        $this->getJson($this->url('entries?parent_id='.$parent))->assertJsonCount(1, 'data.items');
        $this->sign($this->other);
        $this->putJson($this->url('blocks/'.$this->owner->id), ['blocked' => false])->assertOk();
        $this->getJson($this->url('entries'))->assertJsonCount(1, 'data.items');
        $this->sign();
        $this->deleteJson($this->url('entries/'.$parent))->assertOk();
        $this->sign($this->other);
        $this->getJson($this->url('entries?parent_id='.$parent))->assertNotFound();
        $this->assertSame('deleted', DB::table('religious_community_entries')->find($comment)->status);
    }

    public function test_report_deduplication_suspension_and_text_limits(): void
    {
        $id = $this->approved();
        $this->sign($this->other);
        $data = ['type' => 'entry', 'target_id' => $id, 'reason' => 'يحتاج مراجعة'];
        for ($i = 0; $i < 2; $i++) {
            $this->postJson($this->url('reports'), $data)->assertOk();
        }
        $this->assertSame(1, DB::table('religious_community_reports')->count());
        DB::table('religious_community_accounts')->insert(['account_id' => $this->owner->id, 'suspended' => true]);
        $this->getJson($this->url('entries'))->assertJsonCount(0, 'data.items');
        $this->sign();
        $this->postJson($this->url('entries'), ['operation_id' => (string) Str::uuid(), 'body' => 'دعاء'])->assertForbidden();
        $this->sign($this->other);
        $this->postJson($this->url('entries'), ['operation_id' => (string) Str::uuid(), 'body' => str_repeat('ب', 181)])->assertUnprocessable();
        $this->postJson($this->url('entries'), ['operation_id' => (string) Str::uuid(), 'body' => '   '])->assertUnprocessable();
    }

    public function test_scheduled_publications_filters_expiry_and_repeated_background_job(): void
    {
        foreach ([
            ['channel' => 'message', 'kind' => 'dua', 'status' => 'scheduled', 'publish_at' => now()->subMinute(), 'expires_at' => null],
            ['channel' => 'story', 'kind' => 'dua', 'status' => 'published', 'publish_at' => now()->subMinute(), 'expires_at' => now()->addHour()],
            ['channel' => 'message', 'kind' => 'video', 'status' => 'draft', 'publish_at' => now()->subMinute(), 'expires_at' => null],
            ['channel' => 'message', 'kind' => 'dua', 'status' => 'scheduled', 'publish_at' => now()->addHour(), 'expires_at' => null],
            ['channel' => 'message', 'kind' => 'dua', 'status' => 'published', 'publish_at' => now()->subHour(), 'expires_at' => now()->subMinute()],
        ] as $row) {
            DB::table('religious_publications')->insert([...$row, 'title' => 'منشور اختبار', 'content_id' => $this->content, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->getJson($this->url('publications?channel=message'))->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.content.id', $this->content);
        $this->getJson($this->url('publications?channel=story'))->assertJsonCount(1, 'data.items');
        $this->getJson($this->url('publications?channel=message&kind=video'))->assertJsonCount(0, 'data.items');
        $this->assertSame(1, app(ReligiousCommunity::class)->publishDue());
        $this->assertSame(0, app(ReligiousCommunity::class)->publishDue());
        $this->assertSame(1, DB::table('religious_publications')->where('status', 'draft')->count());
    }

    public function test_administration_routes_require_auth_and_superadmin_and_render_escaped_user_text(): void
    {
        $this->withoutMiddleware([
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        ]);
        $controller = app(ReligiousCommunityAdminController::class);
        $this->assertContains('auth', array_column($controller->getMiddleware(), 'middleware'));
        $this->assertContains('role:superadmin', array_column($controller->getMiddleware(), 'middleware'));
        $this->get(route('admin.religious.community.index'))->assertRedirect();
        $staff = new class extends User
        {
            public function hasRole(string|array|\BackedEnum $name, mixed $team = null, bool $requireAll = false): bool
            {
                return false;
            }
        };
        $staff->id = 77;
        config(['laratrust.middleware.handling' => 'abort', 'laratrust.middleware.handlers.abort' => ['code' => 403]]);
        $this->actingAs($staff, 'web')->get(route('admin.religious.community.index'))->assertForbidden();
        $this->actingAs($this->owner, 'web')->get(route('admin.religious.community.index'))->assertForbidden();
        $view = file_get_contents(resource_path('views/admin/religious/community.blade.php'));
        $this->assertStringContainsString('{{ $entry->body }}', $view);
        $this->assertStringNotContainsString('{!!', $view);
    }

    public function test_admin_cannot_change_a_counted_circles_content_or_lower_goal(): void
    {
        $this->sign();
        $this->putJson($this->url('circles/'.$this->circle.'/membership'), ['joined' => true]);
        $this->postJson($this->url('circles/'.$this->circle.'/counts'), ['operation_id' => (string) Str::uuid(), 'amount' => 3])->assertOk();
        $controller = app(ReligiousCommunityAdminController::class);
        try {
            $controller->circle(Request::create('/', 'POST', ['id' => $this->circle, 'title' => 'حلقة', 'content_id' => $this->content, 'goal' => 2, 'published' => true]));
            $this->fail('Accepted goal below contributions');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(5, (int) DB::table('religious_circles')->find($this->circle)->goal);
        }
    }

    public function test_account_deletion_cascades_only_owned_community_rows(): void
    {
        $this->approved();
        $this->owner->delete();
        $this->assertSame(0, DB::table('religious_community_entries')->count());
        $this->assertNotNull($this->other->fresh());
        $this->assertSame(1, DB::table('religious_circles')->count());
    }

    public function test_moderation_approves_comments_only_with_visible_parent_and_report_can_suspend_then_restore(): void
    {
        $parent = $this->createPost();
        $controller = app(ReligiousCommunityAdminController::class);
        $controller->review(Request::create('/', 'POST', ['status' => 'approved']), $parent);
        $comment = $this->createPost('تعليق', $parent);
        $controller->review(Request::create('/', 'POST', ['status' => 'approved']), $comment);
        $this->sign($this->other);
        $this->getJson($this->url('entries?parent_id='.$parent))->assertJsonCount(1, 'data.items');
        $this->postJson($this->url('reports'), ['type' => 'entry', 'target_id' => $parent, 'reason' => 'مراجعة'])->assertOk();
        $report = DB::table('religious_community_reports')->first()->id;
        $controller->report(Request::create('/', 'POST', ['action' => 'suspend']), $report);
        $this->getJson($this->url('entries'))->assertJsonCount(0, 'data.items');
        $controller->report(Request::create('/', 'POST', ['action' => 'restore']), $report);
        $this->getJson($this->url('entries'))->assertJsonCount(1, 'data.items');
        $controller->report(Request::create('/', 'POST', ['action' => 'hide']), $report);
        $this->getJson($this->url('entries?parent_id='.$parent))->assertNotFound();
    }

    public function test_publication_requires_canonical_content_https_and_a_real_video_source_before_publication(): void
    {
        Storage::fake('public');
        $controller = app(ReligiousCommunityAdminController::class);
        $base = ['channel' => 'message', 'kind' => 'dua', 'title' => 'رسالة', 'body' => null, 'content_id' => $this->content, 'youtube_id' => null, 'link' => null, 'status' => 'scheduled', 'publish_at' => now()->addHour()->toDateTimeString(), 'expires_at' => null];
        $controller->publication(Request::create('/', 'POST', $base));
        $this->assertSame(1, DB::table('religious_publications')->count());
        foreach ([
            [...$base, 'content_id' => null], [...$base, 'body' => 'نص بديل غير موثق'],
            [...$base, 'kind' => 'video', 'content_id' => null, 'youtube_id' => null],
            [...$base, 'kind' => 'link', 'content_id' => null, 'link' => 'http://unsafe.test'],
            [...$base, 'publish_at' => null],
        ] as $invalid) {
            try {
                $controller->publication(Request::create('/', 'POST', $invalid));
                $this->fail('Invalid publication accepted');
            } catch (\Illuminate\Validation\ValidationException) {
                $this->assertSame(1, DB::table('religious_publications')->count());
            }
        }
        $youtube = [...$base, 'kind' => 'video', 'content_id' => null, 'youtube_id' => 'abcdefghijk'];
        $controller->publication(Request::create('/', 'POST', $youtube));
        $this->assertSame(2, DB::table('religious_publications')->count());
        $saved = DB::table('religious_publications')->where('kind', 'dua')->first()->id;
        $this->postJson($this->url('publications/query?channel=message'), ['ids' => [$saved]])->assertJsonCount(0, 'data.items');
        DB::table('religious_publications')->where('id', $saved)->update(['publish_at' => now()->subMinute()]);
        $this->postJson($this->url('publications/query?channel=message'), ['ids' => [$saved]])->assertJsonCount(1, 'data.items');
        $this->postJson($this->url('publications/query?channel=message'), ['ids' => []])->assertJsonCount(0, 'data.items');
    }
}
