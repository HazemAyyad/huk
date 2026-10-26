<?php

namespace App\Services\Religious;

use App\Models\ReligiousAccount;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

final class ReligiousAssistant
{
    public const COUNTED = ['reserved', 'completed', 'uncertain', 'failed_billed'];

    public function ready(): bool
    {
        $key = config('religious_assistant.api_key');

        return config('religious_assistant.enabled') && is_string($key) && trim($key) !== '' && app(ReligiousSubscriptions::class)->schemaReady();
    }

    public function cost(int $input, int $output): int
    {
        return (int) ceil(($input * config('religious_assistant.input_micro_usd_per_million') + $output * config('religious_assistant.output_micro_usd_per_million')) / 1000000);
    }

    public function available(): bool
    {
        if (! $this->ready()) {
            return false;
        }
        $budget = DB::table('religious_assistant_budgets')->where('month', now('UTC')->format('Y-m'))->first();

        return ! $budget || (! $budget->halted && $budget->reserved + $budget->spent + $this->cost(config('religious_assistant.input_limit'), config('religious_assistant.output_limit')) <= config('religious_assistant.budget_micro_usd') - config('religious_assistant.safety_micro_usd'));
    }

    public function quota(int $owner): array
    {
        $query = DB::table('religious_assistant_requests')->where('account_id', $owner)->whereIn('status', self::COUNTED);

        return ['month' => now('UTC')->format('Y-m'), 'day' => now('UTC')->toDateString(),
            'monthly_limit' => config('religious_assistant.monthly_limit'), 'daily_limit' => config('religious_assistant.daily_limit'),
            'monthly_used' => (clone $query)->where('month', now('UTC')->format('Y-m'))->count(), 'daily_used' => (clone $query)->where('day', now('UTC')->toDateString())->count()];
    }

    public function present(object $row): array
    {
        if ($row->status === 'reserved' && \Illuminate\Support\Carbon::parse($row->updated_at)->lt(now()->subMinutes(2))) {
            DB::table('religious_assistant_requests')->where('id', $row->id)->where('status', 'reserved')->update(['status' => 'uncertain', 'error_code' => 'request_interrupted', 'updated_at' => now()]);
            $row = DB::table('religious_assistant_requests')->find($row->id);
        }

        return ['id' => (int) $row->id, 'operation_id' => $row->operation_id, 'status' => $row->status,
            'question' => $row->question === null ? null : Crypt::decryptString($row->question),
            'answer' => $row->answer === null ? null : Crypt::decryptString($row->answer),
            'sources' => json_decode($row->sources, true, 32, JSON_THROW_ON_ERROR), 'error_code' => $row->error_code, 'created_at' => $row->created_at,
            'quota' => $this->quota((int) $row->account_id)];
    }

