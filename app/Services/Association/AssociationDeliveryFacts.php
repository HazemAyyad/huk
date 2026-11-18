<?php

namespace App\Services\Association;

use App\Models\Event;
use App\Models\MessageLog;
use App\Models\TelegramUser;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Shared by web and mobile. A transport failure cannot erase a physical delivery. */
final class AssociationDeliveryFacts
{
    public function apply(User $staff, int $invitationId, CarbonInterface $time, bool $override = false): array
    {
        return DB::transaction(function () use ($staff, $invitationId, $time, $override): array {
            $source = MessageLog::whereKey($invitationId)->where('message_type', 'invitation')->whereIn('delivery_status', ['manual', 'sent'])->firstOrFail();
            $person = TelegramUser::whereKey($source->telegram_user_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($source->event_id)->lockForUpdate()->firstOrFail();
            $invitation = MessageLog::whereKey($source->id)->lockForUpdate()->firstOrFail();
            $receipt = MessageLog::where('telegram_user_id', $person->id)->where('event_id', $event->id)->where('message_type', 'received')->lockForUpdate()->first();
            if ($receipt) { return ['already_applied' => true, 'receipt' => $receipt]; }
            if (! $invitation->attended_at && ! $override) { throw ValidationException::withMessages(['attendance' => 'يلزم تسجيل الحضور قبل التسليم.']); }
            if (! $invitation->attended_at) {
                $invitation->forceFill(['attended_at' => $time, 'attended_by' => $staff->id, 'excused_at' => null, 'excused_by' => null])->save();
            }
            $receipt = MessageLog::create(['telegram_user_id' => $person->id, 'event_id' => $event->id, 'sent_by' => $staff->id,
                'sender_type' => $staff->name, 'message' => strip_tags($event->default_message ?: 'تم التسليم بنجاح'), 'message_type' => 'received',
                'delivery_status' => 'manual', 'sent_at' => $time, 'is_override' => $override, 'override_by' => $override ? $staff->id : null]);
            $person->increment('benefit_count');

            return ['already_applied' => false, 'receipt' => $receipt];
        }, 3);
    }
}
