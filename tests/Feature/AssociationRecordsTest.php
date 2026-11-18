<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Services\Association\AssociationMedia;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationRecordsTest extends AssociationTestCase
{
    private function endpoint(string $module, ?int $id = null): string
    {
        return '/api/v2/admin/association/records/'.$module.($id === null ? '' : '/'.$id);
    }

    private function operation(array $record, ?string $revision = null): array
    {
        return ['operation_id' => (string) Str::uuid(), 'record' => $record, ...($revision === null ? [] : ['expected_revision' => $revision])];
    }

    public function test_translation_permissions_do_not_open_association_data(): void
    {
        [$account] = $this->actor(['admin.enter', 'translations.edit']);
        $this->withToken($this->token($account))->getJson('/api/v2/admin/association/manifest')->assertForbidden();
        $this->getJson($this->endpoint('beneficiaries'))->assertForbidden();
    }

    public function test_beneficiary_creation_issues_one_code_and_preserves_original_staff_attribution(): void
    {
        $category = $this->category('O');
        [$account, $staff] = $this->actor();
        $token = $this->token($account);
        $input = $this->operation(['full_name' => 'اسم مستفيد', 'national_id' => '١٢٣٤٥٦٧٨٩', 'categories' => [$category]]);
        $data = $this->withToken($token)->postJson($this->endpoint('beneficiaries'), $input)->assertOk()->json('data.record');
        $this->assertSame('application', $data['source']);
        $this->assertSame('123456789', $data['national_id']);
        $this->assertSame('O-1', $data['access_code']);
        $this->assertSame($staff->id, $data['created_by']);
        $this->withToken($token)->postJson($this->endpoint('beneficiaries'), $input)->assertOk()->assertJsonPath('data.record.id', $data['id']);
        $this->assertSame(1, DB::table('telegram_users')->count());
        $this->assertSame(1, DB::table('categories')->value('last_number'));
        $this->assertSame(1, DB::table('categories')->value('total_assigned'));
        $this->assertSame(1, DB::table('association_audit')->where('action', 'create')->count());
    }

    public function test_cached_success_is_rechecked_against_the_current_category_scope(): void
    {
        $allowed = $this->category('Allowed');
        $other = $this->category('Other');
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.beneficiaries.view', 'association.beneficiaries.create'], 'scoped', false, [$allowed]);
        $token = $this->token($account);
        $input = $this->operation(['full_name' => 'One', 'national_id' => '123456789', 'categories' => [$allowed]]);
        $this->withToken($token)->postJson($this->endpoint('beneficiaries'), $input)->assertOk();
        DB::table('app_admin_memberships')->where('app_account_id', $account->id)->update(['association_categories' => json_encode([$other])]);
        $this->withToken($token)->postJson($this->endpoint('beneficiaries'), $input)->assertForbidden();
        $this->withToken($token)->getJson($this->endpoint('beneficiaries'))->assertOk()->assertJsonCount(0, 'data.items');
    }

    public function test_stale_revisions_include_category_membership_even_with_identical_timestamps(): void
    {
        Carbon::setTestNow('2030-01-01 12:00:00');
        try {
            $first = $this->category('First');
            $second = $this->category('Second');
            [$account] = $this->actor();
            $token = $this->token($account);
            $created = $this->withToken($token)->postJson($this->endpoint('beneficiaries'), $this->operation(['full_name' => 'One', 'national_id' => '123456789', 'categories' => [$first]]))->assertOk()->json('data.record');
            $this->withToken($token)->putJson($this->endpoint('beneficiaries', $created['id']), $this->operation(['categories' => [$first, $second]], $created['_revision']))->assertOk();
            $this->withToken($token)->putJson($this->endpoint('beneficiaries', $created['id']), $this->operation(['address' => 'Stale'], $created['_revision']))->assertConflict();
            $this->assertNull(DB::table('telegram_users')->value('address'));
            $this->assertSame(2, DB::table('user_categories')->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_family_member_creation_cannot_cross_a_staff_scope_or_duplicate_an_identity(): void
    {
        $allowed = $this->category('Allowed');
        $other = $this->category('Other');
        $owner = $this->beneficiary($allowed);
        $hidden = $this->beneficiary($other, '987654321');
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.family-members.create', 'association.family-members.view'], 'scoped', false, [$allowed]);
        $token = $this->token($account);
        $this->withToken($token)->postJson($this->endpoint('family-members'), $this->operation(['telegram_user_id' => $hidden, 'full_name' => 'Child', 'national_id' => '111111111']))->assertNotFound();
        $this->withToken($token)->postJson($this->endpoint('family-members'), $this->operation(['telegram_user_id' => $owner, 'full_name' => 'Child', 'national_id' => '987654321']))->assertUnprocessable();
        $this->assertSame(0, DB::table('family_members')->count());
        $this->withToken($token)->postJson($this->endpoint('family-members'), $this->operation(['telegram_user_id' => $owner, 'full_name' => 'Child', 'national_id' => '111111111']))->assertOk();
        $this->assertSame(1, DB::table('telegram_users')->where('id', $owner)->value('family_members'));
    }

    public function test_clients_cannot_set_a_benefit_counter_or_staff_attribution_directly(): void
    {
        $category = $this->category();
        $beneficiary = $this->beneficiary($category);
        [$account] = $this->actor();
        $token = $this->token($account);
        $row = $this->withToken($token)->getJson($this->endpoint('beneficiaries', $beneficiary))->assertOk()->json('data.record');
        $this->withToken($token)->putJson($this->endpoint('beneficiaries', $beneficiary), $this->operation(['benefit_count' => 900, 'updated_by' => 999], $row['_revision']))->assertUnprocessable();
        $this->assertSame(0, DB::table('telegram_users')->value('benefit_count'));
        $this->assertSame(0, DB::table('association_operations')->count());
    }

    public function test_an_expired_legacy_event_cannot_be_reactivated_by_changing_its_date(): void
    {
        $event = DB::table('events')->insertGetId(['name' => 'Past', 'event_date' => now()->subDays(3)->toDateString(), 'event_time' => '10:00:00', 'created_at' => now(), 'updated_at' => now()]);
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.events.view', 'association.events.update'], 'events-editor');
        $token = $this->token($account);
        $row = $this->withToken($token)->getJson($this->endpoint('events', $event))->assertOk()->json('data.record');
        $this->withToken($token)->putJson($this->endpoint('events', $event), $this->operation(['event_date' => now()->addDay()->toDateString()], $row['_revision']))->assertForbidden();
        $this->assertSame('Past', DB::table('events')->value('name'));
        $this->assertSame(0, DB::table('association_event_settings')->count());
    }

    public function test_attachment_size_is_not_limited_to_the_previous_one_megabyte_default(): void
    {
        Storage::fake('local');
        [$account] = $this->actor();
        $file = UploadedFile::fake()->createWithContent('large.dat', str_repeat('a', 2 * 1024 * 1024));
        $saved = app(AssociationMedia::class)->store($account, 'orphan-requests', 'death_certificate', $file);
        $this->assertSame(2 * 1024 * 1024, $saved['bytes']);
        $this->assertSame(1, DB::table('association_uploads')->count());
        $this->assertCount(1, Storage::disk('local')->allFiles('association/uploads'));
    }
}
