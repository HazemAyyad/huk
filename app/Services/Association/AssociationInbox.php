<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AssociationInbox
{
    private const FIELDS = ['notifications.id', 'notifications.title', 'notifications.body', 'notifications.image', 'notifications.link', 'notifications.status',
        'notifications.requires_confirmation', 'notifications.created_at', 'recipients.sent_at', 'recipients.delivered_at', 'recipients.opened_at', 'recipients.confirmed_at'];
    public function __construct(private AssociationOperations $operations) {}

    private function query(AppAccount $account)
    {
        return DB::table('association_notification_recipients as recipients')->join('association_notifications as notifications', 'notifications.id', '=', 'recipients.notification_id')
            ->where('recipients.account_id', $account->id)->whereIn('notifications.status', ['queued', 'withdrawn'])
            ->where(function ($query): void { $query->whereNull('notifications.scheduled_at')->orWhere('notifications.scheduled_at', '<=', now()); });
    }

    private function describe(object $row): array
    {
        $data = (array) $row;
        $data['link'] = $row->link ? json_decode($row->link, true) : null;
        if ($row->status === 'withdrawn') { $data['body'] = null; $data['image'] = null; $data['link'] = null; }

        return $data;
    }

    public function index(AppAccount $account): array
    {
        $page = $this->query($account)->select(self::FIELDS)->orderByDesc('notifications.id')->paginate(30);

        return ['items' => array_map(fn ($row) => $this->describe($row), $page->items()), 'page' => $page->currentPage(), 'has_more' => $page->hasMorePages(), 'total' => $page->total()];
    }

    public function show(AppAccount $account, int $id): array
    {
        return $this->describe($this->query($account)->where('notifications.id', $id)->select(self::FIELDS)->first() ?? abort(404));
    }

    public function acknowledge(AppAccount $account, int $id, array $input): array
    {
        Validator::make($input, ['action' => 'required|in:delivered,opened,confirmed'])->validate();
        $this->query($account)->where('notifications.id', $id)->first() ?? abort(404);
        return $this->operations->run($account, 'inbox.'.$id.'.'.$input['action'], $input, function () use ($account, $id, $input): array {
            $notification = DB::table('association_notifications')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            $row = DB::table('association_notification_recipients')->where('notification_id', $id)->where('account_id', $account->id)->lockForUpdate()->first() ?? abort(404);
            abort_unless($notification->status === 'queued', 409, 'notification_withdrawn');
            abort_if($input['action'] === 'confirmed' && ! $notification->requires_confirmation, 422, 'confirmation_not_requested');
            $values = ['updated_at' => now()];
            if (! $row->delivered_at) { $values['delivered_at'] = now(); }
            if (in_array($input['action'], ['opened', 'confirmed'], true) && ! $row->opened_at) { $values['opened_at'] = now(); }
            if ($input['action'] === 'confirmed' && ! $row->confirmed_at) {
                $values['confirmed_at'] = now(); $values['next_attempt_at'] = null;
                DB::table('association_push_attempts')->where('recipient_id', $row->id)->where('reminder', '>', 0)->where('status', 'pending')->update(['status' => 'cancelled', 'updated_at' => now()]);
            }
            DB::table('association_notification_recipients')->where('id', $row->id)->update($values);

            return ['id' => $id, 'delivered_at' => $values['delivered_at'] ?? $row->delivered_at,
                'opened_at' => $values['opened_at'] ?? $row->opened_at, 'confirmed_at' => $values['confirmed_at'] ?? $row->confirmed_at];
        }, fn () => $this->query($account)->where('notifications.id', $id)->first() ?? abort(404));
    }

    public function canRead(AppAccount $account, int $id): bool
    {
        return $this->query($account)->where('notifications.id', $id)->where('notifications.status', 'queued')->exists();
    }
}
