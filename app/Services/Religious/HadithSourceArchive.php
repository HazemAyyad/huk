<?php

namespace App\Services\Religious;

use RuntimeException;

/** Exact, versioned publisher records, separate from editable translations. */
final class HadithSourceArchive
{
    public const COLUMNS = ['id', 'title_ar', 'title', 'hadith_text_ar', 'hadith_text', 'explanation_ar', 'explanation', 'benefits_ar', 'benefits', 'grade_ar', 'takhrij_ar', 'grade', 'takhrij', 'lang', 'link'];

    public static function providerLocale(string $locale): string
    {
        return ['fil' => 'tl', 'zh-Hans' => 'zh', 'ckb' => 'ku'][$locale] ?? $locale;
    }

    public function language(string $locale): array
    {
        $language = collect(app(AppLocalization::class)->languages())->firstWhere('locale', $locale);
        $sourceLocale = self::providerLocale($locale);
        $index = json_decode(file_get_contents(resource_path('haq-app/sources/hadeethenc-languages-20261030.json')), true, 512, JSON_THROW_ON_ERROR);
        if (! $language || $locale === 'ar' || ! collect($index)->contains('code', $sourceLocale)) {
            throw new RuntimeException('hadith_source_locale_unavailable');
        }

        return [...$language, 'source_locale' => $sourceLocale];
    }

    public function references(array $mapping): array
    {
        if (! $mapping || count($mapping) > 100) {
            throw new RuntimeException('hadith_references_invalid');
        }
        $items = array_column(app(ReligiousCatalog::class)->dataset()['items'], null, 'id');
        $references = [];
        foreach ($mapping as $id => $sourceId) {
            if (($items[$id]['type'] ?? '') !== 'hadith' || ! is_string($sourceId) || ! preg_match('/^[1-9][0-9]{0,9}$/D', $sourceId)) {
                throw new RuntimeException('hadith_reference_invalid');
            }
            $references[$id] = ['source_id' => $sourceId, 'original_sha256' => hash('sha256', $items[$id]['text']), 'original_source_url' => $items[$id]['sourceUrl']];
        }

        return $references;
    }

    public function header(string $raw, string $sourceLocale): array
    {
        if (strlen($raw) > 65536 || ! mb_check_encoding($raw, 'UTF-8')) {
            throw new RuntimeException('hadith_header_invalid');
        }
        $handle = fopen('php://temp', 'w+b');
        try {
            fwrite($handle, $raw);
            rewind($handle);
            $document = fgetcsv($handle, 0, ',', '"', '');
            $columns = fgetcsv($handle, 0, ',', '"', '');
            if (! is_array($document) || ! in_array(count($document), [1, count(self::COLUMNS)], true) ||
                array_filter(array_slice($document, 1), fn ($value) => $value !== '') ||
                $columns !== self::COLUMNS || fgetcsv($handle, 0, ',', '"', '') !== false) {
                throw new RuntimeException('hadith_header_invalid');
            }
            $text = $document[0];
            if (! preg_match('/^# Source: https:\/\/hadeethenc\.com\/'.preg_quote($sourceLocale, '/').'\s*$/m', $text) ||
                ! preg_match('/^# Last update: ([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}) \(v([0-9.]{1,30})\)\s*$/m', $text, $version)) {
                throw new RuntimeException('hadith_source_metadata_invalid');
            }
            if (! preg_match('/^# Check for updates: (https:\/\/hadeethenc\.com\/[a-z]{2,3}\/check\/'.preg_quote($sourceLocale, '/').'\/v'.preg_quote($version[2], '/').')\s*$/m', $text, $update)) {
                throw new RuntimeException('hadith_update_metadata_invalid');
            }

            return ['version' => $version[2], 'last_update' => $version[1], 'update_timezone' => 'unspecified', 'update_url' => $update[1], 'document' => $text];
        } finally {
            fclose($handle);
        }
    }

