<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationFormsTest extends AssociationTestCase
{
    private function record(int $category): array
    {
        return ['title' => 'طلب خدمة', 'fields' => [
            ['key' => 'phone', 'label' => 'الهاتف', 'type' => 'text', 'required' => true],
            ['key' => 'address', 'label' => 'العنوان', 'type' => 'text', 'required' => true]],
            'audience' => ['category_ids' => [$category]], 'review_categories' => [$category], 'status' => 'published',
            'capacity' => 1, 'requires_location' => false];
    }

    private function owner(int $category, string $identity): AppAccount
    {
        $person = $this->beneficiary($category, $identity);
        $user = User::create(['name' => 'Owner', 'email' => Str::uuid().'@example.test', 'password' => 'owner-password', 'telegram_user_id' => $person]);

        return AppAccount::create(['user_id' => $user->id, 'association_enabled' => true]);
    }

    private function save(AppAccount $owner, array $form, array $answers, bool $final, ?array $previous = null)
    {
        return $this->withToken($this->personal($owner))->postJson('/api/v2/association/forms/'.$form['id'].'/submit', [
            'operation_id' => (string) Str::uuid(), 'form_revision' => $form['revision'], 'answers' => $answers, 'final' => $final,
            ...$previous ? ['expected_revision' => $previous['_revision']] : [],
        ]);
    }

    public function test_drafts_do_not_reserve_capacity_and_final_submission_is_unique_and_locked_to_the_owner(): void
    {
        $category = $this->category();
        [$admin] = $this->actor();
        $form = $this->withToken($this->token($admin))->postJson('/api/v2/admin/association/records/forms', ['operation_id' => (string) Str::uuid(), 'record' => $this->record($category)])->assertOk()->json('data.record');
        $first = $this->owner($category, '111111111');
        $second = $this->owner($category, '222222222');
        $draft = $this->save($first, $form, ['phone' => '123'], false)->assertOk()->json('data.submission');
        $this->save($second, $form, ['phone' => '456', 'address' => 'Address'], true)->assertOk();
        $this->save($first, $form, ['phone' => '123', 'address' => 'Other'], true, $draft)->assertConflict();
        $this->save($second, $form, ['phone' => 'Changed', 'address' => 'Other'], true)->assertUnprocessable();
        $this->assertSame(1, DB::table('association_form_submissions')->whereNotNull('submitted_at')->count());
        $outside = $this->owner($this->category('Other'), '333333333');
        $this->withToken($this->personal($outside))->getJson('/api/v2/association/forms/'.$form['id'])->assertNotFound();
        $this->save($outside, $form, ['phone' => 'x', 'address' => 'x'], true)->assertConflict();
    }

    public function test_staff_can_open_only_selected_fields_for_correction_and_schema_changes_preserve_the_original_submission(): void
    {
        $category = $this->category();
        [$admin] = $this->actor();
        $adminToken = $this->token($admin);
        $form = $this->withToken($adminToken)->postJson('/api/v2/admin/association/records/forms', ['operation_id' => (string) Str::uuid(), 'record' => $this->record($category)])->assertOk()->json('data.record');
        $owner = $this->owner($category, '111111111');
        $submission = $this->save($owner, $form, ['phone' => '123', 'address' => 'Old'], true)->assertOk()->json('data.submission');
        $review = $this->withToken($adminToken)->postJson('/api/v2/admin/association/records/submissions/'.$submission['id'].'/action', ['action' => 'review', 'operation_id' => (string) Str::uuid(),
            'expected_revision' => $submission['_revision'], 'status' => 'correction_requested', 'reason' => 'صحح العنوان', 'editable_fields' => ['address']])->assertOk()->json('data.record');
        $updated = $this->record($category);
        $updated['fields'][] = ['key' => 'new_field', 'label' => 'حقل جديد', 'type' => 'text', 'required' => true];
        $this->withToken($adminToken)->putJson('/api/v2/admin/association/records/forms/'.$form['id'], ['operation_id' => (string) Str::uuid(), 'expected_revision' => $form['_revision'], 'record' => $updated])->assertOk();
        $this->save($owner, $form, ['phone' => '999'], true, $review)->assertUnprocessable();
        $result = $this->save($owner, $form, ['address' => 'Corrected'], true, $review)->assertOk()->json('data.submission');
        $this->assertSame('123', $result['answers']['phone']);
        $this->assertSame('Corrected', $result['answers']['address']);
        $this->assertCount(2, $result['form_snapshot']['fields']);
        $this->assertSame([], $result['editable_fields']);
    }

    public function test_forms_require_actual_superadmin_and_reviews_require_both_category_and_department_scope(): void
    {
        $category = $this->category();
        [$editor] = $this->actor(['admin.enter', 'association.enter', 'association.forms.create'], 'ordinary');
        $this->withToken($this->token($editor))->postJson('/api/v2/admin/association/records/forms', ['operation_id' => (string) Str::uuid(), 'record' => $this->record($category)])->assertForbidden();
        [$admin] = $this->actor();
        $record = $this->record($category);
        $record['review_categories'] = [$this->category('Department')];
        $form = $this->withToken($this->token($admin))->postJson('/api/v2/admin/association/records/forms', ['operation_id' => (string) Str::uuid(), 'record' => $record])->assertOk()->json('data.record');
        $submission = $this->save($this->owner($category, '111111111'), $form, ['phone' => '123', 'address' => 'Address'], true)->assertOk()->json('data.submission');
        [$reviewer] = $this->actor(['admin.enter', 'association.enter', 'association.submissions.view', 'association.submissions.review'], 'reviewer', false, [$category]);
        $this->withToken($this->token($reviewer))->getJson('/api/v2/admin/association/records/submissions/'.$submission['id'])->assertNotFound();
    }
}
