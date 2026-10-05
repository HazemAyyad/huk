<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $chatId;
    private $text;
    private $businessConnectionId;
    private $botToken;

    public function __construct($chatId, $text, $businessConnectionId = null)
    {
        $this->chatId = $chatId;
        $this->text = $text;
        $this->businessConnectionId = $businessConnectionId;
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
    }

    public function handle()
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'parse_mode' => 'Markdown',
        ];

        if ($this->businessConnectionId) {
            $params['business_connection_id'] = $this->businessConnectionId;
        }

        Http::timeout(5)
            ->asJson()
            ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $params);
    }
}