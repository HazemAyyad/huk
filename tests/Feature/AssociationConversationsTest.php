<?php

namespace Tests\Feature;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationConversationsTest extends AssociationTestCase
{
    private function owner(int $category): AppAccount
    {
        $person = $this->beneficiary($category);
        $user = User::create(['name' => 'Beneficiary', 'email' => 'beneficiary@example.test', 'password' => 'owner-password', 'telegram_user_id' => $person]);
        return AppAccount::create(['user_id' => $user->id, 'association_enabled' => true]);
    }
    private function grantChat(AppAccount $admin, AppAccount $owner, AppAccount $staff): void
    {
        $this->withToken($this->token($admin))->postJson('/api/v2/admin/association/chat/grants', ['operation_id' => (string) Str::uuid(), 'beneficiary_account_id' => $owner->id,
            'staff_account_id' => $staff->id, 'active' => true, 'reason' => 'تواصل مع الموظف المختص'])->assertOk();
    }
    private function send(string $prefix, int $thread, string $body = 'Hello', ?array $input = null)
    {
        return $this->postJson($prefix.'/threads/'.$thread.'/messages', $input ?? ['operation_id' => (string) Str::uuid(), 'client_id' => (string) Str::uuid(), 'body' => $body]);
    }

    public function test_beneficiary_conversation_requires_grant_and_one_message_is_retained_across_retries(): void
    {
        $owner = $this->owner($this->category());
        [$staff] = $this->actor();
        $this->withToken($this->personal($owner))->postJson('/api/v2/association/chat/start', ['operation_id' => (string) Str::uuid(), 'staff_account_id' => $staff->id])->assertForbidden();
        $this->grantChat($staff, $owner, $staff);
        $ownerToken = $this->personal($owner);
        $thread = $this->withToken($ownerToken)->postJson('/api/v2/association/chat/start', ['operation_id' => (string) Str::uuid(), 'staff_account_id' => $staff->id])->assertOk()->json('data.record');
        $input = ['operation_id' => (string) Str::uuid(), 'client_id' => (string) Str::uuid(), 'body' => 'Private message'];
        $this->send('/api/v2/association', $thread['id'], input: $input)->assertOk();
        $this->send('/api/v2/association', $thread['id'], input: $input)->assertOk();
        $this->assertSame(1, DB::table('association_thread_messages')->count());
        [$other] = $this->actor();
        $this->withToken($this->token($other))->getJson('/api/v2/admin/association/threads/'.$thread['id'])->assertNotFound();
        $staffToken = $this->token($staff);
        $current = $this->withToken($staffToken)->getJson('/api/v2/admin/association/threads/'.$thread['id'])->assertOk()->assertJsonPath('data.messages.0.body', 'Private message')->json('data.record');
        $this->postJson('/api/v2/admin/association/threads/'.$thread['id'].'/action', ['operation_id' => (string) Str::uuid(), 'action' => 'close', 'reason' => 'انتهى التواصل', 'expected_revision' => $current['_revision']])->assertOk();
        $this->withToken($ownerToken);
        $this->send('/api/v2/association', $thread['id'])->assertForbidden();
        $this->getJson('/api/v2/association/threads/'.$thread['id'])->assertOk()->assertJsonPath('data.record.can_send', false);
    }

    public function test_revoking_a_chat_grant_stops_new_messages_but_keeps_the_beneficiary_history(): void
    {
        $category = $this->category();
        $owner = $this->owner($category);
        [$staff] = $this->actor();
        $this->grantChat($staff, $owner, $staff);
        $ownerToken = $this->personal($owner);
        $thread = $this->withToken($ownerToken)->postJson('/api/v2/association/chat/start', ['operation_id' => (string) Str::uuid(), 'staff_account_id' => $staff->id])->assertOk()->json('data.record');
        $this->send('/api/v2/association', $thread['id'])->assertOk();
        DB::table('association_chat_grants')->update(['active' => false]);
        $this->send('/api/v2/association', $thread['id'])->assertForbidden();
        $this->getJson('/api/v2/association/threads/'.$thread['id'])->assertOk()->assertJsonCount(1, 'data.messages');
        $this->withToken($this->token($staff));
        $this->send('/api/v2/admin/association', $thread['id'])->assertForbidden();
    }

