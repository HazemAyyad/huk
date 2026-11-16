<?php

namespace Database\Seeders;

use App\Services\Quran\QuranCatalog;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use RuntimeException;

/** Starter publications for each Haq Messages category. */
final class HaqMessagesSeeder extends Seeder
{
    public function run(ReligiousCatalog $catalog, QuranCatalog $quran): void
    {
        if (! Schema::hasTable('religious_publications')) {
            throw new RuntimeException('Apply the religious community migration before importing Haq messages.');
        }

        $references = json_decode(file_get_contents(resource_path('religious/haq-message-references.json')), true, 512, JSON_THROW_ON_ERROR);
        Validator::make(['messages' => $references], [
            'messages' => 'required|array',
            'messages.*.kind' => 'required|in:quran,hadith,dua,dhikr,tasbih,video,link',
            'messages.*.title' => 'required|string|max:100',
            'messages.*.body' => 'nullable|string|max:2000',
            'messages.*.content_id' => 'required_unless:messages.*.kind,video,link|string|max:120',
            'messages.*.youtube_id' => ['required_if:messages.*.kind,video', 'regex:/^[A-Za-z0-9_-]{11}$/'],
            'messages.*.link' => 'required_if:messages.*.kind,link|url:https|max:2048',
        ])->validate();

        $created = Cache::lock('haq-message-reference-import', 120)->block(10, function () use ($catalog, $quran, $references): int {
            $verses = array_column($quran->dataset()['verses'], 'text', 'key');
            foreach ($references as $reference) {
                if (! isset($reference['item'])) {
                    continue;
                }
                $id = $reference['content_id'];
                $state = $catalog->all();
                if (in_array($id, array_column($state['items'], 'id'), true)) {
                    continue;
                }
                $item = $reference['item'];
                if ($item['type'] === 'quran') {
                    $item['text'] = implode("\n", array_map(fn (string $key): string => $verses[$key], $item['verseKeys']));
                }
                $catalog->saveItem([
                    ...$item, 'id' => $id, 'title' => $reference['title'], 'published' => true,
                ], $state['version']);
            }

            $available = array_column($catalog->dataset()['items'], null, 'id');
            foreach ($references as $reference) {
                $id = $reference['content_id'] ?? null;
                if ($id !== null && (empty($available[$id]['text']) || empty($available[$id]['source']))) {
                    throw new RuntimeException("Published source is unavailable for message {$id}.");
                }
            }

            return DB::transaction(function () use ($references): int {
                $added = 0;
                $now = now();
                foreach ($references as $reference) {
                    $identity = match ($reference['kind']) {
                        'video' => 'youtube_id',
                        'link' => 'link',
                        default => 'content_id',
                    };
                    if (DB::table('religious_publications')->where('channel', 'message')->where($identity, $reference[$identity])->exists()) {
                        continue;
                    }
                    DB::table('religious_publications')->insert([
                        ...Arr::only($reference, ['kind', 'title', 'body', 'content_id', 'youtube_id', 'link']),
                        'channel' => 'message', 'status' => 'published', 'publish_at' => $now,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                    $added++;
                }

                return $added;
            }, 3);
        });

        $this->command?->info("Haq message references imported: {$created}.");
    }
}
