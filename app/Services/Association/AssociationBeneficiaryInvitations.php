<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\Event;
use App\Models\MessageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationBeneficiaryInvitations
{
    public function __construct(private AssociationPortal $portal, private AssociationEntities $entities, private AssociationOperations $operations) {}

    private function query(AppAccount $account)
    {
        $person = $this->portal->person($account);
        return MessageLog::where('telegram_user_id', $person->id)->where('message_type', 'invitation')->whereIn('delivery_status', ['sent', 'manual']);
    }

    private function describe(MessageLog $log): array
    {
        $event = Event::find($log->event_id);
        $state = DB::table('association_invitation_states')->where('message_log_id', $log->id)->first();
        $settings = $event ? DB::table('association_event_settings')->where('event_id', $event->id)->first() : null;
        $receipt = MessageLog::where('telegram_user_id', $log->telegram_user_id)->where('event_id', $log->event_id)->where('message_type', 'received')->first();
        $available = $event && ! $event->is_archived && $this->entities->eventEnd($event)->isFuture() && ! $state?->withdrawn_at;

        return ['id' => $log->id, 'message' => $log->message, 'event' => $event ? ['id' => $event->id, 'name' => $event->name, 'date' => $event->event_date, 'time' => $event->event_time,
            'starts_at' => $this->entities->eventStart($event)->toIso8601String(), 'ends_at' => $this->entities->eventEnd($event)->toIso8601String(), 'location' => $event->location,
            'active' => $this->entities->eventActive($event), 'archived' => (bool) $event->is_archived] : null,
            'state' => $state ? (array) $state : null, 'attended_at' => $log->attended_at,
            'receipt' => $receipt ? ['id' => $receipt->id, 'message' => $receipt->message, 'received_at' => $receipt->sent_at] : null,
            'can_confirm' => $available, 'can_respond' => $available && (! $settings?->reply_deadline || \Carbon\CarbonImmutable::parse($settings->reply_deadline)->isFuture()),
            'reply_deadline' => $settings?->reply_deadline, 'can_rate' => $event && ($log->attended_at || $receipt) && ! $state?->withdrawn_at,
            '_revision' => $this->operations->revision(['log' => $log->getAttributes(), 'state' => (array) $state])];
    }

    public function index(AppAccount $account): array
    {
        $page = $this->query($account)->orderByDesc('id')->paginate(30);
        return ['items' => array_map(fn ($log) => $this->describe($log), $page->items()), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => $page->total()];
    }

    public function show(AppAccount $account, int $id): array
    {
        return $this->describe($this->query($account)->whereKey($id)->firstOrFail());
    }

    public function action(AppAccount $account, int $id, array $input): array
    {
        $this->query($account)->whereKey($id)->firstOrFail();
        $data = Validator::make($input, ['action' => 'required|in:opened,confirmed,will_attend,declined,rate', 'rating' => 'nullable|integer|between:1,5', 'comment' => 'nullable|string|max:5000'])->validate();
        return $this->operations->run($account, 'invitations.'.$id.'.'.$data['action'], $input, function () use ($account, $id, $input, $data): array {
            $log = $this->query($account)->whereKey($id)->firstOrFail();
            \App\Models\TelegramUser::whereKey($log->telegram_user_id)->lockForUpdate()->firstOrFail();
            Event::whereKey($log->event_id)->lockForUpdate()->firstOrFail();
            $log = $this->query($account)->whereKey($id)->lockForUpdate()->firstOrFail();
            DB::table('association_invitation_states')->insertOrIgnore(['message_log_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
            $state = DB::table('association_invitation_states')->where('message_log_id', $id)->lockForUpdate()->first();
            $description = $this->describe($log);
            $values = ['updated_at' => now()];
            if ($data['action'] === 'opened') { if (! $state->opened_at) { $values['opened_at'] = now(); } }
            elseif ($data['action'] === 'confirmed') {
                abort_unless($description['can_confirm'], 409, 'invitation_inactive');
                if (! $state->receipt_confirmed_at) { $values['receipt_confirmed_at'] = now(); }
                if (! $state->opened_at) { $values['opened_at'] = now(); }
            } elseif (in_array($data['action'], ['will_attend', 'declined'], true)) {
                abort_unless($description['can_respond'] && ! $log->attended_at && ! $description['receipt'], 409, 'invitation_reply_closed');
                // A reply never marks actual attendance or creates a replacement.
                $values += ['response' => $data['action'], 'responded_at' => now()];
            } else {
                abort_unless($description['can_rate'], 409, 'event_rating_requires_attendance');
                Validator::make($data, ['rating' => 'required|integer|between:1,5'])->validate();
                $values += ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null];
            }
            DB::table('association_invitation_states')->where('message_log_id', $id)->update($values);
            $this->operations->audit($account, (object) ['staff_id' => null], 'invitations', $id, $data['action'], null, $values);

            return ['record' => $this->describe($log)];
        }, fn () => $this->query($account)->whereKey($id)->firstOrFail());
    }
}
