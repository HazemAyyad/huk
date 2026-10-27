<?php

namespace App\Services\Religious;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReligiousPush
{
    public function ready(): bool
    {
        $path = realpath((string) config('religious_push.credentials_path'));

        return (bool) config('religious_push.enabled') && app(ReligiousCommunity::class)->ready()
            && preg_match('/^[a-z][a-z0-9-]{4,62}$/', (string) config('religious_push.project'))
            && $path && is_file($path) && ! str_starts_with(str_replace('\\', '/', $path), str_replace('\\', '/', public_path()).'/')
            && Schema::hasTable('religious_push_state') && Schema::hasTable('religious_push_outbox');
    }

    protected function accessToken(): string
    {
        $credentials = new ServiceAccountCredentials('https://www.googleapis.com/auth/firebase.messaging',
            json_decode(file_get_contents(config('religious_push.credentials_path')), true, 32, JSON_THROW_ON_ERROR));
        $token = $credentials->fetchAuthToken(fn ($request) => Http::connectTimeout(5)->timeout(12)
            ->withHeaders($request->getHeaders())->send($request->getMethod(), (string) $request->getUri(), ['body' => (string) $request->getBody()])->toPsrResponse());
        if (empty($token['access_token'])) {
            throw new \RuntimeException('push_unavailable');
        }

        return $token['access_token'];
    }

    public function dispatch(int $limit = 20): int
    {
        if (! $this->ready()) {
            return 0;
        }
        // Establish an activation boundary instead of blasting historical posts.
        DB::table('religious_push_state')->insertOrIgnore(['id' => 1, 'activated_at' => now()]);
        $started = DB::table('religious_push_state')->where('id', 1)->value('activated_at');
        $posts = app(ReligiousCommunity::class)->publications()->where('channel', 'message')->where('publish_at', '>=', $started)
            ->whereNotIn('id', DB::table('religious_push_outbox')->select('publication_id'))->orderBy('id')->limit(100)->get(['id']);
        foreach ($posts as $post) {
            DB::table('religious_push_outbox')->insertOrIgnore(['publication_id' => $post->id, 'status' => 'pending',
                'attempts' => 0, 'available_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        }
        // A process can die after FCM accepted the request. Never replay such a
        // lease automatically; transport ambiguity is not proof of non-delivery.
        DB::table('religious_push_outbox')->where('status', 'sending')->where('claimed_at', '<', now()->subMinutes(5))
            ->update(['status' => 'uncertain', 'failure' => 'interrupted', 'updated_at' => now()]);
        $sent = 0;
        for ($i = 0; $i < max(1, min(100, $limit)); $i++) {
            $row = DB::transaction(function () {
                $row = DB::table('religious_push_outbox')->where('status', 'pending')->where('available_at', '<=', now())
                    ->orderBy('id')->lockForUpdate()->first();
                if (! $row) {
                    return null;
                }
                $claim = (string) Str::uuid();
                DB::table('religious_push_outbox')->where('id', $row->id)->update(['status' => 'sending', 'claim' => $claim,
                    'claimed_at' => now(), 'attempts' => $row->attempts + 1, 'updated_at' => now()]);
                $row->claim = $claim;
                $row->attempts++;

                return $row;
            });
            if (! $row) {
                break;
            }
            $lease = fn () => DB::table('religious_push_outbox')->where('id', $row->id)->where('claim', $row->claim)->where('status', 'sending');
            $post = app(ReligiousCommunity::class)->publications()->where('channel', 'message')->where('id', $row->publication_id)->first();
            if (! $post) {
                $lease()->update(['status' => 'skipped', 'failure' => 'not_public', 'updated_at' => now()]);

                continue;
            }
            try {
                $token = $this->accessToken();
            } catch (\Throwable) {
                $lease()->update(['status' => $row->attempts < 5 ? 'pending' : 'failed', 'available_at' => now()->addMinutes(5), 'failure' => 'credentials', 'updated_at' => now()]);

                continue;
            }
            try {
                $ttl = $post->expires_at ? min(86400, max(1, now()->diffInSeconds(\Illuminate\Support\Carbon::parse($post->expires_at), false))) : 86400;
                $response = Http::withToken($token)->connectTimeout(5)->timeout(15)->post(
                    'https://fcm.googleapis.com/v1/projects/'.config('religious_push.project').'/messages:send',
                    ['message' => ['topic' => config('religious_push.topic'),
                        'notification' => ['title' => 'رسائل حق', 'body' => 'وصل محتوى جديد إلى رسائل حق'],
                        'data' => ['kind' => 'haq.publication.v1', 'route' => 'messages', 'publication_id' => (string) $post->id],
                        'android' => ['priority' => 'HIGH', 'ttl' => ((int) $ttl).'s', 'notification' => ['channel_id' => 'haq_community_push', 'icon' => 'ic_haq_reminder', 'tag' => 'haq-publication-'.$post->id]],
                        'apns' => ['headers' => ['apns-push-type' => 'alert', 'apns-priority' => '10', 'apns-collapse-id' => 'haq-publication-'.$post->id,
                            'apns-expiration' => (string) now()->addSeconds((int) $ttl)->timestamp], 'payload' => ['aps' => ['thread-id' => 'haq-publications']]],
                    ]]);
                $receipt = $response->json('name');
                if ($response->successful() && is_string($receipt) && str_starts_with($receipt, 'projects/'.config('religious_push.project').'/messages/')) {
                    $lease()->update(['status' => 'sent', 'provider_receipt' => substr($receipt, 0, 255), 'failure' => null, 'updated_at' => now()]);
                    $sent++;
                } elseif ($response->status() === 429 || $response->serverError()) {
                    $delay = max(60, min(3600, (int) ($response->header('Retry-After') ?? 60 * (2 ** $row->attempts))));
                    $lease()->update(['status' => $row->attempts < 5 ? 'pending' : 'failed', 'available_at' => now()->addSeconds($delay), 'failure' => 'provider_busy', 'updated_at' => now()]);
                } else {
                    $lease()->update(['status' => $response->successful() ? 'uncertain' : 'failed', 'failure' => 'provider_response', 'updated_at' => now()]);
                }
            } catch (\Throwable) {
                $lease()->update(['status' => 'uncertain', 'failure' => 'transport', 'updated_at' => now()]);
            }
        }

        return $sent;
    }
}
