<?php

namespace App\Services\Quran;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class QuranRemoteContent
{
    public const TAFSIRS = [91 => 'تفسير السعدي', 15 => 'تفسير الطبري', 14 => 'تفسير ابن كثير', 90 => 'تفسير القرطبي'];

    private function fetch(string $url, callable $valid): array
    {
        return Cache::store(config('quran.content_cache_store', 'file'))->remember(
            'quran-content-v1:'.hash('sha256', $url), now()->addDay(), function () use ($url, $valid) {
                $response = Http::acceptJson()->connectTimeout(10)->timeout(30)->retry(2, 200, throw: false)->get($url);
                if (! $response->successful() || ! is_array($response->json())) {
                    throw new RuntimeException('Quran source is unavailable.');
                }
                $data = $response->json();
                if (! $valid($data)) {
                    throw new RuntimeException('Quran source reference or completeness mismatch.');
                }

                return $data;
            });
    }

    public function page(int $page): array
    {
        return $this->fetch("https://api.quran.com/api/v4/verses/by_page/{$page}?mushaf=1&words=true&word_fields=code_v2,text_qpc_hafs,page_number,line_number&per_page=50",
            fn (array $data) => ! empty($data['verses']) && empty($data['pagination']['next_page']));
    }

    public function tafsir(int $source, string $key): array
    {
        return $this->fetch("https://api.quran.com/api/v4/tafsirs/{$source}/by_ayah/{$key}",
            fn (array $data) => ($data['tafsir']['resource_id'] ?? null) === $source && isset($data['tafsir']['verses'][$key]) && ! empty($data['tafsir']['text']));
    }

    public function meanings(int $surah, int $ayah): array
    {
        return $this->fetch("https://quranenc.com/api/v1/translation/aya/arabic_seraj/{$surah}/{$ayah}",
            fn (array $data) => (int) ($data['result']['sura'] ?? 0) === $surah && (int) ($data['result']['aya'] ?? 0) === $ayah && isset($data['result']['translation']));
    }

    public function recitations(): array
    {
        return $this->fetch('https://www.mp3quran.net/api/v3/reciters?language=ar&rewaya=1', fn (array $data) => isset($data['reciters']) && is_array($data['reciters']));
    }

    public function words(string $key): array
    {
        return $this->fetch("https://api.quran.com/api/v4/verses/by_key/{$key}?words=true&word_fields=text_qpc_hafs,page_number,line_number,code_v2",
            fn (array $data) => ($data['verse']['verse_key'] ?? null) === $key && ! empty($data['verse']['words']));
    }
}
