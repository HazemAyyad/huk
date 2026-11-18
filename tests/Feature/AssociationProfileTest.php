<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationProfileTest extends AssociationTestCase
{
    private function owner(int $category): array
    {
        $person = $this->beneficiary($category);
        $user = User::create(['name' => 'Owner', 'email' => 'profile@example.test', 'password' => 'owner-password', 'telegram_user_id' => $person]);
        return [AppAccount::create(['user_id' => $user->id, 'association_enabled' => true]), $person];
    }
    public function test_direct_edits_are_limited_and_staff_can_open_specific_fields_once(): void
    {
        [$owner, $person] = $this->owner($this->category());
        $token = $this->personal($owner);
        $profile = $this->withToken($token)->getJson('/api/v2/association/profile')->assertOk()->json('data');
        $this->putJson('/api/v2/association/profile', ['operation_id' => (string) Str::uuid(), 'expected_revision' => $profile['_revision'], 'record' => ['full_name' => 'Unauthorized']])->assertUnprocessable();
        $profile = $this->putJson('/api/v2/association/profile', ['operation_id' => (string) Str::uuid(), 'expected_revision' => $profile['_revision'], 'record' => ['address' => 'New address']])->assertOk()->json('data');
        $this->assertSame('New address', $profile['record']['address']);
        [$admin] = $this->actor();
        $this->withToken($this->token($admin))->postJson('/api/v2/admin/association/beneficiaries/'.$person.'/profile-fields', ['operation_id' => (string) Str::uuid(), 'fields' => ['full_name'], 'reason' => 'تصحيح الاسم بناء على المستند'])->assertOk();
        $profile = $this->withToken($token)->getJson('/api/v2/association/profile')->assertOk()->json('data');
        $profile = $this->putJson('/api/v2/association/profile', ['operation_id' => (string) Str::uuid(), 'expected_revision' => $profile['_revision'], 'record' => ['full_name' => 'Corrected']])->assertOk()->json('data');
        $this->assertSame('Corrected', $profile['record']['full_name']);
        $this->assertNotContains('full_name', $profile['editable_fields']);
        $this->putJson('/api/v2/association/profile', ['operation_id' => (string) Str::uuid(), 'expected_revision' => $profile['_revision'], 'record' => ['is_approved' => true, 'benefit_count' => 50]])->assertUnprocessable();
        $this->assertSame(0, DB::table('telegram_users')->value('benefit_count'));
    }
    public function test_scoped_staff_cannot_open_another_category_and_duplicate_identity_cannot_be_claimed(): void
    {
        $category = $this->category(); [$owner, $person] = $this->owner($category);
        $otherCategory = $this->category('Other'); $this->beneficiary($otherCategory, '987654321');
        [$scoped] = $this->actor(['admin.enter', 'association.enter', 'association.beneficiaries.update'], 'scoped', false, [$otherCategory]);
        $this->withToken($this->token($scoped))->postJson('/api/v2/admin/association/beneficiaries/'.$person.'/profile-fields', ['operation_id' => (string) Str::uuid(), 'fields' => ['national_id'], 'reason' => 'Attempt'])->assertNotFound();
        [$admin] = $this->actor();
        $this->withToken($this->token($admin))->postJson('/api/v2/admin/association/beneficiaries/'.$person.'/profile-fields', ['operation_id' => (string) Str::uuid(), 'fields' => ['national_id'], 'reason' => 'Correct identity'])->assertOk();
        $this->withToken($this->personal($owner));
        $profile = $this->getJson('/api/v2/association/profile')->assertOk()->json('data');
        $this->putJson('/api/v2/association/profile', ['operation_id' => (string) Str::uuid(), 'expected_revision' => $profile['_revision'], 'record' => ['national_id' => '987654321']])->assertUnprocessable();
        $this->assertSame('123456789', DB::table('telegram_users')->where('id', $person)->value('national_id'));
    }
}
