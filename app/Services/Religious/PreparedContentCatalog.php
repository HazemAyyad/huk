<?php

namespace App\Services\Religious;

use App\Services\Quran\QuranCatalog;
use RuntimeException;

/** Immutable publisher snapshots shipped with code, never editable drafts. */
final class PreparedContentCatalog
{
    public function __construct(private readonly ?string $directory = null) {}

    private function directory(): string
    {
        return $this->directory ?? resource_path('haq-app/content');
    }

    public function catalog(): array
    {
        $index = json_decode(file_get_contents($this->directory().'/index.json'), true, 512, JSON_THROW_ON_ERROR);
        if (($index['schema'] ?? null) !== 1 || array_keys($index['locales'] ?? []) !== ['en', 'tr']) {
            throw new RuntimeException('prepared_catalog_invalid');
        }

        return $index;
    }

    public function metadata(string $locale, string $kind): array
    {
        $row = $this->catalog()['locales'][$locale][$kind] ?? null;
        if (! is_array($row) || ! in_array($locale, ['en', 'tr'], true) ||
            ! in_array($kind, ['quran', 'tafsir', 'dhikr', 'names', 'hadith'], true) ||
            ($row['file'] ?? null) !== $locale.'-'.$kind.'.json.gz' ||
            ! preg_match('/^[a-f0-9]{64}$/D', $row['sha256'] ?? '') ||
            ! preg_match('/^[a-f0-9]{64}$/D', $row['json_sha256'] ?? '') ||
            ! is_int($row['bytes'] ?? null) || $row['bytes'] < 1 || $row['bytes'] > 8 * 1024 * 1024 ||
            ! is_int($row['json_bytes'] ?? null) || $row['json_bytes'] < 1 || $row['json_bytes'] > 32 * 1024 * 1024) {
            throw new RuntimeException('prepared_source_invalid');
        }

        return $row;
    }

    public function bytes(string $locale, string $kind): string
    {
        $meta = $this->metadata($locale, $kind);
        $bytes = file_get_contents($this->directory().'/'.$meta['file']);
        if (strlen($bytes) !== $meta['bytes'] || ! hash_equals($meta['sha256'], hash('sha256', $bytes))) {
            throw new RuntimeException('prepared_archive_integrity_failed');
        }

        return $bytes;
    }

    public function verify(string $locale, string $kind): array
    {
        $meta = $this->metadata($locale, $kind);
        $raw = gzdecode($this->bytes($locale, $kind), 32 * 1024 * 1024);
        if (! is_string($raw) || strlen($raw) !== $meta['json_bytes'] || ! hash_equals($meta['json_sha256'], hash('sha256', $raw))) {
            throw new RuntimeException('prepared_text_integrity_failed');
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (($data['format'] ?? '') !== 'haq.bundled-source' || ($data['schema'] ?? 0) !== 1 ||
            ($data['locale'] ?? '') !== $locale || ($data['kind'] ?? '') !== $kind ||
            count($data['verses'] ?? $data['entries'] ?? []) !== $meta['records']) {
            throw new RuntimeException('prepared_source_metadata_invalid');
        }
        if (in_array($kind, ['quran', 'tafsir'], true)) {
            $keys = array_column(app(QuranCatalog::class)->dataset()['verses'], 'key');
            if (array_diff($keys, array_keys($data['verses'])) || count($data['verses']) !== 6236) {
                throw new RuntimeException('prepared_quran_coverage_invalid');
            }
            foreach ($data['verses'] as $row) {
                if ($kind === 'quran' && (! is_string($row['text'] ?? null) || $row['text'] === '')) {
                    throw new RuntimeException('prepared_verse_empty');
                }
                if ($kind === 'tafsir') {
                    if (! $row) {
                        throw new RuntimeException('prepared_tafsir_empty');
                    }
                    foreach ($row as $id) {
                        if (! is_string($data['blocks'][$id]['text'] ?? null) || $data['blocks'][$id]['text'] === '') {
                            throw new RuntimeException('prepared_tafsir_reference_invalid');
                        }
                    }
                }
            }
        } else {
            $catalog = $kind === 'dhikr'
                ? json_decode(file_get_contents(resource_path('religious/adhkar.json')), true, 512, JSON_THROW_ON_ERROR)
                : app(ReligiousCatalog::class)->dataset();
            $originals = array_column($catalog['items'], null, 'id');
            foreach ($data['entries'] as $id => $row) {
                if (! isset($originals[$id]) || ! hash_equals(hash('sha256', $originals[$id]['text']), $row['original_sha256'] ?? '') ||
                    ! is_string($row['text'] ?? null) || trim($row['text']) === '') {
                    throw new RuntimeException('prepared_original_changed');
                }
                if ($kind === 'hadith' && isset($row['fields'])) {
                    $fields = $row['fields'] ?? [];
                    $reference = 'https://hadeethenc.com/'.$locale.'/browse/hadith/'.$row['source_id'];
                    if (($fields['link'] ?? '') !== $reference || ($fields['lang'] ?? '') !== $locale ||
                        ($fields['hadith_text'] ?? null) !== $row['text'] || ($fields['hadith_text_ar'] ?? null) !== $row['source_arabic'] ||
                        array_diff(HadithSourceArchive::COLUMNS, array_keys($fields))) {
                        throw new RuntimeException('prepared_hadith_record_invalid');
                    }
                }
                if ($kind === 'hadith' && ! isset($row['fields'])) {
                    $provider = $row['source']['provider'] ?? '';
                    $host = parse_url($row['source']['url'] ?? '', PHP_URL_HOST);
                    if ($provider !== 'IslamHouse.com' || ! in_array($host, ['islamhouse.com', 'd1.islamhouse.com', 'riyadh.islamenc.com'], true) ||
                        ! preg_match('/^[a-f0-9]{64}$/D', $row['source']['artifact_sha256'] ?? '')) {
                        throw new RuntimeException('prepared_supplement_source_invalid');
                    }
                }
            }
            foreach ($data['additional_content'] ?? [] as $id => $row) {
                $original = collect(app(ReligiousCatalog::class)->dataset()['items'])->firstWhere('id', $id);
                if (! $original || ! hash_equals(hash('sha256', $original['text']), $row['original_sha256'] ?? '') ||
                    ! is_string($row['text'] ?? null) || trim($row['text']) === '' || ($row['source']['provider'] ?? '') !== 'IslamHouse.com') {
                    throw new RuntimeException('prepared_additional_source_invalid');
                }
            }
        }

        return $data;
    }
}
