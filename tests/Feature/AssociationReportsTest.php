<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationReportsTest extends AssociationTestCase
{
    private const BASE = '/api/v2/admin/association/';

    public function test_settings_reject_stale_updates_and_never_allow_secret_configuration(): void
    {
        [$account] = $this->actor(); $this->withToken($this->token($account));
        $original = $this->getJson(self::BASE.'settings')->assertOk()->assertJsonPath('data.can_edit', true)->json('data');
        $input = ['operation_id' => (string) Str::uuid(), 'expected_revision' => $original['revision'], 'record' => ['contact_phone' => '12345', 'support_instructions' => 'تعليمات الدعم']];
        $this->putJson(self::BASE.'settings', $input)->assertOk()->assertJsonPath('data.record.contact_phone', '12345');
        $this->putJson(self::BASE.'settings', $input)->assertOk();
        $this->putJson(self::BASE.'settings', [...$input, 'operation_id' => (string) Str::uuid(), 'record' => ['contact_phone' => 'stale']])->assertConflict();
        $this->putJson(self::BASE.'settings', [...$input, 'operation_id' => (string) Str::uuid(), 'record' => ['api_key' => 'not-allowed']])->assertUnprocessable();
        $this->assertSame(1, DB::table('association_audit')->where('module', 'settings')->count());
        [$reader] = $this->actor(['admin.enter', 'association.enter', 'association.settings.view'], 'settings-reader');
        $this->withToken($this->token($reader))->getJson(self::BASE.'settings')->assertOk()->assertJsonPath('data.can_edit', false);
        $this->putJson(self::BASE.'settings', $input)->assertForbidden();
    }

    public function test_usage_counts_unique_enabled_accounts_and_keeps_store_downloads_unknown(): void
    {
        [$account] = $this->actor(); [$disabled] = $this->actor();
        DB::table('association_account_policies')->insert(['account_id' => $disabled->id, 'app_enabled' => false]);
        foreach ([$account->id, $account->id, $disabled->id] as $id) {
            DB::table('association_devices')->insert(['account_id' => $id, 'device_hash' => hash('sha256', Str::uuid()), 'platform' => 'android', 'last_seen_at' => now()]);
        }
        foreach ([$account->id, $disabled->id] as $id) { DB::table('association_usage_days')->insert(['account_id' => $id, 'day' => now()->toDateString(), 'last_seen_at' => now()]); }
        $data = $this->withToken($this->token($account))->getJson(self::BASE.'usage')->assertOk()->json('data');
        $metrics = array_column($data['metrics'], 'value', 'key');
        $this->assertSame(1, $metrics['active_now']); $this->assertSame(1, $metrics['active_today']);
        $this->assertSame(2, $metrics['registered_devices']); $this->assertNull($metrics['store_downloads']);
        $this->assertCount(14, $data['daily']); $this->assertSame(1, $data['daily'][13]['accounts']);
    }

    public function test_device_lists_and_details_redact_credentials_and_honor_read_only_permissions(): void
    {
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.sessions.view'], 'device-reader');
        $id = DB::table('association_devices')->insertGetId(['account_id' => $account->id, 'device_hash' => 'private-hash', 'label' => 'Private phone', 'platform' => 'android', 'fcm_token' => 'private-token', 'last_seen_at' => now()]);
        $this->withToken($this->token($account));
        $list = $this->getJson(self::BASE.'devices?q=Private&status=active')->assertOk()->assertJsonPath('data.can_revoke', false)->assertJsonCount(1, 'data.items');
        $detail = $this->getJson(self::BASE.'devices/'.$id)->assertOk()->assertJsonPath('data.record.push_registered', true);
        foreach ([$list, $detail] as $response) { $this->assertStringNotContainsString('private-token', $response->getContent()); $this->assertStringNotContainsString('private-hash', $response->getContent()); }
        $this->postJson(self::BASE.'devices/'.$id.'/revoke', ['operation_id' => (string) Str::uuid(), 'account_id' => $account->id, 'reason' => 'فك الارتباط'])->assertForbidden();
    }

    public function test_submission_exports_respect_both_beneficiary_scope_and_form_review_categories(): void
    {
        $allowed = $this->category('Allowed'); $other = $this->category('Other');
        $one = $this->beneficiary($allowed, '111111111'); $two = $this->beneficiary($other, '222222222');
        [$account, $staff] = $this->actor(['admin.enter', 'association.enter', 'association.submissions.view', 'association.submissions.export'], 'reviewer', false, [$allowed]);
        $first = DB::table('association_forms')->insertGetId(['title' => 'Allowed form', 'fields' => '[]', 'review_categories' => json_encode([$allowed]), 'created_by' => $staff->id]);
        $second = DB::table('association_forms')->insertGetId(['title' => 'Other review team', 'fields' => '[]', 'review_categories' => json_encode([$other]), 'created_by' => $staff->id]);
        foreach ([[$first, $one, 'VISIBLE'], [$first, $two, 'HIDDEN_PERSON'], [$second, $one, 'HIDDEN_REVIEW_TEAM']] as [$form, $person, $answer]) {
            DB::table('association_form_submissions')->insert(['form_id' => $form, 'beneficiary_id' => $person, 'account_id' => $account->id, 'answers' => json_encode(['answer' => $answer]), 'submitted_at' => now(), 'status' => 'submitted']);
        }
        $this->withToken($this->token($account));
        $this->getJson(self::BASE.'exports/submissions?metadata=1')->assertOk()->assertJsonPath('data.rows', 1)->assertJsonCount(1, 'data.sample');
        $csv = $this->get(self::BASE.'exports/submissions')->assertOk()->streamedContent();
        $this->assertStringContainsString('VISIBLE', $csv); $this->assertStringNotContainsString('HIDDEN_PERSON', $csv); $this->assertStringNotContainsString('HIDDEN_REVIEW_TEAM', $csv);
    }

    public function test_audit_report_filters_are_used_for_preview_and_download_and_usage_exports_unknown_values_honestly(): void
    {
        [$account] = $this->actor();
        DB::table('association_audit')->insert(['account_id' => $account->id, 'module' => 'beneficiaries', 'record_id' => 1, 'action' => 'review', 'created_at' => now()]);
        DB::table('association_audit')->insert(['account_id' => $account->id, 'module' => 'events', 'record_id' => 2, 'action' => 'create', 'created_at' => now()->subDays(5)]);
        $this->withToken($this->token($account));
        $query = '?q=review&from='.now()->toDateString().'&to='.now()->toDateString();
        $this->getJson(self::BASE.'exports/audit'.$query.'&metadata=1')->assertOk()->assertJsonPath('data.rows', 1);
        $csv = $this->get(self::BASE.'exports/audit'.$query)->assertOk()->streamedContent();
        $this->assertStringContainsString('beneficiaries', $csv); $this->assertStringNotContainsString('events', $csv);
        $usage = $this->getJson(self::BASE.'exports/usage?metadata=1')->assertOk()->json('data.sample');
        $store = array_values(array_filter($usage, fn ($row) => $row['key'] === 'store_downloads'))[0];
        $this->assertNull($store['value']); $this->assertNotEmpty($store['note']);
    }

    public function test_support_category_patch_preserves_its_name_and_staff_when_only_active_changes(): void
    {
        [$account] = $this->actor();
        $id = DB::table('association_support_categories')->insertGetId(['name' => 'مساعدة تقنية', 'staff_accounts' => json_encode([$account->id]), 'active' => true]);
        $this->withToken($this->token($account));
        $url = self::BASE.'records/support-categories';
        $this->getJson($url)->assertOk()->assertJsonPath('data.total', 1);
        $row = $this->getJson($url.'/'.$id)->assertOk()->json('data.record');
        $this->putJson($url.'/'.$id, ['operation_id' => (string) Str::uuid(), 'expected_revision' => $row['_revision'], 'record' => ['active' => false]])
            ->assertOk()->assertJsonPath('data.record.name', 'مساعدة تقنية')->assertJsonPath('data.record.staff_accounts', [$account->id]);
        $this->assertSame(0, DB::table('association_support_categories')->value('active'));
    }
}
