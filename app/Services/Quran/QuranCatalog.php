<?php

namespace App\Services\Quran;

use InvalidArgumentException;
use RuntimeException;

/** The same versioned, verbatim Tanzil dataset is bundled in Flutter. */
final class QuranCatalog
{
    private ?array $data = null;

    public function __construct(private readonly ?string $path = null) {}

    public function path(): string
    {
        return $this->path ?? base_path('resources/quran/quran.json');
    }

    public function dataset(): array
    {
        if ($this->data === null) {
            $json = file_get_contents($this->path());
            if ($json === false) {
                throw new RuntimeException('Quran dataset is unavailable.');
            }
            $this->data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->data;
    }

    public function manifest(): array
    {
        $data = $this->dataset();

        return [
            'version' => $data['version'],
            'source' => $data['source'],
            'source_url' => $data['sourceUrl'],
            'text_license' => $data['textLicense'],
            'sha256' => hash_file('sha256', $this->path()),
            'surah_count' => count($data['surahs']),
            'verse_count' => count($data['verses']),
            'juz_count' => count($data['juzs']),
            'page_count' => 604,
            'capabilities' => [
                'text' => true, 'search' => true, 'offline_text' => true,
                'tafsir' => true, 'word_meanings' => true,
                'audio' => true, 'account_sync' => true,
                'fixed_page_layout' => true,
            ],
        ];
    }

    public function surahs(): array
    {
        return $this->dataset()['surahs'];
    }

    public function juzs(): array
    {
        return $this->dataset()['juzs'];
    }

    public function surah(int $id): array
    {
        if ($id < 1 || $id > 114) {
            throw new InvalidArgumentException('Invalid surah.');
        }

        return $this->surahs()[$id - 1];
    }

    public function verses(?int $surah = null, ?int $juz = null, ?int $page = null): array
    {
        return array_values(array_filter($this->dataset()['verses'],
            static fn (array $verse): bool => ($surah === null || $verse['surah'] === $surah) &&
                ($juz === null || $verse['juz'] === $juz) &&
                ($page === null || $verse['page'] === $page)));
    }

    public static function normalize(string $text): string
    {
        $text = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $text);
        $text = strtr($text, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ى' => 'ي']);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public function search(string $query, ?int $surah = null, ?int $juz = null): array
    {
        $needle = self::normalize($query);
        if ($needle === '') {
            return [];
        }

        return array_values(array_filter($this->verses($surah, $juz),
            static fn (array $verse): bool => str_contains(self::normalize($verse['searchText'] ?? $verse['text']), $needle)));
    }
}