    public function record(string $raw, string $sourceLocale, string $id): array
    {
        if ($raw === '' || strlen($raw) > 1024 * 1024 || ! mb_check_encoding($raw, 'UTF-8')) {
            throw new RuntimeException('hadith_record_invalid');
        }
        $handle = fopen('php://temp', 'w+b');
        try {
            fwrite($handle, $raw);
            rewind($handle);
            $fields = fgetcsv($handle, 0, ',', '"', '');
            if (! is_array($fields) || count($fields) !== count(self::COLUMNS) || fgetcsv($handle, 0, ',', '"', '') !== false || count(array_filter($fields, 'is_string')) !== count($fields)) {
                throw new RuntimeException('hadith_record_invalid');
            }
            $row = array_combine(self::COLUMNS, $fields);
            if ($row['id'] !== $id || $row['lang'] !== $sourceLocale || $row['link'] !== 'https://hadeethenc.com/'.$sourceLocale.'/browse/hadith/'.$id ||
                trim($row['hadith_text']) === '' || trim($row['hadith_text_ar']) === '' || trim($row['title']) === '') {
                throw new RuntimeException('hadith_record_identity_invalid');
            }

            return $row;
        } finally {
            fclose($handle);
        }
    }

    public function validate(string $json, string $locale, string $sha256): array
    {
        if (strlen($json) > 32 * 1024 * 1024 || ! hash_equals($sha256, hash('sha256', $json))) {
            throw new RuntimeException('hadith_source_integrity_failed');
        }
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $language = $this->language($locale);
        $source = $data['source'] ?? [];
        if (($data['format'] ?? '') !== 'haq.hadith.source.review' || ($data['schema'] ?? 0) !== 1 ||
            ($data['provider'] ?? '') !== 'HadeethEnc.com' || ($data['locale'] ?? '') !== $locale ||
            ($data['source_locale'] ?? '') !== $language['source_locale'] || ($data['direction'] ?? '') !== $language['direction'] ||
            ($data['policy_url'] ?? '') !== 'https://hadeethenc.com/en/home' || ! is_string($source['header_csv'] ?? null) ||
            ! preg_match('/^[a-f0-9]{64}$/D', $source['archive_sha256'] ?? '') || ! is_int($source['archive_bytes'] ?? null) ||
            $source['archive_bytes'] < 1 || $source['archive_bytes'] > 32 * 1024 * 1024 || ! is_int($source['archive_records'] ?? null) || $source['archive_records'] < 1) {
            throw new RuntimeException('hadith_source_metadata_invalid');
        }
        $header = $this->header($source['header_csv'], $language['source_locale']);
        foreach ($header as $key => $value) {
            if (($source[$key] ?? null) !== $value) {
                throw new RuntimeException('hadith_source_metadata_mismatch');
            }
        }
        if (($data['attribution'] ?? '') !== 'HadeethEnc.com — v'.$source['version'] || ! is_array($data['references'] ?? null)) {
            throw new RuntimeException('hadith_source_attribution_invalid');
        }
        $mapping = [];
        foreach ($data['references'] as $id => $ref) {
            $mapping[$id] = $ref['source_id'] ?? null;
        }
        $references = $this->references($mapping);
        if ($data['references'] !== $references) {
            throw new RuntimeException('hadith_original_changed');
        }
        $records = [];
        foreach ($data['records'] ?? [] as $id => $entry) {
            $raw = $entry['raw_csv'] ?? '';
            if (! is_string($raw) || ! hash_equals($entry['sha256'] ?? '', hash('sha256', $raw))) {
                throw new RuntimeException('hadith_record_integrity_failed');
            }
            $row = $this->record($raw, $language['source_locale'], (string) $id);
            if (($entry['fields'] ?? null) !== $row) {
                throw new RuntimeException('hadith_record_fields_changed');
            }
            $records[(string) $id] = $row;
        }
        $expected = array_unique(array_values($mapping));
        if (! $records || count($records) !== count($expected) || array_diff($expected, array_keys($records))) {
            throw new RuntimeException('hadith_records_missing');
        }

        return ['source' => $source, 'records' => $records, 'references' => $references,
            'direction' => $language['direction'], 'attribution' => $data['attribution'],
            'mapping_review_required' => in_array($locale, ['fil', 'zh-Hans', 'ckb'], true)];
    }
}