    public function ask(ReligiousAccount $account, string $operation, string $question): array
    {
        $billing = app(ReligiousSubscriptions::class);
        $owner = $billing->actor($account);
        $query = fn () => DB::table('religious_assistant_requests')->where('account_id', $owner)->where('operation_id', $operation);
        $existing = $query()->first();
        if ($existing) {
            abort_unless(hash_equals($existing->question_hash, hash('sha256', $question)), 409, 'معرّف السؤال مستخدم لنص مختلف');

            return $this->present($existing);
        }
        abort_unless($this->ready(), 503, 'المساعد غير مفعّل حاليًا');
        abort_unless($billing->status($account)['active'], 402, 'المساعد يحتاج اشتراكًا موثقًا وساريًا');
        $sources = app(AssistantSources::class)->find($question);
        abort_unless($sources, 422, 'لم أجد مصدرًا مناسبًا في المكتبة؛ جرّب سؤالًا أوضح عن الأذكار أو الأدعية');
        $provider = app(AssistantProvider::class);
        $payload = $provider->payload($question, $sources);
        $month = now('UTC')->format('Y-m');
        $reserve = $this->cost(config('religious_assistant.input_limit'), config('religious_assistant.output_limit'));
        $created = DB::transaction(function () use ($owner, $query, $operation, $question, $sources, $month, $reserve) {
            abort_unless(DB::table('religious_accounts')->where('id', $owner)->lockForUpdate()->first(), 401);
            if ($old = $query()->first()) {
                return ['row' => $old, 'new' => false];
            }
            $quota = $this->quota($owner);
            abort_if($quota['monthly_used'] >= $quota['monthly_limit'] || $quota['daily_used'] >= $quota['daily_limit'], 429, 'وصلت حد الأسئلة؛ راجع الرصيد وموعد تجديده');
            DB::table('religious_assistant_budgets')->insertOrIgnore(['month' => $month, 'reserved' => 0, 'spent' => 0, 'halted' => false]);
            $budget = DB::table('religious_assistant_budgets')->where('month', $month)->lockForUpdate()->first();
            abort_if($budget->halted || $budget->reserved + $budget->spent + $reserve > config('religious_assistant.budget_micro_usd') - config('religious_assistant.safety_micro_usd'), 503, 'توقفت خدمة المساعد مؤقتًا عند حد الاستهلاك العام');
            DB::table('religious_assistant_budgets')->where('month', $month)->increment('reserved', $reserve);
            $id = DB::table('religious_assistant_requests')->insertGetId(['account_id' => $owner, 'operation_id' => $operation, 'question_hash' => hash('sha256', $question), 'question' => Crypt::encryptString($question),
                'answer' => null, 'sources' => json_encode($sources, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'month' => $month, 'day' => now('UTC')->toDateString(), 'status' => 'reserved', 'reserved' => $reserve, 'created_at' => now(), 'updated_at' => now()]);

            return ['row' => DB::table('religious_assistant_requests')->find($id), 'new' => true];
        }, 3);
        if (! $created['new']) {
            abort_unless(hash_equals($created['row']->question_hash, hash('sha256', $question)), 409);

            return $this->present($created['row']);
        }
        $row = $created['row'];
        try {
            $tokens = $provider->count($payload);
            if ($tokens < 1 || $tokens > config('religious_assistant.input_limit')) {
                throw new \RuntimeException('input_limit');
            }
        } catch (\Throwable) {
            return $this->finish($row, 'failed', 'input_unavailable', null, null);
        }
        try {
            $body = $provider->generate($payload);
        } catch (\Throwable) {
            return $this->finish($row, 'uncertain', 'provider_uncertain', null, null);
        }
        $usage = $body['usage'] ?? [];
        $validUsage = is_int($usage['input_tokens'] ?? null) && is_int($usage['output_tokens'] ?? null) && $usage['input_tokens'] >= 0 && $usage['output_tokens'] >= 0 &&
            $usage['input_tokens'] <= config('religious_assistant.input_limit') && $usage['output_tokens'] <= config('religious_assistant.output_limit') &&
            ($usage['input_tokens_details']['cache_write_tokens'] ?? 0) === 0 && ($usage['input_tokens_details']['cached_tokens'] ?? 0) === 0 &&
            ($body['model'] ?? '') === config('religious_assistant.model');
        if (! $validUsage) {
            DB::table('religious_assistant_budgets')->where('month', $month)->update(['halted' => true]);

            return $this->finish($row, 'uncertain', 'usage_unverified', null, null);
        }
        try {
            if (($body['status'] ?? '') !== 'completed') {
                throw new \RuntimeException('incomplete');
            }
            $text = '';
            foreach ($body['output'] ?? [] as $output) {
                foreach ($output['content'] ?? [] as $part) {
                    if (($part['type'] ?? '') === 'output_text') {
                        $text .= $part['text'];
                    }
                }
            }
            $answer = json_decode($text, true, 16, JSON_THROW_ON_ERROR);
            if (! is_array($answer) || ! is_string($answer['answer'] ?? null) || ! is_bool($answer['supported'] ?? null) || ! is_array($answer['source_ids'] ?? null) || mb_strlen($answer['answer']) > 4000 || trim($answer['answer']) === '' ||
                array_diff($answer['source_ids'], array_column($sources, 'id')) || ($answer['supported'] && ! $answer['source_ids'])) {
                throw new \RuntimeException('invalid_sources');
            }
            $text = $answer['supported'] ? $answer['answer'] : 'المصادر المتاحة لا تكفي لإجابة موثقة عن هذا السؤال. يمكنك مراجعة أهل الاختصاص.';
        } catch (\Throwable) {
            return $this->finish($row, 'failed_billed', 'answer_unverified', $usage, null);
        }

        return $this->finish($row, 'completed', null, $usage, $text);
    }

    private function finish(object $row, string $status, ?string $error, ?array $usage, ?string $answer): array
    {
        DB::transaction(function () use ($row, $status, $error, $usage, $answer): void {
            DB::table('religious_assistant_budgets')->where('month', $row->month)->lockForUpdate()->first();
            $current = DB::table('religious_assistant_requests')->where('id', $row->id)->lockForUpdate()->first();
            // A deleted account cannot resurrect a response; keep the aggregate reservation conservative.
            if (! $current || $current->status !== 'reserved') {
                return;
            }
            $cost = $usage === null ? null : $this->cost($usage['input_tokens'], $usage['output_tokens']);
            if ($status !== 'uncertain') {
                DB::table('religious_assistant_budgets')->where('month', $row->month)->decrement('reserved', $row->reserved);
                if ($cost !== null) {
                    DB::table('religious_assistant_budgets')->where('month', $row->month)->increment('spent', $cost);
                }
            }
            DB::table('religious_assistant_requests')->where('id', $row->id)->update(['status' => $status, 'error_code' => $error, 'cost' => $cost, 'input_tokens' => $usage['input_tokens'] ?? null,
                'output_tokens' => $usage['output_tokens'] ?? null, 'answer' => $current->hidden || $answer === null ? null : Crypt::encryptString($answer), 'updated_at' => now()]);
        }, 3);
        $final = DB::table('religious_assistant_requests')->find($row->id);
        abort_unless($final, 401);

        return $this->present($final);
    }
}
