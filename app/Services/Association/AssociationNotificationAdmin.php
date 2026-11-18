<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class AssociationNotificationAdmin
{
    public function __construct(private AssociationAccess $access, private AssociationOperations $operations, private AssociationNotificationAudience $audience, private AssociationMedia $media) {}

    public function query(object $grant)
    {
        $query = DB::table('association_notifications');
        if (! $this->access->global($grant)) { $query->where('created_by', $grant->staff_id); }

        return $query;
    }

    public function describe(object $row): array
    {
        $data = (array) $row;
        foreach (['link', 'audience'] as $key) { $data[$key] = $data[$key] ? json_decode($data[$key], true) : []; }
        $data['_label'] = $row->title;
        $data['_revision'] = $this->operations->revision($row);

        return $data;
    }

    public function metadata(object $grant): array
    {
        return ['title' => 'الإشعارات الموجهة', 'fields' => [], 'can_create' => $this->access->allows($grant, 'notifications', 'create'), 'can_edit' => $this->access->allows($grant, 'notifications', 'update'),
            'actions' => array_values(array_filter(AssociationModules::definition('notifications')['actions'], fn ($action) => $this->access->allows($grant, 'notifications', $action)))];
    }

    public function index(AppAccount $account, array $filter): array
    {
        $grant = $this->access->require($account, 'notifications');
        $query = $this->query($grant);
        if (! empty($filter['q'])) { $query->where('title', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $filter['q']).'%'); }
        if (! empty($filter['status'])) { $query->where('status', $filter['status']); }
        $page = $query->orderByDesc('id')->paginate(30);

        return [...$this->metadata($grant), 'items' => array_map(fn ($row) => $this->describe($row), $page->items()), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => $page->total()];
    }

    public function show(AppAccount $account, int $id): array
    {
        $grant = $this->access->require($account, 'notifications');
        $row = $this->query($grant)->where('id', $id)->first() ?? abort(404);
        $recipients = DB::table('association_notification_recipients')->where('notification_id', $id);
        $stats = ['total' => (clone $recipients)->count()];
        foreach (['sent_at' => 'provider_accepted', 'delivered_at' => 'device_delivered', 'opened_at' => 'opened', 'confirmed_at' => 'confirmed'] as $column => $key) { $stats[$key] = (clone $recipients)->whereNotNull($column)->count(); }
        $stats['failed_attempts'] = DB::table('association_push_attempts')->whereIn('recipient_id', (clone $recipients)->select('id'))->where('status', 'failed')->count();

        return [...$this->metadata($grant), 'record' => $this->describe($row), 'stats' => $stats];
    }

    public function save(AppAccount $account, array $input, ?int $id = null): array
    {
        $grant = $this->access->require($account, 'notifications', $id ? 'update' : 'create');
        return $this->operations->run($account, 'notifications.'.($id ?? 'new').'.save', $input, function () use ($account, $grant, $input, $id): array {
            $old = $id ? $this->query($grant)->where('id', $id)->lockForUpdate()->first() ?? abort(404) : null;
            if ($old) { $this->operations->expect($old, $input); abort_unless($old->status === 'draft', 409, 'notification_already_queued'); }
            $data = Validator::make($input['record'] ?? [], ['title' => 'required|string|max:255', 'body' => 'required|string|max:10000',
                'image' => 'nullable|string|max:2048', 'link' => 'nullable|array', 'link.type' => 'required_with:link|in:event,form,submission,receipt,thread,home', 'link.id' => 'nullable|integer|min:1',
                'audience' => 'required|array', 'scheduled_at' => 'nullable|date', 'requires_confirmation' => 'required|boolean',
                'first_reminder_minutes' => 'nullable|integer|min:1|max:525600', 'reminder_interval_minutes' => 'nullable|integer|min:1|max:525600', 'reminder_count' => 'required|integer|min:0|max:100'])->validate();
            if ($data['reminder_count'] > 0) {
                abort_unless($data['requires_confirmation'] && ! empty($data['first_reminder_minutes']) && ! empty($data['reminder_interval_minutes']), 422, 'reminder_configuration_required');
            }
            $data['audience'] = json_encode($this->audience->validate($data['audience'], $grant), JSON_THROW_ON_ERROR);
            $data['image'] = $this->media->value($account, 'notifications', 'image', $data['image'] ?? null, $old->image ?? null, $old ? (string) $old->id : null);
            if ($data['image']) {
                $mime = DB::table('association_uploads')->where('id', $this->media->id($data['image']))->value('mime');
                abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true), 422, 'image_attachment_required');
            }
            $data['link'] = empty($data['link']) ? null : json_encode(array_intersect_key($data['link'], array_flip(['type', 'id'])), JSON_THROW_ON_ERROR);
            $data['scheduled_at'] = empty($data['scheduled_at']) ? null : CarbonImmutable::parse($data['scheduled_at'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $data['revision'] = ($old->revision ?? 0) + 1;
            $data['updated_at'] = now();
            if ($id) { DB::table('association_notifications')->where('id', $id)->update($data); }
            else { $id = DB::table('association_notifications')->insertGetId([...$data, 'created_by' => $grant->staff_id, 'created_account_id' => $account->id, 'status' => 'draft', 'created_at' => now()]); }
            $this->media->bind($account, 'notifications', (string) $id, ['image' => $data['image']]);
            $row = DB::table('association_notifications')->where('id', $id)->first();
            $this->operations->audit($account, $grant, 'notifications', $id, $old ? 'update' : 'create', $old, $row);

            return ['record' => $this->describe($row)];
        }, fn (array $result) => $this->query($grant)->where('id', $result['record']['id'])->first() ?? abort(404));
    }

    public function preview(AppAccount $account, int $id): array
    {
        $grant = $this->access->require($account, 'notifications', 'send');
        $row = $this->query($grant)->where('id', $id)->first() ?? abort(404);
        abort_unless($row->status === 'draft', 409);
        $target = $this->audience->validate(json_decode($row->audience, true), $grant);
        $query = $this->audience->query($target, $grant);
        $preview = (string) Str::uuid();
        $count = (clone $query)->count();
        DB::table('association_audience_previews')->insert(['id' => $preview, 'account_id' => $account->id, 'purpose' => 'notification', 'record_id' => $id,
            'revision' => $this->operations->revision($row), 'fingerprint' => $this->audience->fingerprint($target, $grant), 'recipient_count' => $count,
            'expires_at' => now()->addMinutes(15), 'created_at' => now(), 'updated_at' => now()]);

        return ['preview_id' => $preview, 'record' => $this->describe($row), 'recipient_count' => $count,
            'flagged' => $this->audience->flagged($target, $grant), 'included_flagged_ids' => $target['filter']['include_flagged_ids'] ?? [],
            'sample' => $query->orderBy('app_accounts.id')->limit(20)->get(['app_accounts.id', 'users.name', 'telegram_users.full_name'])->all()];
    }

    public function action(AppAccount $account, int $id, array $input): array
    {
        Validator::make($input, ['action' => 'required|in:send,withdraw'])->validate();
        $action = $input['action'];
        $grant = $this->access->require($account, 'notifications', $action);
        $this->query($grant)->where('id', $id)->first() ?? abort(404);
        return $this->operations->run($account, 'notifications.'.$id.'.'.$action, $input, function () use ($account, $grant, $id, $input, $action): array {
            $old = $this->query($grant)->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            $this->operations->expect($old, $input);
            if ($action === 'withdraw') {
                Validator::make($input, ['reason' => 'required|string|min:3|max:2000'])->validate();
                DB::table('association_notifications')->where('id', $id)->update(['status' => 'withdrawn', 'withdrawn_at' => now(), 'revision' => $old->revision + 1, 'updated_at' => now()]);
                DB::table('association_notification_recipients')->where('notification_id', $id)->update(['next_attempt_at' => null, 'updated_at' => now()]);
                DB::table('association_push_attempts')->whereIn('recipient_id', DB::table('association_notification_recipients')->where('notification_id', $id)->select('id'))->where('status', 'pending')->update(['status' => 'cancelled', 'updated_at' => now()]);
            } else {
                Validator::make($input, ['preview_id' => 'required|uuid', 'flagged_ack_ids' => 'present|array', 'flagged_ack_ids.*' => 'integer|distinct'])->validate();
                abort_unless($old->status === 'draft', 409, 'notification_already_queued');
                $preview = DB::table('association_audience_previews')->where('id', $input['preview_id'])->where('account_id', $account->id)->where('purpose', 'notification')->where('record_id', $id)->lockForUpdate()->first() ?? abort(404);
                abort_if($preview->applied_at || CarbonImmutable::parse($preview->expires_at)->isPast() || ! hash_equals($preview->revision, $this->operations->revision($old)), 409, 'audience_preview_expired');
                $target = $this->audience->validate(json_decode($old->audience, true), $grant);
                $expected = $target['filter']['include_flagged_ids'] ?? []; $ack = $input['flagged_ack_ids']; sort($ack, SORT_NUMERIC);
                abort_unless($expected === $ack, 422, 'flagged_people_require_individual_acknowledgement');
                abort_unless(hash_equals($preview->fingerprint, $this->audience->fingerprint($target, $grant)), 409, 'audience_changed');
                $query = $this->audience->query($target, $grant);
                abort_unless($query->count() === (int) $preview->recipient_count && $preview->recipient_count > 0, 409, 'audience_changed');
                $rows = [];
                foreach ($query->orderBy('app_accounts.id')->select('app_accounts.id', 'users.telegram_user_id')->cursor() as $recipient) {
                    $rows[] = ['notification_id' => $id, 'account_id' => $recipient->id, 'beneficiary_id' => $recipient->telegram_user_id, 'status' => 'pending', 'next_attempt_at' => $old->scheduled_at ?? now(), 'created_at' => now(), 'updated_at' => now()];
                    if (count($rows) === 200) { DB::table('association_notification_recipients')->insert($rows); $rows = []; }
                }
                if ($rows) { DB::table('association_notification_recipients')->insert($rows); }
                DB::table('association_audience_previews')->where('id', $preview->id)->update(['applied_at' => now(), 'updated_at' => now()]);
                DB::table('association_notifications')->where('id', $id)->update(['status' => 'queued', 'revision' => $old->revision + 1, 'updated_at' => now()]);
            }
            $row = DB::table('association_notifications')->where('id', $id)->first();
            $this->operations->audit($account, $grant, 'notifications', $id, $action, $old, $row, $input['reason'] ?? null);

            return ['record' => $this->describe($row)];
        }, fn () => $this->query($grant)->where('id', $id)->first() ?? abort(404));
    }
}