    public function test_support_has_one_active_ticket_only_staff_close_it_and_previous_history_remains(): void
    {
        $owner = $this->owner($this->category());
        [$staff] = $this->actor();
        $category = DB::table('association_support_categories')->insertGetId(['name' => 'Technical', 'staff_accounts' => json_encode([$staff->id]), 'active' => true]);
        $ownerToken = $this->personal($owner);
        $input = ['operation_id' => (string) Str::uuid(), 'category_id' => $category, 'subject' => 'Help'];
        $thread = $this->withToken($ownerToken)->postJson('/api/v2/association/support/start', $input)->assertOk()->json('data.record');
        $this->postJson('/api/v2/association/support/start', $input)->assertOk();
        $this->postJson('/api/v2/association/support/start', [...$input, 'operation_id' => (string) Str::uuid()])->assertConflict();
        $this->send('/api/v2/association', $thread['id'], 'Need support')->assertOk();
        $this->postJson('/api/v2/association/threads/'.$thread['id'].'/action', ['action' => 'close'])->assertNotFound();
        $current = $this->withToken($this->token($staff))->getJson('/api/v2/admin/association/threads/'.$thread['id'])->assertOk()->json('data.record');
        $this->postJson('/api/v2/admin/association/threads/'.$thread['id'].'/action', ['action' => 'close', 'operation_id' => (string) Str::uuid(), 'reason' => 'تم الحل', 'expected_revision' => $current['_revision']])->assertOk();
        $this->withToken($ownerToken)->postJson('/api/v2/association/support/start', [...$input, 'operation_id' => (string) Str::uuid()])->assertOk();
        $this->getJson('/api/v2/association/threads/'.$thread['id'])->assertOk()->assertJsonPath('data.messages.0.body', 'Need support');
        $this->assertSame(2, DB::table('association_threads')->where('kind', 'support')->count());
    }

    public function test_group_membership_revocation_and_read_receipts_do_not_leak_to_other_staff(): void
    {
        [$first] = $this->actor(); [$second] = $this->actor(); [$third] = $this->actor();
        $firstToken = $this->token($first);
        $thread = $this->withToken($firstToken)->postJson('/api/v2/admin/association/chat/threads', ['operation_id' => (string) Str::uuid(), 'subject' => 'Staff group', 'kind' => 'group', 'members' => [$second->id]])->assertOk()->json('data.record');
        $message = $this->send('/api/v2/admin/association', $thread['id'])->assertOk()->json('data.message_id');
        $this->withToken($this->token($second))->postJson('/api/v2/admin/association/threads/'.$thread['id'].'/presence', ['read_through' => 999999, 'typing' => true])->assertOk();
        $this->assertSame($message, DB::table('association_thread_members')->where('thread_id', $thread['id'])->where('account_id', $second->id)->value('read_through'));
        $this->withToken($this->token($third))->getJson('/api/v2/admin/association/threads/'.$thread['id'])->assertNotFound();
        $current = $this->withToken($firstToken)->getJson('/api/v2/admin/association/threads/'.$thread['id'])->assertOk()->json('data.record');
        $this->postJson('/api/v2/admin/association/threads/'.$thread['id'].'/action', ['action' => 'members', 'operation_id' => (string) Str::uuid(), 'reason' => 'تعديل المجموعة', 'expected_revision' => $current['_revision'], 'members' => [$third->id]])->assertOk();
        $this->withToken($this->token($second))->getJson('/api/v2/admin/association/threads/'.$thread['id'])->assertNotFound();
        $this->withToken($this->token($third))->getJson('/api/v2/admin/association/threads/'.$thread['id'])->assertOk()->assertJsonPath('data.messages.0.id', $message);
    }
}
