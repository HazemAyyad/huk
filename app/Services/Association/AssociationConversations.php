<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationConversations
{
    public function __construct(private AssociationAccess $access, private AssociationOperations $operations, private AssociationMedia $media,
        private AssociationNotifications $notifications, private AssociationPortal $portal) {}

    private function module(object $thread): string { return $thread->kind === 'support' ? 'support' : 'chat'; }

    private function member(AppAccount $account, int $thread): ?object
    {
        return DB::table('association_thread_members')->where('thread_id', $thread)->where('account_id', $account->id)->where('active', true)->first();
    }

    private function grantFor(AppAccount $account, string $module): ?object
    {
        if (! $this->access->can($account, $module)) { return null; }
        return $this->access->require($account, $module);
    }

    private function peerAllowed(AppAccount $staff, AppAccount $owner, object $grant): bool
    {
        if ($this->access->global($grant)) { return true; }
        $person = $owner->user_id ? User::whereKey($owner->user_id)->value('telegram_user_id') : null;
        if ($person && $this->access->beneficiaryQuery($grant)->where('telegram_users.id', $person)->exists()) { return true; }
        return DB::table('association_chat_grants')->where('staff_account_id', $staff->id)->where('beneficiary_account_id', $owner->id)->where('active', true)->exists();
    }

    public function canRead(AppAccount $account, int $id): bool
    {
        $thread = DB::table('association_threads')->where('id', $id)->first();
        if (! $thread || ! $account->active || ! $this->member($account, $id)) { return false; }
        if ((int) $thread->owner_account_id === $account->id) { return true; }
        $token = $account->currentAccessToken();
        if ($token && ! $token->can('app:admin')) { return false; }
        $grant = $this->grantFor($account, $this->module($thread));
        if (! $grant) { return false; }
        if ($thread->kind === 'support') {
            if ((int) $thread->assigned_to === $account->id) { return true; }
            $category = DB::table('association_support_categories')->where('id', $thread->support_category_id)->first();
            return $category && in_array($account->id, json_decode($category->staff_accounts, true) ?: [], true);
        }
        if ($thread->owner_account_id) {
            $owner = AppAccount::find($thread->owner_account_id);
            return $owner && $this->peerAllowed($account, $owner, $grant);
        }

        return true;
    }

    private function thread(AppAccount $account, int $id, bool $lock = false): object
    {
        abort_unless($this->canRead($account, $id), 404);
        $query = DB::table('association_threads')->where('id', $id);
        return ($lock ? $query->lockForUpdate() : $query)->first() ?? abort(404);
    }

    public function canSend(AppAccount $account, object $thread): bool
    {
        if ($thread->status !== 'open' || $thread->archived_at || ! $this->canRead($account, $thread->id)) { return false; }
        if ((int) $thread->owner_account_id !== $account->id) {
            if ($thread->owner_account_id && $thread->kind !== 'support' && ! DB::table('association_chat_grants')->where('beneficiary_account_id', $thread->owner_account_id)->where('staff_account_id', $account->id)->where('active', true)->exists()) { return false; }
            return $this->access->can($account, $this->module($thread), 'send');
        }
        if ($thread->kind === 'support') { return true; }
        return DB::table('association_chat_grants')->where('beneficiary_account_id', $account->id)->where('active', true)
            ->whereIn('staff_account_id', DB::table('association_thread_members')->where('thread_id', $thread->id)->where('active', true)->select('account_id'))->exists();
    }

    private function describe(AppAccount $account, object $thread): array
    {
        $member = $this->member($account, $thread->id);
        $data = (array) $thread;
        $data['_label'] = $thread->subject;
        $data['_revision'] = $this->operations->revision($thread);
        $data['can_send'] = $this->canSend($account, $thread);
        $data['unread'] = DB::table('association_thread_messages')->where('thread_id', $thread->id)->where('id', '>', $member->read_through)->where('account_id', '!=', $account->id)->count();
        $data['members'] = [];
        foreach (DB::table('association_thread_members')->where('thread_id', $thread->id)->where('active', true)->orderBy('id')->get() as $peer) {
            $identity = AppAccount::find($peer->account_id);
            $name = $identity?->user_id ? User::whereKey($identity->user_id)->value('name') : ($identity?->religious_account_id ? \App\Models\ReligiousAccount::whereKey($identity->religious_account_id)->value('name') : null);
            $lastSeen = DB::table('association_devices')->where('account_id', $peer->account_id)->whereNull('revoked_at')->max('last_seen_at');
            $data['members'][] = ['account_id' => $peer->account_id, 'name' => $name, 'role' => $peer->role,
                'read_through' => $peer->read_through, 'delivered_through' => $peer->delivered_through,
                'typing' => $peer->typing_until && \Carbon\CarbonImmutable::parse($peer->typing_until)->isFuture(),
                'last_seen_at' => $lastSeen, 'online' => $lastSeen && \Carbon\CarbonImmutable::parse($lastSeen)->greaterThan(now()->subMinutes(config('association.presence_minutes')))];
        }
        $data['actions'] = [];
        $grant = $this->grantFor($account, $this->module($thread));
        if ($grant && (int) $thread->owner_account_id !== $account->id) {
            foreach (['transfer', 'assign', 'close', 'archive', 'delete'] as $action) { if ($this->access->allows($grant, $this->module($thread), $action)) { $data['actions'][] = $action; } }
        }

        return $data;
    }

    public function index(AppAccount $account, string $module, array $filter): array
    {
        abort_unless(in_array($module, ['chat', 'support'], true), 404);
        $query = DB::table('association_threads')->whereIn('id', DB::table('association_thread_members')->where('account_id', $account->id)->where('active', true)->select('thread_id'));
        $module === 'support' ? $query->where('kind', 'support') : $query->where('kind', '!=', 'support');
        if (empty($filter['archived'])) { $query->whereNull('archived_at'); }
        if (! empty($filter['status'])) { $query->where('status', $filter['status']); }
        if (! empty($filter['q'])) { $query->where('subject', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $filter['q']).'%'); }
        $page = $query->orderByDesc('updated_at')->orderByDesc('id')->paginate(30);
        $rows = array_values(array_filter($page->items(), fn ($row) => $this->canRead($account, $row->id)));

        return ['title' => $module === 'support' ? 'الدعم الفني' : 'المحادثات', 'items' => array_map(fn ($row) => $this->describe($account, $row), $rows),
            'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => null, 'fields' => [], 'can_edit' => false,
            'can_create' => $module === 'support' || $this->access->can($account, 'chat', 'create'), 'actions' => []];
    }

    private function addMember(int $thread, int $account, string $role): void
    {
        DB::table('association_thread_members')->updateOrInsert(['thread_id' => $thread, 'account_id' => $account], ['role' => $role, 'active' => true, 'typing_until' => null, 'updated_at' => now()]);
    }

    public function grant(AppAccount $account, array $input): array
    {
        $grant = $this->access->require($account, 'chat', 'grant');
        $data = Validator::make($input, ['beneficiary_account_id' => 'required|integer|min:1', 'staff_account_id' => 'required|integer|min:1', 'active' => 'required|boolean', 'reason' => 'required|string|min:3|max:2000'])->validate();
        $owner = AppAccount::whereKey($data['beneficiary_account_id'])->where('active', true)->firstOrFail();
        $person = $this->portal->person($owner);
        $this->access->beneficiary($grant, $person->id);
        $staff = AppAccount::whereKey($data['staff_account_id'])->where('active', true)->firstOrFail();
        $this->access->require($staff, 'chat');
        abort_if($owner->id === $staff->id, 422);

        return $this->operations->run($account, 'chat.grant', $input, function () use ($account, $grant, $data): array {
            DB::table('association_chat_grants')->updateOrInsert(['beneficiary_account_id' => $data['beneficiary_account_id'], 'staff_account_id' => $data['staff_account_id']],
                ['active' => $data['active'], 'granted_by' => $grant->staff_id, 'updated_at' => now()]);
            $this->operations->audit($account, $grant, 'chat', $data['beneficiary_account_id'], 'grant', null, $data, $data['reason']);

            return ['updated' => true];
        });
    }

    public function create(AppAccount $account, array $input): array
    {
        $grant = $this->access->require($account, 'chat', 'create');
        $data = Validator::make($input, ['subject' => 'required|string|max:255', 'kind' => 'required|in:direct,group', 'members' => 'required|array|min:1|max:100', 'members.*' => 'integer|distinct|min:1',
            'beneficiary_account_id' => 'nullable|integer|min:1'])->validate();
        $ids = array_values(array_unique([$account->id, ...$data['members']])); sort($ids, SORT_NUMERIC);
        $owner = isset($data['beneficiary_account_id']) ? AppAccount::whereKey($data['beneficiary_account_id'])->where('active', true)->firstOrFail() : null;
        if ($owner) {
            abort_unless($data['kind'] === 'direct' && count($ids) === 2 && in_array($owner->id, $ids, true) && $owner->id !== $account->id, 422, 'beneficiary_conversation_must_be_direct');
            $this->portal->person($owner);
            abort_unless($this->peerAllowed($account, $owner, $grant) && DB::table('association_chat_grants')->where('beneficiary_account_id', $owner->id)->where('staff_account_id', $account->id)->where('active', true)->exists(), 403, 'beneficiary_chat_not_enabled');
        }
        foreach ($ids as $id) {
            if ($owner?->id === $id) { continue; }
            $peer = AppAccount::whereKey($id)->where('active', true)->firstOrFail();
            $this->access->require($peer, 'chat');
        }
        if ($data['kind'] === 'direct') { abort_unless(count($ids) === 2, 422, 'direct_conversation_requires_two_members'); }

        return $this->createDirectOrGroup($account, $grant, $data, $ids, $owner?->id, $input);
    }

    private function createDirectOrGroup(AppAccount $account, ?object $grant, array $data, array $ids, ?int $owner, array $input): array
    {
        return $this->operations->run($account, 'chat.create', $input, function () use ($account, $grant, $data, $ids, $owner): array {
            $slot = null;
            if ($data['kind'] === 'direct') {
                $pair = implode(':', $ids);
                DB::table('association_direct_slots')->insertOrIgnore(['pair' => $pair, 'created_at' => now(), 'updated_at' => now()]);
                $slot = DB::table('association_direct_slots')->where('pair', $pair)->lockForUpdate()->first();
                if ($slot->thread_id) {
                    $thread = $this->thread($account, $slot->thread_id);
                    return ['record' => $this->describe($account, $thread)];
                }
            }
            $id = DB::table('association_threads')->insertGetId(['kind' => $data['kind'], 'subject' => $data['subject'], 'owner_account_id' => $owner,
                'created_by' => $account->id, 'status' => 'open', 'revision' => 1, 'created_at' => now(), 'updated_at' => now()]);
            foreach ($ids as $peer) { $this->addMember($id, $peer, $peer === $account->id ? 'manager' : 'member'); }
            if ($slot) { DB::table('association_direct_slots')->where('pair', $slot->pair)->update(['thread_id' => $id, 'updated_at' => now()]); }
            if ($grant) { $this->operations->audit($account, $grant, 'chat', $id, 'create', null, ['members' => $ids, 'owner' => $owner]); }

            return ['record' => $this->describe($account, DB::table('association_threads')->where('id', $id)->first())];
        }, fn (array $result) => $this->thread($account, $result['record']['id']));
    }

    public function contacts(AppAccount $account): array
    {
        $items = [];
        foreach (DB::table('association_chat_grants')->where('beneficiary_account_id', $account->id)->where('active', true)->orderBy('id')->get() as $row) {
            $staff = AppAccount::find($row->staff_account_id);
            if ($staff && $this->access->can($staff, 'chat')) { $items[] = ['id' => $staff->id, 'name' => User::whereKey($staff->user_id)->value('name')]; }
        }

        return ['items' => $items];
    }

    public function ownerStart(AppAccount $account, array $input): array
    {
        $this->portal->person($account);
        $data = Validator::make($input, ['staff_account_id' => 'required|integer|min:1'])->validate();
        $staff = AppAccount::whereKey($data['staff_account_id'])->where('active', true)->firstOrFail();
        $this->access->require($staff, 'chat');
        abort_unless(DB::table('association_chat_grants')->where('beneficiary_account_id', $account->id)->where('staff_account_id', $staff->id)->where('active', true)->exists(), 403, 'beneficiary_chat_not_enabled');
        $ids = [$account->id, $staff->id]; sort($ids, SORT_NUMERIC);

        return $this->createDirectOrGroup($account, null, ['kind' => 'direct', 'subject' => 'التواصل مع الجمعية'], $ids, $account->id, $input);
    }

    public function openSupport(AppAccount $account, array $input): array
    {
        $data = Validator::make($input, ['category_id' => 'required|integer|min:1', 'subject' => 'required|string|max:255'])->validate();
        return $this->operations->run($account, 'support.create', $input, function () use ($account, $data): array {
            AppAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            DB::table('association_support_slots')->insertOrIgnore(['account_id' => $account->id, 'created_at' => now(), 'updated_at' => now()]);
            $slot = DB::table('association_support_slots')->where('account_id', $account->id)->lockForUpdate()->first();
            if ($slot->thread_id) {
                $existing = DB::table('association_threads')->where('id', $slot->thread_id)->first();
                abort_if($existing && $existing->status === 'open', 409, 'one_active_support_ticket_allowed');
            }
            $category = DB::table('association_support_categories')->where('id', $data['category_id'])->where('active', true)->lockForUpdate()->first() ?? abort(404);
            $staff = [];
            foreach (json_decode($category->staff_accounts, true) as $peer) {
                $identity = AppAccount::find($peer);
                if ($identity && $identity->id !== $account->id && $this->access->can($identity, 'support')) { $staff[] = $identity->id; }
            }
            abort_unless($staff, 409, 'support_category_has_no_active_staff');
            $id = DB::table('association_threads')->insertGetId(['kind' => 'support', 'subject' => $data['subject'], 'support_category_id' => $category->id,
                'owner_account_id' => $account->id, 'created_by' => $account->id, 'status' => 'open', 'revision' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $this->addMember($id, $account->id, 'owner');
            foreach ($staff as $peer) { $this->addMember($id, $peer, 'support'); }
            DB::table('association_support_slots')->where('account_id', $account->id)->update(['thread_id' => $id, 'updated_at' => now()]);
            $this->notifications->system($staff, $account->user_id, 'تذكرة دعم جديدة', 'توجد تذكرة جديدة في قسم الدعم الفني.', ['type' => 'thread', 'id' => $id]);

            return ['record' => $this->describe($account, DB::table('association_threads')->where('id', $id)->first())];
        }, fn (array $result) => $this->thread($account, $result['record']['id']));
    }

    public function show(AppAccount $account, int $id, array $filter): array
    {
        $thread = $this->thread($account, $id);
        $query = DB::table('association_thread_messages')->where('thread_id', $id);
        if (isset($filter['before'])) { $query->where('id', '<', $filter['before']); }
        if (isset($filter['after'])) { $query->where('id', '>', $filter['after']); }
        $rows = $query->orderByDesc('id')->limit(51)->get();
        $more = $rows->count() > 50;
        $items = [];
        foreach ($rows->take(50)->reverse() as $row) {
            $items[] = ['id' => $row->id, 'account_id' => $row->account_id, 'client_id' => $row->client_id, 'body' => $row->deleted_at ? null : $row->body,
                'attachments' => $row->deleted_at ? [] : json_decode($row->attachments ?? '[]', true), 'deleted' => $row->deleted_at !== null, 'created_at' => $row->created_at];
        }

        return ['record' => $this->describe($account, $thread), 'messages' => $items, 'has_more' => $more];
    }

    public function send(AppAccount $account, int $id, array $input): array
    {
        $thread = $this->thread($account, $id);
        abort_unless($this->canSend($account, $thread), 403, 'conversation_send_denied');
        $data = Validator::make($input, ['client_id' => 'required|uuid', 'body' => 'nullable|string|max:10000', 'attachments' => 'sometimes|array|max:20', 'attachments.*' => 'string|max:2048'])->validate();
        abort_unless(trim($data['body'] ?? '') !== '' || ! empty($data['attachments']), 422, 'message_is_empty');
        return $this->operations->run($account, 'threads.'.$id.'.send', $input, function () use ($account, $id, $data): array {
            $thread = $this->thread($account, $id, true);
            abort_unless($this->canSend($account, $thread), 403, 'conversation_send_denied');
            $existing = DB::table('association_thread_messages')->where('account_id', $account->id)->where('client_id', $data['client_id'])->first();
            if ($existing) {
                $oldFiles = array_column(json_decode($existing->attachments ?? '[]', true), 'url');
                abort_unless((int) $existing->thread_id === $id && $existing->body === ($data['body'] ?? null) && $oldFiles === ($data['attachments'] ?? []), 409, 'message_client_id_conflict');
                return ['message_id' => $existing->id];
            }
            $module = $this->module($thread);
            $files = [];
            foreach ($data['attachments'] ?? [] as $value) {
                $uploadId = $this->media->id($value);
                $upload = $uploadId ? DB::table('association_uploads')->where('id', $uploadId)->where('module', $module)->where('field', 'attachment')->where('account_id', $account->id)->where('record_id', (string) $id)->first() : null;
                abort_unless($upload && ! str_starts_with($upload->mime, 'audio/'), 422, 'invalid_chat_attachment');
                $files[] = $this->media->describe($upload);
            }
            $message = DB::table('association_thread_messages')->insertGetId(['thread_id' => $id, 'account_id' => $account->id, 'client_id' => $data['client_id'],
                'body' => $data['body'] ?? null, 'attachments' => json_encode($files, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('association_threads')->where('id', $id)->update(['updated_at' => now()]);
            DB::table('association_thread_members')->where('thread_id', $id)->where('account_id', $account->id)->update(['typing_until' => null, 'delivered_through' => $message, 'read_through' => $message, 'updated_at' => now()]);
            $recipients = DB::table('association_thread_members')->where('thread_id', $id)->where('active', true)->where('account_id', '!=', $account->id)->pluck('account_id')->all();
            // Targeted generic inbox entries avoid exposing private message text.
            $this->notifications->system($recipients, $account->user_id, $thread->kind === 'support' ? 'رد جديد على تذكرة الدعم' : 'رسالة جديدة', 'وصلت رسالة جديدة إلى حسابك.', ['type' => 'thread', 'id' => $id]);

            return ['message_id' => $message, 'thread_id' => $id];
        }, fn () => $this->thread($account, $id));
    }

    public function presence(AppAccount $account, int $id, array $input): array
    {
        $thread = $this->thread($account, $id);
        $data = Validator::make($input, ['read_through' => 'sometimes|integer|min:0', 'delivered_through' => 'sometimes|integer|min:0', 'typing' => 'sometimes|boolean'])->validate();
        DB::transaction(function () use ($account, $id, $data): void {
            $thread = $this->thread($account, $id, true);
            $max = (int) DB::table('association_thread_messages')->where('thread_id', $id)->max('id');
            $member = DB::table('association_thread_members')->where('thread_id', $id)->where('account_id', $account->id)->where('active', true)->lockForUpdate()->first() ?? abort(404);
            $read = min($max, max((int) $member->read_through, $data['read_through'] ?? 0));
            $delivered = min($max, max((int) $member->delivered_through, $data['delivered_through'] ?? 0, $read));
            DB::table('association_thread_members')->where('id', $member->id)->update(['read_through' => $read, 'delivered_through' => $delivered,
                'typing_until' => ! empty($data['typing']) && $this->canSend($account, $thread) ? now()->addSeconds(15) : null, 'updated_at' => now()]);
        }, 3);
        app(AssociationDevices::class)->seen($account);

        return ['record' => $this->describe($account, $thread)];
    }

    public function action(AppAccount $account, int $id, array $input): array
    {
        $thread = $this->thread($account, $id);
        $data = Validator::make($input, ['action' => 'required|in:close,reopen,transfer,assign,archive,delete,members', 'reason' => 'required|string|min:3|max:2000',
            'staff_account_id' => 'nullable|integer|min:1', 'members' => 'sometimes|array|max:100', 'members.*' => 'integer|distinct|min:1', 'message_id' => 'nullable|integer|min:1'])->validate();
        abort_if((int) $thread->owner_account_id === $account->id, 403, 'staff_action_required');
        $action = $data['action'];
        $permission = match ($action) { 'reopen' => 'close', 'members' => 'transfer', default => $action };
        if ($thread->kind !== 'support' && $permission === 'assign') { $permission = 'transfer'; }
        $grant = $this->access->require($account, $this->module($thread), $permission);
        abort_if(in_array($action, ['archive', 'delete'], true) && ! $grant->is_superadmin, 403);
        return $this->operations->run($account, 'threads.'.$id.'.'.$action, $input, function () use ($account, $grant, $id, $input, $data, $action): array {
            $thread = $this->thread($account, $id, true);
            $this->operations->expect($thread, $input);
            $values = ['updated_at' => now(), 'revision' => $thread->revision + 1];
            if ($action === 'close') { $values += ['status' => 'closed', 'closed_at' => now()]; }
            if ($action === 'reopen') {
                abort_if($thread->archived_at, 409, 'conversation_archived');
                if ($thread->kind === 'support') {
                    AppAccount::whereKey($thread->owner_account_id)->lockForUpdate()->firstOrFail();
                    $slot = DB::table('association_support_slots')->where('account_id', $thread->owner_account_id)->lockForUpdate()->first();
                    $other = $slot?->thread_id ? DB::table('association_threads')->where('id', $slot->thread_id)->first() : null;
                    abort_if($other && $other->id !== $id && $other->status === 'open', 409, 'one_active_support_ticket_allowed');
                    DB::table('association_support_slots')->where('account_id', $thread->owner_account_id)->update(['thread_id' => $id, 'updated_at' => now()]);
                }
                $values += ['status' => 'open', 'closed_at' => null];
            }
            if ($action === 'archive') { $values += ['status' => 'closed', 'closed_at' => $thread->closed_at ?? now(), 'archived_at' => now()]; }
            if (in_array($action, ['transfer', 'assign'], true)) {
                Validator::make($data, ['staff_account_id' => 'required|integer|min:1'])->validate();
                $peer = AppAccount::whereKey($data['staff_account_id'])->where('active', true)->firstOrFail();
                $peerGrant = $this->access->require($peer, $this->module($thread));
                abort_if($peer->id === (int) $thread->owner_account_id, 422);
                if ($thread->owner_account_id && $thread->kind !== 'support') {
                    $owner = AppAccount::findOrFail($thread->owner_account_id);
                    abort_unless($this->peerAllowed($peer, $owner, $peerGrant), 403, 'transfer_target_outside_scope');
                    DB::table('association_chat_grants')->updateOrInsert(['beneficiary_account_id' => $owner->id, 'staff_account_id' => $peer->id], ['active' => true, 'granted_by' => $grant->staff_id, 'updated_at' => now()]);
                }
                DB::table('association_thread_members')->where('thread_id', $id)->where('account_id', '!=', $thread->owner_account_id ?? 0)->update(['active' => false, 'typing_until' => null, 'updated_at' => now()]);
                $this->addMember($id, $peer->id, 'manager');
                $values['assigned_to'] = $peer->id;
                if ($thread->kind === 'direct') { DB::table('association_direct_slots')->where('thread_id', $id)->delete(); }
            }
            if ($action === 'members') {
                abort_unless($thread->kind === 'group', 422, 'group_action_required');
                $ids = array_values(array_unique([$account->id, ...($data['members'] ?? [])]));
                foreach ($ids as $peerId) { $peer = AppAccount::findOrFail($peerId); $this->access->require($peer, 'chat'); }
                DB::table('association_thread_members')->where('thread_id', $id)->whereNotIn('account_id', $ids)->update(['active' => false, 'typing_until' => null, 'updated_at' => now()]);
                foreach ($ids as $peerId) { $this->addMember($id, $peerId, $peerId === $account->id ? 'manager' : 'member'); }
            }
            if ($action === 'delete') {
                Validator::make($data, ['message_id' => 'required|integer|min:1'])->validate();
                $message = DB::table('association_thread_messages')->where('thread_id', $id)->where('id', $data['message_id'])->first() ?? abort(404);
                DB::table('association_thread_messages')->where('id', $message->id)->update(['deleted_at' => now(), 'deleted_by' => $grant->staff_id, 'updated_at' => now()]);
            }
            DB::table('association_threads')->where('id', $id)->update($values);
            DB::table('association_thread_members')->where('thread_id', $id)->update(['typing_until' => null]);
            $this->operations->audit($account, $grant, $this->module($thread), $id, $action, $thread, ['changes' => $values, 'target' => $data['staff_account_id'] ?? null, 'message_id' => $data['message_id'] ?? null], $data['reason']);

            return ['thread_id' => $id, 'action' => $action];
        });
    }

    public function attachmentContext(AppAccount $account, int $id, string $module): object
    {
        $thread = $this->thread($account, $id);
        abort_unless($module === $this->module($thread) && $this->canSend($account, $thread), 403, 'conversation_send_denied');

        return $thread;
    }

    public function canReadAttachment(AppAccount $account, object $upload): bool
    {
        if (! $this->canRead($account, (int) $upload->record_id)) { return false; }
        $found = false;
        foreach (DB::table('association_thread_messages')->where('thread_id', $upload->record_id)->whereNotNull('attachments')->select('attachments', 'deleted_at')->cursor() as $message) {
            foreach (json_decode($message->attachments, true) ?: [] as $attachment) {
                if (($attachment['id'] ?? null) === $upload->id) {
                    $found = true;
                    if (! $message->deleted_at) { return true; }
                }
            }
        }
        // An uploaded but unsent attachment is visible only to its uploader.
        return ! $found && (int) $upload->account_id === $account->id;
    }
}
