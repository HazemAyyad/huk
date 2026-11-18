<?php

namespace App\Services\Association;

use App\Models\Event;
use App\Models\MessageLog;
use App\Models\TelegramUser;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class AssociationAttendanceFacts
{
    public function apply(int $staff, int $invitationId, CarbonInterface $time): bool
    {
        return DB::transaction(function () use ($staff, $invitationId, $time): bool {
            $source = MessageLog::whereKey($invitationId)->where('message_type', 'invitation')->whereIn('delivery_status', ['sent', 'manual'])->firstOrFail();
            TelegramUser::whereKey($source->telegram_user_id)->lockForUpdate()->firstOrFail();
            Event::whereKey($source->event_id)->lockForUpdate()->firstOrFail();
            $log = MessageLog::whereKey($source->id)->lockForUpdate()->firstOrFail();
            if ($log->attended_at) { return true; }
            $log->forceFill(['attended_at' => $time, 'attended_by' => $staff, 'excused_at' => null, 'excused_by' => null])->save();

            return false;
        }, 3);
    }
}
