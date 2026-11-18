<?php

namespace Tests\Feature;

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\AdminTelegramController;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationWebConsistencyTest extends AssociationTestCase
{
    public function test_failed_telegram_transport_preserves_web_delivery_and_mobile_cannot_count_it_again(): void
    {
        $person = $this->beneficiary($this->category());
        DB::table('telegram_users')->where('id', $person)->update(['chat_id' => 'test-only']);
        $event = DB::table('events')->insertGetId(['name' => 'Event', 'event_date' => now()->toDateString(), 'event_time' => '00:00:00']);
        $log = DB::table('message_logs')->insertGetId(['telegram_user_id' => $person, 'event_id' => $event, 'message_type' => 'invitation', 'delivery_status' => 'manual', 'message' => 'Invitation', 'attended_at' => now()]);
        [$admin, $staff] = $this->actor();
        $this->actingAs($staff, 'web');
        Http::preventStrayRequests(); Http::fake(['*' => Http::response(['ok' => false], 503)]);
        $response = app(DeliveryController::class)->markDelivery(Request::create('/test', 'POST', ['send_method' => 'telegram']), $log);
        $this->assertTrue($response->getData(true)['success']);
        $this->assertFalse($response->getData(true)['notification_sent']);
        $this->assertSame(1, DB::table('telegram_users')->value('benefit_count'));
        $this->withToken($this->token($admin))->postJson('/api/v2/admin/association/confirm/delivery', ['operation_id' => (string) Str::uuid(), 'invitation_id' => $log])->assertOk()->assertJsonPath('data.already_applied', true);
        $this->assertSame(1, DB::table('message_logs')->where('message_type', 'received')->count());
        Http::assertSentCount(1);
    }
    public function test_native_deletion_hides_non_soft_deleted_rows_from_existing_web_models_and_preserves_audit_history(): void
    {
        $person = $this->beneficiary($this->category());
        $member = FamilyMember::create(['telegram_user_id' => $person, 'full_name' => 'Child', 'national_id' => '111111111']);
        [$admin] = $this->actor();
        $this->withToken($this->token($admin));
        $row = $this->getJson('/api/v2/admin/association/records/family-members/'.$member->id)->assertOk()->json('data.record');
        $this->postJson('/api/v2/admin/association/records/family-members/'.$member->id.'/action', ['operation_id' => (string) Str::uuid(), 'expected_revision' => $row['_revision'], 'action' => 'delete'])->assertOk();
        $this->assertNull(FamilyMember::find($member->id));
        $this->assertSame(1, DB::table('family_members')->where('id', $member->id)->count());
        $this->assertSame(1, DB::table('association_audit')->where('module', 'family-members')->where('action', 'delete')->count());
        $this->assertSame(0, DB::table('telegram_users')->where('id', $person)->value('family_members'));
    }

    public function test_web_family_creation_rejects_collisions_without_leaving_a_partial_parent_or_code(): void
    {
        $category = $this->category('Test');
        $this->beneficiary($category, '111111111');
        [, $staff] = $this->actor(); $this->actingAs($staff, 'web');
        $request = Request::create('/test', 'POST', ['full_name' => 'New parent', 'national_id' => '٢٢٢٢٢٢٢٢٢', 'status' => 'completed', 'categories' => [$category],
            'family_members' => [['full_name' => 'Child', 'national_id' => '١١١١١١١١١']]]);
        $response = app(AdminTelegramController::class)->storeUser($request);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(1, DB::table('telegram_users')->count());
        $this->assertSame(0, DB::table('family_members')->count());
        $this->assertSame(0, DB::table('categories')->value('last_number'));
        $request = Request::create('/test', 'POST', ['full_name' => 'Parent', 'national_id' => '222222222', 'status' => 'completed',
            'family_members' => [['full_name' => 'Child', 'national_id' => '222222222']]]);
        app(AdminTelegramController::class)->storeUser($request);
        $this->assertSame(1, DB::table('telegram_users')->count());
    }

    public function test_web_family_edit_validates_the_final_graph_and_cannot_move_another_familys_member(): void
    {
        $category = $this->category(); $one = $this->beneficiary($category, '111111111'); $two = $this->beneficiary($category, '222222222');
        $child = FamilyMember::create(['telegram_user_id' => $two, 'full_name' => 'Other child', 'national_id' => '333333333']);
        [, $staff] = $this->actor(); $this->actingAs($staff, 'web');
        $request = Request::create('/test', 'POST', ['full_name' => 'Should roll back', 'new_family_members' => [['full_name' => 'New child', 'national_id' => '٣٣٣٣٣٣٣٣٣']]]);
        app(AdminTelegramController::class)->update($request, $one);
        $this->assertNotSame('Should roll back', DB::table('telegram_users')->where('id', $one)->value('full_name'));
        $this->assertSame(1, DB::table('family_members')->count());
        $request = Request::create('/test', 'POST', ['family_members' => [$child->id => ['full_name' => 'Hijacked', 'national_id' => '444444444']]]);
        app(AdminTelegramController::class)->update($request, $one);
        $this->assertSame('Other child', $child->fresh()->full_name);
    }
}
