<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationRecordActionsTest extends AssociationTestCase
{
    private function cases(bool $boolean = false): array
    {
        $module = $boolean ? 'cases-patients' : 'cases-amputations';
        $table = $boolean ? 'patient_cases' : 'amputation_cases';
        Schema::create($table, function (Blueprint $t) use ($boolean): void {
            $t->id(); $t->string('full_name'); $t->string('national_id')->nullable();
            $t->boolean('is_reviewed')->default(false); $t->unsignedBigInteger('researcher_id')->nullable();
            $t->text('case_description')->nullable(); $t->text('researcher_opinion')->nullable();
            if ($boolean) { $t->boolean('deserves_support')->nullable(); $t->text('deserve_reason')->nullable(); $t->string('disease_type')->nullable(); $t->string('other_disease_type')->nullable(); }
            else { $t->string('family_deserves')->nullable(); $t->text('family_reason')->nullable(); }
            $t->boolean('is_sent_for_attendance')->default(false); $t->boolean('is_attended')->default(false); $t->boolean('is_benefited')->default(false); $t->timestamps();
        });
        $id = DB::table($table)->insertGetId(['full_name' => 'Case', 'national_id' => '123456789', 'created_at' => now(), 'updated_at' => now()]);
        return [$module, $table, $id];
    }

    public function test_case_review_keeps_all_four_web_fields_and_cannot_be_bypassed_by_generic_update(): void
    {
        [$module, $table, $id] = $this->cases();
        [$account, $staff] = $this->actor();
        $this->withToken($this->token($account));
        $url = '/api/v2/admin/association/records/'.$module.'/'.$id;
        $data = $this->getJson($url)->assertOk()->json('data');
        $this->assertSame(['case_description', 'researcher_opinion', 'family_deserves', 'family_reason'], array_column($data['action_forms']['review'], 'key'));
        $this->putJson($url, ['operation_id' => (string) Str::uuid(), 'expected_revision' => $data['record']['_revision'], 'record' => ['family_deserves' => 'yes']])->assertUnprocessable();
        $input = ['operation_id' => (string) Str::uuid(), 'expected_revision' => $data['record']['_revision'], 'action' => 'review', 'case_description' => 'وصف الحالة', 'researcher_opinion' => 'رأي الباحث', 'family_deserves' => 'no', 'family_reason' => 'سبب القرار'];
        $this->postJson($url.'/action', array_diff_key($input, ['researcher_opinion' => true]))->assertUnprocessable();
        $this->postJson($url.'/action', $input)->assertOk();
        $this->assertSame('no', DB::table($table)->value('family_deserves'));
        $this->assertSame($staff->id, DB::table($table)->value('researcher_id'));
        $this->assertSame('رأي الباحث', DB::table($table)->value('researcher_opinion'));
        $this->postJson($url.'/action', $input)->assertOk();
        $this->assertSame(1, DB::table('association_audit')->where('action', 'review')->count());
    }

    public function test_patient_review_persists_false_in_the_actual_boolean_support_fields(): void
    {
        [$module, $table, $id] = $this->cases(true);
        [$account] = $this->actor(); $this->withToken($this->token($account));
        $url = '/api/v2/admin/association/records/'.$module.'/'.$id;
        $data = $this->getJson($url)->assertOk()->json('data');
        $this->postJson($url.'/action', ['operation_id' => (string) Str::uuid(), 'expected_revision' => $data['record']['_revision'], 'action' => 'review',
            'case_description' => 'وصف', 'researcher_opinion' => 'رأي', 'deserves_support' => false, 'deserve_reason' => 'لم يتحقق الاستحقاق'])->assertOk();
        $this->assertSame(0, DB::table($table)->value('deserves_support'));
        $this->assertSame('لم يتحقق الاستحقاق', DB::table($table)->value('deserve_reason'));
    }

    public function test_case_actions_keep_the_correct_history_foreign_key_and_do_not_duplicate_on_retry(): void
    {
        [$module, $table, $id] = $this->cases();
        Schema::create('amputation_messages', function (Blueprint $t): void { $t->id(); $t->unsignedBigInteger('amputation_id'); $t->unsignedBigInteger('user_id'); $t->text('message'); $t->decimal('latitude', 10, 6)->nullable(); $t->decimal('longitude', 10, 6)->nullable(); $t->timestamps(); });
        [$account, $staff] = $this->actor(); $this->withToken($this->token($account));
        $url = '/api/v2/admin/association/records/'.$module.'/'.$id;
        foreach (['invite', 'attend', 'benefit'] as $action) {
            $revision = $this->getJson($url)->assertOk()->json('data.record._revision');
            $input = ['operation_id' => (string) Str::uuid(), 'expected_revision' => $revision, 'action' => $action, 'reason' => 'ملاحظة موثقة', 'latitude' => 31.5, 'longitude' => 34.4];
            $this->postJson($url.'/action', $input)->assertOk(); $this->postJson($url.'/action', $input)->assertOk();
        }
        $this->assertSame(3, DB::table('amputation_messages')->count());
        $this->assertSame($id, DB::table('amputation_messages')->value('amputation_id'));
        $this->assertSame($staff->id, DB::table('amputation_messages')->value('user_id'));
        $this->assertEquals(34.4, DB::table('amputation_messages')->value('longitude'));
        $this->assertSame(1, DB::table($table)->value('is_benefited'));
    }

    public function test_scoped_staff_can_restore_only_deleted_beneficiaries_in_their_categories(): void
    {
        $allowed = $this->category('Allowed'); $other = $this->category('Hidden');
        $one = $this->beneficiary($allowed, '111111111'); $two = $this->beneficiary($other, '222222222');
        DB::table('telegram_users')->whereIn('id', [$one, $two])->update(['deleted_at' => now(), 'deleted_by' => 123]);
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.beneficiaries.view', 'association.beneficiaries.restore'], 'restorer', false, [$allowed]);
        $this->withToken($this->token($account));
        $url = '/api/v2/admin/association/records/beneficiaries';
        $this->getJson($url)->assertOk()->assertJsonCount(0, 'data.items');
        $this->getJson($url.'?trashed=1')->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.id', $one);
        $data = $this->getJson($url.'/'.$one.'?trashed=1')->assertOk()->assertJsonPath('data.can_edit', false)->json('data');
        $this->getJson($url.'/'.$two.'?trashed=1')->assertNotFound();
        $this->postJson($url.'/'.$one.'/action', ['action' => 'restore', 'operation_id' => (string) Str::uuid(), 'expected_revision' => $data['record']['_revision']])->assertOk();
        $this->getJson($url)->assertOk()->assertJsonCount(1, 'data.items');
        $this->assertNotNull(DB::table('telegram_users')->where('id', $two)->value('deleted_at'));
    }
}
