<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\MessageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class AssociationInvitations
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records, private AssociationEntities $entities,
        private AssociationAudience $audience, private AssociationOperations $operations, private AssociationNotifications $notifications) {}

    private function event(AppAccount $account, int $id): array
    {
        $grant = $this->access->require($account, 'events', 'invite');
        $event = $this->records->query('events', $grant)->whereKey($id)->firstOrFail();
        abort_if($event->is_archived || ! $this->entities->eventEnd($event)->isFuture(), 409, 'event_not_accepting_invitations');

        return [$grant, $event];
    }

    private function query(object $grant, int $event, array $criteria, string $channel)
    {
        $query = $this->audience->query($criteria, $grant, true)->whereNotIn('telegram_users.id', MessageLog::where('event_id', $event)->where('message_type', 'invitation')->select('telegram_user_id'));
        if ($channel === 'app') {
            $query->whereIn('telegram_users.id', DB::table('app_accounts')->join('users', 'users.id', '=', 'app_accounts.user_id')->where('app_accounts.active', true)
                ->where('app_accounts.association_enabled', true)->whereNull('users.deleted_by')->whereNotIn('app_accounts.id', DB::table('association_account_policies')->where('app_enabled', false)->select('account_id'))->select('users.telegram_user_id'));
        }

        return $query;
    }

    private function fingerprint($query): string
    {
        $hash = hash_init('sha256');
        foreach ((clone $query)->orderBy('telegram_users.id')->select('telegram_users.id', 'telegram_users.updated_at', 'telegram_users.is_blocked', 'telegram_users.blocked_reason')->cursor() as $person) { hash_update($hash, json_encode((array) $person, JSON_THROW_ON_ERROR)."\n"); }
        foreach (DB::table('telegram_user_notes')->whereIn('telegram_user_id', (clone $query)->select('telegram_users.id'))->orderBy('id')->cursor() as $note) { hash_update($hash, json_encode((array) $note, JSON_THROW_ON_ERROR)."\n"); }

        return hash_final($hash);
    }

    private function occupied(int $event): int
    {
        return MessageLog::where('event_id', $event)->where('message_type', 'invitation')->whereIn('delivery_status', ['sent', 'manual'])
            ->whereNotIn('id', DB::table('association_invitation_states')->whereNotNull('withdrawn_at')->select('message_log_id'))->distinct()->count('telegram_user_id');
    }

    public function preview(AppAccount $account, int $id, array $input): array
    {
        [$grant, $event] = $this->event($account, $id);
        $data = Validator::make($input, ['audience' => 'present|array', 'channel' => 'required|in:app,manual'])->validate();
        $criteria = $this->audience->validate($data['audience'], $grant);
        $query = $this->query($grant, $id, $criteria, $data['channel']);
        $count = (clone $query)->count();
        $settings = DB::table('association_event_settings')->where('event_id', $id)->first();
        $occupied = $this->occupied($id);
        $preview = (string) Str::uuid();
        $revision = $this->operations->revision($this->records->revisionData('events', $event));
        DB::table('association_audience_previews')->insert(['id' => $preview, 'account_id' => $account->id, 'purpose' => 'event', 'record_id' => $id,
            'payload' => json_encode(['audience' => $criteria, 'channel' => $data['channel']], JSON_THROW_ON_ERROR), 'revision' => $revision, 'fingerprint' => $this->fingerprint($query), 'recipient_count' => $count,
            'expires_at' => now()->addMinutes(15), 'created_at' => now(), 'updated_at' => now()]);
        $flagged = [];
        if (! empty($criteria['include_flagged_ids'])) { $flagged = $this->access->beneficiaryQuery($grant)->whereIn('telegram_users.id', $criteria['include_flagged_ids'])->get(['telegram_users.id', 'full_name', 'national_id', 'blocked_reason'])->all(); }
        $unlinked = $data['channel'] === 'app' ? $this->query($grant, $id, $criteria, 'manual')->count() - $count : null;

        return ['preview_id' => $preview, 'event' => $this->entities->describe('events', $event), 'recipient_count' => $count, 'occupied' => $occupied,
            'capacity' => $settings?->capacity, 'remaining' => $settings?->capacity === null ? null : max(0, $settings->capacity - $occupied),
            'channel' => $data['channel'], 'not_linked_to_app' => $unlinked, 'included_flagged' => $flagged,
            'sample' => $query->orderBy('telegram_users.id')->limit(30)->get(['telegram_users.id', 'full_name', 'national_id'])->all()];
    }

    public function send(AppAccount $account, int $id, array $input): array
    {
        [$grant] = $this->event($account, $id);
        Validator::make($input, ['preview_id' => 'required|uuid', 'flagged_ack_ids' => 'present|array', 'flagged_ack_ids.*' => 'integer|distinct'])->validate();
        return $this->operations->run($account, 'events.'.$id.'.invite', $input, function () use ($account, $grant, $id, $input): array {
            $event = $this->records->query('events', $grant)->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_if($event->is_archived || ! $this->entities->eventEnd($event)->isFuture(), 409, 'event_not_accepting_invitations');
            $preview = DB::table('association_audience_previews')->where('id', $input['preview_id'])->where('purpose', 'event')->where('record_id', $id)->where('account_id', $account->id)->lockForUpdate()->first() ?? abort(404);
            abort_if($preview->applied_at || \Carbon\CarbonImmutable::parse($preview->expires_at)->isPast(), 409, 'audience_preview_expired');
            abort_unless(hash_equals($preview->revision, $this->operations->revision($this->records->revisionData('events', $event))), 409, 'event_changed');
            $payload = json_decode($preview->payload, true);
            $criteria = $this->audience->validate($payload['audience'], $grant);
            $ack = $input['flagged_ack_ids']; sort($ack, SORT_NUMERIC);
            abort_unless($ack === ($criteria['include_flagged_ids'] ?? []), 422, 'flagged_people_require_individual_acknowledgement');
            $query = $this->query($grant, $id, $criteria, $payload['channel']);
            abort_unless((clone $query)->count() === (int) $preview->recipient_count && $preview->recipient_count > 0 && hash_equals($preview->fingerprint, $this->fingerprint($query)), 409, 'audience_changed');
            $capacity = DB::table('association_event_settings')->where('event_id', $id)->value('capacity');
            abort_if($capacity !== null && $this->occupied($id) + $preview->recipient_count > $capacity, 409, 'event_capacity_reached');
            // Snapshot before inserts: the anti-invitation subquery changes as we
            // write. ID chunking keeps that cursor independent of new rows.
            $ids = (clone $query)->orderBy('telegram_users.id')->pluck('telegram_users.id');
            $notified = 0;
            foreach ($ids as $personId) {
                $person = $this->access->beneficiaryQuery($grant)->where('telegram_users.id', $personId)->lockForUpdate()->first() ?? abort(404);
                $message = str_replace(['{name}', '{full_name}', '{national_id}', '{event}'], [$person->full_name, $person->full_name, $person->national_id, $event->name],
                    strip_tags($event->invitation_message ?: 'ندعوك للمشاركة في '.$event->name));
                $log = MessageLog::create(['telegram_user_id' => $person->id, 'event_id' => $id, 'sent_by' => $grant->staff_id, 'sender_type' => $this->access->staff($grant)->name,
                    'message' => $message, 'message_type' => 'invitation', 'delivery_status' => 'manual', 'sent_at' => now()]);
                DB::table('association_invitation_states')->insert(['message_log_id' => $log->id, 'expires_at' => $this->entities->eventEnd($event), 'created_at' => now(), 'updated_at' => now()]);
                if ($payload['channel'] === 'app') {
                    if ($this->notifications->beneficiary($person->id, $grant->staff_id, 'دعوة من جمعية حق', $message, ['type' => 'event', 'id' => $id, 'invitation_id' => $log->id])) { $notified++; }
                }
            }
            DB::table('association_audience_previews')->where('id', $preview->id)->update(['applied_at' => now(), 'updated_at' => now()]);
            $this->operations->audit($account, $grant, 'events', $id, 'invite', null, ['recipient_count' => $ids->count(), 'inbox_count' => $notified, 'channel' => $payload['channel'], 'audience' => $criteria]);

            return ['event_id' => $id, 'invitations_created' => $ids->count(), 'inbox_entries' => $notified, 'channel' => $payload['channel']];
        }, fn () => $this->records->query('events', $grant)->whereKey($id)->firstOrFail());
    }

    public function withdraw(AppAccount $account, int $id, array $input): array
    {
        $grant = $this->access->require($account, 'invitations', 'update');
        $this->records->query('invitations', $grant)->whereKey($id)->firstOrFail();
        Validator::make($input, ['reason' => 'required|string|min:3|max:2000'])->validate();
        return $this->operations->run($account, 'invitations.'.$id.'.withdraw', $input, function () use ($account, $grant, $id, $input): array {
            $log = $this->records->query('invitations', $grant)->whereKey($id)->firstOrFail();
            \App\Models\TelegramUser::whereKey($log->telegram_user_id)->lockForUpdate()->firstOrFail();
            $event = \App\Models\Event::whereKey($log->event_id)->lockForUpdate()->firstOrFail();
            $log = $this->records->query('invitations', $grant)->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_if($event->is_archived || ! $this->entities->eventEnd($event)->isFuture(), 409, 'event_inactive');
            abort_if($log->attended_at || MessageLog::where('telegram_user_id', $log->telegram_user_id)->where('event_id', $event->id)->where('message_type', 'received')->exists(), 409, 'attended_invitation_cannot_be_withdrawn');
            $before = DB::table('association_invitation_states')->where('message_log_id', $id)->first();
            $this->operations->expect(['log' => $log->getAttributes(), 'state' => (array) $before], $input);
            DB::table('association_invitation_states')->updateOrInsert(['message_log_id' => $id], ['withdrawn_at' => now(), 'updated_at' => now()]);
            $this->notifications->beneficiary($log->telegram_user_id, $grant->staff_id, 'تحديث دعوة فعالية', 'تم سحب الدعوة: '.$input['reason'], ['type' => 'event', 'id' => $event->id, 'invitation_id' => $id, 'notice' => 'withdrawal']);
            $this->operations->audit($account, $grant, 'invitations', $id, 'withdraw', $before, null, $input['reason']);

            return ['invitation_id' => $id, 'withdrawn' => true];
        }, fn () => $this->records->query('invitations', $grant)->whereKey($id)->firstOrFail());
    }
}
