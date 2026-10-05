<?php

namespace App\Imports;

use App\Models\TelegramUser;
use App\Models\MessageLog;
use App\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;

class BenefitsImport implements ToCollection
{
    protected $eventId;
    protected $message;
    protected $notFound = [];

    public function __construct($eventId, $message)
    {
        $this->eventId = $eventId;
        $this->message = $message;
    }

    public function collection(Collection $rows)
    {
        $authUser = Auth::user();
        $event = Event::find($this->eventId);

        foreach ($rows as $index => $row) {

            // تجاهل أول صف (header)
            if ($index == 0) continue;

            $nationalId = trim($row[0]);

            if (!$nationalId) continue;

            $user = TelegramUser::where('national_id', $nationalId)->first();

            if (!$user) {
                $this->notFound[] = $nationalId;
                continue;
            }

            // تسجيل الاستفادة
            MessageLog::create([
                'telegram_user_id' => $user->id,
                'event_id'         => $this->eventId,
                'sent_by'          => $authUser->id,
                'sender_type'      => $authUser->name,
                'message'          => $this->message,
                'message_type'     => 'received',
                'delivery_status'  => 'manual',
                'sent_at'          => now(),
            ]);

        }
    }

    public function getNotFound()
    {
        return $this->notFound;
    }
}