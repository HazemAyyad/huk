<?php

namespace App\Services\Religious;

use App\Services\Quran\QuranCatalog;
use RuntimeException;

/** Validate immutable provider data independently of the queue or HTTP transport. */
final class QuranSourceArchive
{
    public static function providerLocale(string $locale): string
    {
        return ['fil' => 'tl', 'zh-Hans' => 'zh', 'ckb' => 'ku'][$locale] ?? $locale;
    }

    public function validate(string $json, string $locale, string $key, string $sha256): array
    {
        if (strlen($json) > 32 * 1024 * 1024 || ! hash_equals($sha256, hash('sha256', $json))) {
            throw new RuntimeException('source_integrity_failed');
        }
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $source = $data['source'] ?? [];
        $language = collect(app(AppLocalization::class)->languages())->firstWhere('locale', $locale);
        if (($data['format'] ?? '') !== 'haq.source.review' || ($data['schema'] ?? 0) !== 1 ||
            ($data['provider'] ?? '') !== 'QuranEnc.com' || ($data['locale'] ?? '') !== $locale ||
            ($source['key'] ?? '') !== $key || ($source['language_iso_code'] ?? '') !== self::providerLocale($locale) ||
            ! preg_match('/^[a-z0-9_]{1,80}$/D', $key) || ! preg_match('/^[0-9.]{1,30}$/D', $source['version'] ?? '') ||
            ! is_string($source['title'] ?? null) || ($source['direction'] ?? '') !== ($language['direction'] ?? null) ||
            ($data['policy_url'] ?? '') !== 'https://quranenc.com/en/home' || ! is_string($data['attribution'] ?? null)) {
            throw new RuntimeException('source_metadata_invalid');
        }
        $index = array_column(app(QuranCatalog::class)->dataset()['verses'], null, 'key');
        $verses = [];
        foreach ($data['surahs'] ?? [] as $surah => $response) {
            $raw = $response['raw_json'] ?? '';
            if (! is_string($raw) || ! hash_equals($response['sha256'] ?? '', hash('sha256', $raw))) {
                throw new RuntimeException('source_response_integrity_failed');
            }
            $rows = json_decode($raw, true, 512, JSON_THROW_ON_ERROR)['result'] ?? [];
            $count = 0;
            foreach ($rows as $row) {
                $id = ($row['sura'] ?? '').':'.($row['aya'] ?? '');
                if ((string) ($row['sura'] ?? '') !== (string) $surah || ! isset($index[$id]) || isset($verses[$id]) ||
                    ! is_string($row['translation'] ?? null) || $row['translation'] === '' || ! is_string($row['footnotes'] ?? null)) {
                    throw new RuntimeException('source_verse_invalid');
                }
                $verses[$id] = $row;
                $count++;
            }
            $expected = count(array_filter(array_keys($index), fn ($verse) => str_starts_with($verse, $surah.':')));
            if ($count === 0 || $count !== $expected) {
                throw new RuntimeException('source_surah_incomplete');
            }
        }
        if (! $verses || ! is_array($data['references'] ?? null) || ! $data['references']) {
            throw new RuntimeException('source_references_missing');
        }
        $items = array_column(app(ReligiousCatalog::class)->dataset()['items'], null, 'id');
        foreach ($data['references'] as $id => $references) {
            if (! is_array($references) || ! $references || ($items[$id]['type'] ?? null) !== 'quran' ||
                $references !== $items[$id]['verseKeys'] || array_diff($references, array_keys($verses))) {
                throw new RuntimeException('source_reference_mismatch');
            }
        }

        return ['source' => $source, 'verses' => $verses, 'references' => $data['references'],
            'surah_count' => count($data['surahs']), 'item_count' => count($data['references']),
            'mapping_review_required' => in_array($locale, ['fil', 'zh-Hans', 'ckb'], true)];
    }
}
