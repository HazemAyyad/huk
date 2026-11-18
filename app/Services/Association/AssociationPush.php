<?php

namespace App\Services\Association;

use App\Models\AppAccount;
use App\Services\Religious\ReligiousPush;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AssociationPush extends ReligiousPush
{
    public function ready(): bool
    {
        $path = realpath((string) config('religious_push.credentials_path'));
        return (bool) config('religious_push.enabled') && preg_match('/^[a-z][a-z0-9-]{4,62}$/', (string) config('religious_push.project')) &&
            $path && is_file($path) && ! str_starts_with(str_replace('\\', '/', $path), str_replace('\\', '/', public_path()).'/') && Schema::hasTable('association_push_attempts');
    }

    private function enabled(object $notification): bool
    {
        if ($notification->status !== 'queued' || $notification->withdrawn_at || ($notification->scheduled_at && \Carbon\CarbonImmutable::parse($notification->scheduled_at)->isFuture())) { return false; }
        $link = json_decode($notification->link ?? '[]', true);
        if (($link['type'] ?? null) === 'event') {
            $event = \App\Models\Event::find($link['id'] ?? 0);
            if (! $event || $event->is_archived || ! app(AssociationEntities::class)->eventEnd($event)->isFuture()) { return false; }
            if (isset($link['invitation_id']) && ($link['notice'] ?? null) !== 'withdrawal' && DB::table('association_invitation_states')->where('message_log_id', $link['invitation_id'])->whereNotNull('withdrawn_at')->exists()) { return false; }
        }
        if (! $notification->created_account_id) { return true; }
        $actor = AppAccount::find($notification->created_account_id);
        return $actor && app(AssociationAccess::class)->can($actor, 'notifications', 'send');
    }

    private function prepare(int $limit): void
    {
        $ids = DB::table('association_notification_recipients')->whereNull('confirmed_at')->whereNotNull('next_attempt_at')->where('next_attempt_at', '<=', now())->orderBy('next_attempt_at')->limit($limit)->pluck('id');
        foreach ($ids as $id) {
            DB::transaction(function () use ($id): void {
                $recipient = DB::table('association_notification_recipients')->where('id', $id)->lockForUpdate()->first();
                if (! $recipient || $recipient->confirmed_at || ! $recipient->next_attempt_at || \Carbon\CarbonImmutable::parse($recipient->next_attempt_at)->isFuture()) { return; }
                $notification = DB::table('association_notifications')->where('id', $recipient->notification_id)->first();
                if (! $notification || ! $this->enabled($notification)) {
                    DB::table('association_notification_recipients')->where('id', $id)->update(['next_attempt_at' => null, 'failure_code' => 'notification_or_sender_inactive', 'updated_at' => now()]);
                    return;
                }
                $reminder = 0;
                $initial = DB::table('association_push_attempts')->where('recipient_id', $id)->where('reminder', 0)->whereIn('status', ['accepted', 'uncertain'])->min('claimed_at');
                if ($recipient->sent_at || $initial) {
                    if (! $notification->requires_confirmation || $recipient->reminders_queued >= $notification->reminder_count) {
                        DB::table('association_notification_recipients')->where('id', $id)->update(['next_attempt_at' => null]);
                        return;
                    }
                    if ($recipient->reminders_queued === 0) {
                        $first = \Carbon\CarbonImmutable::parse($recipient->sent_at ?? $initial)->addMinutes($notification->first_reminder_minutes);
                        if ($first->isFuture()) { DB::table('association_notification_recipients')->where('id', $id)->update(['next_attempt_at' => $first]); return; }
                    }
                    $reminder = $recipient->reminders_queued + 1;
                }
                $devices = DB::table('association_devices')->where('account_id', $recipient->account_id)->whereNull('revoked_at')->whereNotNull('fcm_token')->pluck('id');
                foreach ($devices as $device) {
                    DB::table('association_push_attempts')->insertOrIgnore(['recipient_id' => $id, 'device_id' => $device, 'reminder' => $reminder,
                        'status' => 'pending', 'available_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
                }
                $values = ['next_attempt_at' => now()->addMinutes(5), 'updated_at' => now()];
                if ($reminder && $devices->isNotEmpty()) {
                    $values['reminders_queued'] = $reminder;
                    $values['next_attempt_at'] = $reminder < $notification->reminder_count ? now()->addMinutes($notification->reminder_interval_minutes) : null;
                }
                DB::table('association_notification_recipients')->where('id', $id)->update($values);
            }, 3);
        }
    }

    public function dispatch(int $limit = 20): int
    {
        if (! $this->ready()) { return 0; }
        $limit = max(1, min(100, $limit));
        $this->prepare($limit * 2);
        // A lost transport response may have been accepted. Keep it uncertain;
        // never silently repeat an ambiguous initial push.
        DB::table('association_push_attempts')->where('status', 'sending')->where('claimed_at', '<', now()->subMinutes(5))->update(['status' => 'uncertain', 'failure' => 'interrupted', 'updated_at' => now()]);
        $accepted = 0;
        $accessToken = null;
        for ($i = 0; $i < $limit; $i++) {
            $attempt = DB::transaction(function () {
                $row = DB::table('association_push_attempts')->where('status', 'pending')->where('available_at', '<=', now())->orderBy('id')->lockForUpdate()->first();
                if (! $row) { return null; }
                $row->claim = (string) Str::uuid();
                $row->attempts++;
                DB::table('association_push_attempts')->where('id', $row->id)->update(['status' => 'sending', 'claim' => $row->claim, 'claimed_at' => now(), 'attempts' => $row->attempts, 'updated_at' => now()]);
                return $row;
            }, 3);
            if (! $attempt) { break; }
            $lease = fn () => DB::table('association_push_attempts')->where('id', $attempt->id)->where('status', 'sending')->where('claim', $attempt->claim);
            $recipient = DB::table('association_notification_recipients')->where('id', $attempt->recipient_id)->first();
            $notification = $recipient ? DB::table('association_notifications')->where('id', $recipient->notification_id)->first() : null;
            $device = $recipient ? DB::table('association_devices')->where('id', $attempt->device_id)->where('account_id', $recipient->account_id)->whereNull('revoked_at')->whereNotNull('fcm_token')->first() : null;
            if (! $recipient || ! $notification || ! $device || ! $this->enabled($notification) || ($attempt->reminder > 0 && $recipient->confirmed_at) || ! AppAccount::whereKey($recipient->account_id)->where('active', true)->exists() ||
                DB::table('association_account_policies')->where('account_id', $recipient->account_id)->where('app_enabled', false)->exists()) {
                $lease()->update(['status' => 'cancelled', 'updated_at' => now()]);
                continue;
            }
            try { $fcm = Crypt::decryptString($device->fcm_token); }
            catch (\Throwable) { $lease()->update(['status' => 'failed', 'failure' => 'registration_unavailable', 'updated_at' => now()]); continue; }
            try { $accessToken ??= $this->accessToken(); }
            catch (\Throwable) {
                $lease()->update(['status' => $attempt->attempts < 5 ? 'pending' : 'failed', 'available_at' => now()->addMinutes(5), 'failure' => 'credentials', 'updated_at' => now()]);
                continue;
            }
            // Recheck consent/withdrawal after obtaining credentials, immediately
            // before transport. Push previews deliberately contain no private text.
            $fresh = DB::table('association_notifications')->where('id', $notification->id)->first();
            $confirmed = DB::table('association_notification_recipients')->where('id', $recipient->id)->value('confirmed_at');
            $sameDevice = DB::table('association_devices')->where('id', $device->id)->whereNull('revoked_at')->where('fcm_hash', $device->fcm_hash)->exists();
            if (! $fresh || ! $this->enabled($fresh) || ($attempt->reminder > 0 && $confirmed) || ! $sameDevice) { $lease()->update(['status' => 'cancelled', 'updated_at' => now()]); continue; }
            DB::table('association_notification_recipients')->where('id', $recipient->id)->increment('attempts');
            try {
                $response = Http::withToken($accessToken)->connectTimeout(5)->timeout(15)->post('https://fcm.googleapis.com/v1/projects/'.config('religious_push.project').'/messages:send', [
                    'message' => ['token' => $fcm, 'notification' => ['title' => 'حق', 'body' => $attempt->reminder ? 'تذكير بإشعار يحتاج تأكيدك' : 'وصل إشعار جديد إلى حسابك'],
                        'data' => ['kind' => 'haq.association.v1', 'route' => 'inbox', 'notification_id' => (string) $notification->id, 'account_id' => (string) $recipient->account_id],
                        'android' => ['priority' => 'HIGH', 'ttl' => '86400s', 'notification' => ['channel_id' => 'haq_community_push', 'icon' => 'ic_haq_reminder', 'tag' => 'haq-private-'.$recipient->id]],
                        'apns' => ['headers' => ['apns-push-type' => 'alert', 'apns-priority' => '10', 'apns-collapse-id' => 'haq-private-'.$recipient->id], 'payload' => ['aps' => ['thread-id' => 'haq-private']]],
                    ],
                ]);
                $receipt = $response->json('name');
                if ($response->successful() && is_string($receipt) && str_starts_with($receipt, 'projects/'.config('religious_push.project').'/messages/')) {
                    DB::transaction(function () use ($attempt, $recipient, $notification, $receipt, $lease): void {
                        $lease()->update(['status' => 'accepted', 'provider_receipt' => mb_substr($receipt, 0, 255), 'failure' => null, 'updated_at' => now()]);
                        $row = DB::table('association_notification_recipients')->where('id', $recipient->id)->lockForUpdate()->first();
                        $values = ['updated_at' => now(), 'failure_code' => null];
                        if (! $row->sent_at) {
                            $values['sent_at'] = now(); $values['status'] = 'provider_accepted';
                            $values['next_attempt_at'] = $notification->requires_confirmation && $notification->reminder_count > $row->reminders_queued && ! $row->confirmed_at
                                ? now()->addMinutes($attempt->reminder > 0 ? $notification->reminder_interval_minutes : $notification->first_reminder_minutes) : null;
                        }
                        $values['reminders_sent'] = DB::table('association_push_attempts')->where('recipient_id', $recipient->id)->where('status', 'accepted')->where('reminder', '>', 0)->distinct()->count('reminder');
                        DB::table('association_notification_recipients')->where('id', $recipient->id)->update($values);
                    }, 3);
                    $accepted++;
                } elseif ($response->status() === 429 || $response->serverError()) {
                    $delay = max(60, min(3600, (int) ($response->header('Retry-After') ?? 60 * (2 ** $attempt->attempts))));
                    $lease()->update(['status' => $attempt->attempts < 5 ? 'pending' : 'failed', 'available_at' => now()->addSeconds($delay), 'failure' => 'provider_busy', 'updated_at' => now()]);
                } else {
                    $lease()->update(['status' => $response->successful() ? 'uncertain' : 'failed', 'failure' => 'provider_response', 'updated_at' => now()]);
                    if ($response->json('error.details.0.errorCode') === 'UNREGISTERED') { DB::table('association_devices')->where('id', $device->id)->where('fcm_hash', $device->fcm_hash)->update(['fcm_token' => null, 'fcm_hash' => null, 'updated_at' => now()]); }
                }
            } catch (\Throwable) { $lease()->update(['status' => 'uncertain', 'failure' => 'transport', 'updated_at' => now()]); }
        }

        return $accepted;
    }
}
