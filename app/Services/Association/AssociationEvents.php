<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Models\Event;
use App\Models\MessageLog;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AssociationEvents
{
    public function __construct(private AssociationAccess $access, private AssociationRecords $records,
        private AssociationOperations $operations, private AssociationEntities $entities,
        private AssociationIdentities $identities, private AssociationOfflinePermits $permits,
        private AssociationNotifications $notifications) {}

    private function module(string $mode): string
    {
        abort_unless(in_array($mode, ['attendance', 'delivery'], true), 404);

        return $mode === 'delivery' ? 'deliveries' : 'attendance';
    }

    public function scan(AppAccount $account, string $mode, array $input): array
    {
        $module = $this->module($mode);
        $grant = $this->access->require($account, $module, 'scan');
        Validator::make($input, ['code' => 'required|string|max:4000', 'event_id' => 'nullable|integer|min:1'])->validate();
        $history = $this->history($account, $grant, $mode, $input['code']);
        $identity = $this->identities->normalize(trim($input['code']));
        if (str_starts_with($identity, '{')) {
            $decoded = json_decode($identity, true);
            $identity = is_array($decoded) && is_string($decoded['national_id'] ?? null) ? $this->identities->normalize($decoded['national_id']) : '';
        }
        if (! preg_match('/^[0-9]{1,100}$/D', $identity)) {
            $this->scanResult($history, 'invalid');

            return ['scan_id' => $history, 'result' => 'invalid', 'events' => []];
        }
        $beneficiary = $this->access->beneficiaryQuery($grant)->where('national_id', $identity)->first();
        if (! $beneficiary) {
            $this->scanResult($history, 'not_found');

            return ['scan_id' => $history, 'result' => 'not_found', 'events' => []];
        }
        $logs = MessageLog::with('event')->where('telegram_user_id', $beneficiary->id)->where('message_type', 'invitation')
            ->whereIn('delivery_status', ['sent', 'manual'])->when(isset($input['event_id']), fn ($query) => $query->where('event_id', $input['event_id']))->orderByDesc('id')->get();
        $events = [];
        foreach ($logs->unique('event_id') as $invitation) {
            if (! $invitation->event || $this->withdrawn($invitation->id)) {
                continue;
            }
            $events[] = $this->invitation($account, $grant, $invitation);
        }
        usort($events, fn ($a, $b) => strcmp($a['event']['event_date'].' '.$a['event']['event_time'], $b['event']['event_date'].' '.$b['event']['event_time']));
        $active = array_values(array_filter($events, fn ($row) => $row['event']['active']));
        $today = array_values(array_filter($active, fn ($row) => $row['event']['event_date'] === now()->toDateString()));
        $upcoming = array_values(array_filter($events, fn ($row) => $row['event']['event_date'] > now()->toDateString()));
        $result = $active ? 'ready' : ($events ? 'inactive_event' : 'no_invitation');
        DB::table('association_scan_history')->where('id', $history)->update(['beneficiary_id' => $beneficiary->id,
            'event_id' => count($events) === 1 ? $events[0]['event']['id'] : null, 'result' => $result]);

        return ['scan_id' => $history, 'result' => $result, 'beneficiary' => $this->person($beneficiary),
            'events' => $active, 'has_event_today' => count($today) > 0, 'nearest_event' => $upcoming[0] ?? null,
            'inactive_events' => array_values(array_filter($events, fn ($row) => ! $row['event']['active']))];
    }

    public function participants(AppAccount $account, int $event, string $mode, array $filter): array
    {
        $module = $this->module($mode);
        $grant = $this->access->require($account, $module);
        $this->records->query('events', $grant)->whereKey($event)->firstOrFail();
        $query = $this->records->query($module, $grant, ['event_id' => $event]);
        if (! empty($filter['q'])) {
            $people = $this->records->query('beneficiaries', $grant, ['q' => $filter['q']])->select('telegram_users.id');
            $query->whereIn('telegram_user_id', $people);
        }
        $page = $query->with(['event', 'user'])->orderBy('id')->paginate(30);

        return ['items' => array_map(fn ($log) => [...$this->invitation($account, $grant, $log), 'beneficiary' => $this->person($log->user)], $page->items()),
            'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => $page->total()];
    }

    private function person(object $person): array
    {
        return ['id' => $person->id, 'full_name' => $person->full_name, 'national_id' => $person->national_id,
            'access_code' => $person->access_code ?? null, 'identity_hash' => hash('sha256', $person->id.'|'.$person->full_name.'|'.$person->national_id)];
    }

    private function invitation(AppAccount $account, object $grant, MessageLog $log): array
    {
        $event = $log->event;
        $receipt = MessageLog::where('telegram_user_id', $log->telegram_user_id)->where('event_id', $event->id)->where('message_type', 'received')->first();
        $state = DB::table('association_invitation_states')->where('message_log_id', $log->id)->first();
        $active = $this->entities->eventActive($event);

        return ['id' => $log->id, 'attended_at' => $log->attended_at, 'delivered' => $receipt !== null,
            'receipt_id' => $receipt?->id, 'response' => $state?->response, 'event' => [
                'id' => $event->id, 'name' => $event->name, 'event_date' => $event->event_date, 'event_time' => $event->event_time,
                'location' => $event->location, 'active' => $active, 'ends_at' => $this->entities->eventEnd($event)->toIso8601String(),
                'starts_at' => $this->entities->eventStart($event)->toIso8601String(),
                'timezone' => config('app.timezone')], 'offline_permit' => $this->permits->issue($account, $grant, $event),
            'can_attend' => $active && $this->access->allows($grant, 'attendance', 'confirm'),
            'can_deliver' => $active && $this->access->allows($grant, 'deliveries', 'confirm'),
            'can_override' => $active && $this->access->allows($grant, 'deliveries', 'override')];
    }

    public function confirm(AppAccount $account, string $mode, array $input): array
    {
        $module = $this->module($mode);
        $grant = $this->access->require($account, $module, 'confirm');
        Validator::make($input, ['operation_id' => 'required|uuid', 'invitation_id' => 'required|integer|min:1',
            'scan_id' => 'nullable|integer|min:1', 'identity_hash' => 'nullable|string|size:64', 'override' => 'sometimes|boolean',
            'reason' => 'nullable|string|max:2000', 'occurred_at' => 'nullable|date', 'offline_permit' => 'nullable|string|max:8000',
            'send_notification' => 'sometimes|boolean'])->validate();
        if (! empty($input['override'])) {
            $this->access->require($account, 'deliveries', 'override');
            Validator::make($input, ['reason' => 'required|string|min:3|max:2000'])->validate();
        }
        $history = isset($input['scan_id']) ? DB::table('association_scan_history')->where('id', $input['scan_id'])->where('account_id', $account->id)->where('mode', $mode)->value('id')
            : $this->history($account, $grant, $mode, 'invitation:'.$input['invitation_id'], $input['operation_id']);
        abort_unless($history, 404);
        try {
            $log = $this->records->query($module, $grant)->whereKey($input['invitation_id'])->firstOrFail();
            $result = $this->operations->run($account, "$module.confirm", $input, function () use ($account, $grant, $module, $mode, $input, $log, $history): array {
                $person = TelegramUser::whereKey($log->telegram_user_id)->lockForUpdate()->firstOrFail();
                $event = Event::whereKey($log->event_id)->lockForUpdate()->firstOrFail();
                $invitation = $this->records->query($module, $grant)->whereKey($log->id)->lockForUpdate()->firstOrFail();
                $scan = DB::table('association_scan_history')->where('id', $history)->first();
                abort_if($scan->beneficiary_id !== null && (int) $scan->beneficiary_id !== $person->id, 409, 'scan_person_changed');
                abort_if($scan->event_id !== null && (int) $scan->event_id !== $event->id, 409, 'scan_event_changed');
                if (! empty($input['identity_hash']) && ! hash_equals($this->person($person)['identity_hash'], $input['identity_hash'])) {
                    abort(409, 'beneficiary_identity_changed');
                }
                abort_if($this->withdrawn($invitation->id), 409, 'invitation_withdrawn');
                $time = $this->permits->time($account, $event, $module, $input);
                $before = $invitation->getAttributes();
                $already = false;
                $receipt = null;
                $notification = null;
                if ($mode === 'attendance') {
                    $already = app(AssociationAttendanceFacts::class)->apply($grant->staff_id, $invitation->id, $time);
                    $invitation->refresh();
                } else {
                    $fact = app(AssociationDeliveryFacts::class)->apply($this->access->staff($grant), $invitation->id, $time, ! empty($input['override']));
                    $receipt = $fact['receipt'];
                    $already = $fact['already_applied'];
                    $invitation->refresh();
                    if (! $already) {
                        if ($input['send_notification'] ?? true) {
                            $notification = $this->notifications->beneficiary($person->id, $grant->staff_id, 'تأكيد استلام المساعدة', $receipt->message,
                                ['type' => 'receipt', 'id' => $receipt->id, 'event_id' => $event->id]);
                        }
                    }
                }
                $this->operations->audit($account, $grant, $module, $invitation->id, $already ? 'duplicate' : 'confirm', $before,
                    ['invitation' => $invitation->fresh()->getAttributes(), 'receipt_id' => $receipt?->id, 'offline' => ! empty($input['occurred_at'])], $input['reason'] ?? null);

                return ['invitation_id' => $invitation->id, 'event_id' => $event->id, 'beneficiary_id' => $person->id,
                    'already_applied' => $already, 'receipt_id' => $receipt?->id, 'notification_id' => $notification,
                    'attended_at' => $invitation->attended_at, 'delivered' => $mode === 'delivery' ? true : MessageLog::where('telegram_user_id', $person->id)->where('event_id', $event->id)->where('message_type', 'received')->exists()];
            });
            DB::table('association_scan_history')->where('id', $history)->update(['beneficiary_id' => $result['beneficiary_id'], 'event_id' => $result['event_id'],
                'result' => $result['already_applied'] ? 'duplicate' : 'confirmed']);

            return $result;
        } catch (\Throwable $exception) {
            $this->scanResult($history, $exception instanceof ValidationException ? 'validation_failed' : 'rejected');
            throw $exception;
        }
    }

    public function events(AppAccount $account, string $mode): array
    {
        $grant = $this->access->require($account, $this->module($mode));
        $items = [];
        foreach ($this->records->query('events', $grant)->where('is_archived', false)->orderBy('event_date')->cursor() as $event) {
            if (! $this->entities->eventEnd($event)->isFuture()) { continue; }
            $items[] = ['id' => $event->id, 'name' => $event->name, 'active' => $this->entities->eventActive($event),
                'starts_at' => $this->entities->eventStart($event)->toIso8601String(), 'ends_at' => $this->entities->eventEnd($event)->toIso8601String()];
        }

        return ['items' => $items];
    }

    public function offlineScan(AppAccount $account, string $mode, array $input): array
    {
        $grant = $this->access->require($account, $this->module($mode), 'scan');
        $data = Validator::make($input, ['operation_id' => 'required|uuid', 'input_hash' => 'required|string|regex:/^[a-f0-9]{64}$/D',
            'result' => 'required|in:ready,invalid,not_cached,inactive_event', 'beneficiary_id' => 'nullable|integer|min:1', 'event_id' => 'nullable|integer|min:1',
            'occurred_at' => 'required|date|before_or_equal:'.now()->addMinute()->toIso8601String()])->validate();
        if (isset($data['beneficiary_id'])) { $this->access->beneficiary($grant, $data['beneficiary_id']); }
        if (isset($data['event_id'])) { $this->records->query('events', $grant)->whereKey($data['event_id'])->firstOrFail(); }

        return $this->operations->run($account, 'scan.offline.'.$mode, $input, function () use ($account, $grant, $mode, $data): array {
            $history = DB::table('association_scan_history')->insertGetId(['account_id' => $account->id, 'staff_id' => $grant->staff_id,
                'mode' => $mode, 'input_hash' => $data['input_hash'], 'operation_id' => $data['operation_id'], 'beneficiary_id' => $data['beneficiary_id'] ?? null,
                'event_id' => $data['event_id'] ?? null, 'result' => 'offline_'.$data['result'], 'created_at' => now()]);
            $this->operations->audit($account, $grant, 'scan-history', $history, 'offline_scan', null, ['client_occurred_at' => $data['occurred_at'], 'result' => $data['result']]);

            return ['scan_id' => $history];
        }, function () use ($grant, $data): void { if (isset($data['beneficiary_id'])) { $this->access->beneficiary($grant, $data['beneficiary_id']); } });
    }

    private function withdrawn(int $invitation): bool
    {
        return DB::table('association_invitation_states')->where('message_log_id', $invitation)->whereNotNull('withdrawn_at')->exists();
    }

    private function history(AppAccount $account, object $grant, string $mode, string $input, ?string $operation = null): int
    {
        if ($operation) {
            $existing = DB::table('association_scan_history')->where('account_id', $account->id)->where('operation_id', $operation)->first();
            if ($existing) {
                abort_unless($existing->mode === $mode && hash_equals($existing->input_hash, hash('sha256', $input)), 409, 'scan_operation_conflict');

                return $existing->id;
            }
        }

        return DB::table('association_scan_history')->insertGetId(['account_id' => $account->id, 'staff_id' => $grant->staff_id,
            'mode' => $mode, 'input_hash' => hash('sha256', $input), 'result' => 'resolved', 'operation_id' => $operation, 'created_at' => now()]);
    }

    private function scanResult(int $id, string $result): void
    {
        DB::table('association_scan_history')->where('id', $id)->update(['result' => $result]);
    }
}
