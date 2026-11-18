<?php

namespace Tests\Feature;

use App\Services\Religious\AppAdminAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationStaffTest extends AssociationTestCase
{
    public function test_superadmin_creates_staff_with_an_explicit_scoped_app_grant_and_no_password_disclosure(): void
    {
        $category = $this->category();
        [$account] = $this->actor();
        $this->withToken($this->token($account));
        $input = ['operation_id' => (string) Str::uuid(), 'record' => ['name' => 'Researcher', 'email' => 'researcher@example.test', 'password' => 'a-test-password',
            'roles' => [], 'app_enabled' => true, 'admin_enabled' => true, 'app_permissions' => ['admin.enter', 'association.enter', 'association.beneficiaries.view'],
            'association_global' => false, 'association_categories' => [$category], 'device_limit' => 2]];
        $row = $this->postJson('/api/v2/admin/association/records/staff', $input)->assertOk()->assertJsonMissingPath('data.record.password')->json('data.record');
        $this->assertTrue(Hash::check('a-test-password', DB::table('users')->where('id', $row['id'])->value('password')));
        $target = \App\Models\AppAccount::where('user_id', $row['id'])->firstOrFail();
        $session = app(AppAdminAccess::class)->session($target);
        $this->assertSame([$category], $session['association_scope']['categories']);
        $this->assertContains('association.beneficiaries.view', $session['permissions']);
        $this->assertNotContains('association.beneficiaries.update', $session['permissions']);
        $this->postJson('/api/v2/admin/association/records/staff', $input)->assertOk()->assertJsonPath('data.record.id', $row['id']);
        $this->assertSame(2, DB::table('users')->count());
    }

    public function test_an_actor_cannot_change_its_own_grant_or_assign_unowned_permissions(): void
    {
        [$account, $staff] = $this->actor(['admin.enter', 'association.enter', 'association.staff.view', 'association.staff.update'], 'staff-editor');
        $this->withToken($this->token($account));
        $row = $this->getJson('/api/v2/admin/association/records/staff/'.$staff->id)->assertOk()->json('data.record');
        $this->putJson('/api/v2/admin/association/records/staff/'.$staff->id, ['operation_id' => (string) Str::uuid(), 'expected_revision' => $row['_revision'], 'record' => ['association_global' => true]])->assertForbidden();
        [$target, $targetStaff] = $this->actor(['admin.enter'], 'target');
        $this->withToken($this->token($account));
        $row = $this->getJson('/api/v2/admin/association/records/staff/'.$targetStaff->id)->assertOk()->json('data.record');
        $this->putJson('/api/v2/admin/association/records/staff/'.$targetStaff->id, ['operation_id' => (string) Str::uuid(), 'expected_revision' => $row['_revision'],
            'record' => ['app_permissions' => ['admin.enter', 'association.events.reactivate']]])->assertForbidden();
        $this->assertSame(0, DB::table('association_operations')->count());
    }
}
