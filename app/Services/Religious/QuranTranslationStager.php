<?php

namespace App\Services\Religious;

use App\Services\Quran\QuranCatalog;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Fetches original provider records for review; never publishes or edits scripture. */
final class QuranTranslationStager
{
    public function __construct(private readonly ?string $defaultDirectory = null) {}

    private function fetch(string $path): array
    {
        $response = Http::acceptJson()->connectTimeout(8)->timeout(25)
            ->get('https://quranenc.com/api/v1/'.$path)->throw();
        if (strlen($response->body()) > 4 * 1024 * 1024) {
            throw new RuntimeException('Source response exceeds limit.');
        }

        return ['body' => $response->body(), 'data' => $response->json(), 'sha256' => hash('sha256', $response->body())];
    }

    public function stage(string $locale, string $key, ?string $directory = null): array
    {
        $languages = app(AppLocalization::class)->languages();
        if (! in_array($locale, array_column($languages, 'locale'), true) || $locale === 'ar' || ! preg_match('/^[a-z0-9_]{1,80}$/D', $key)) {
            throw new RuntimeException('Invalid target locale or provider key.');
        }
        $sourceLocale = ['fil' => 'tl', 'zh-Hans' => 'zh', 'ckb' => 'ku'][$locale] ?? $locale;
        $index = $this->fetch('translations/list?localization=en');
        $source = collect($index['data']['translations'] ?? [])->firstWhere('key', $key);
        if (! $source || $source['language_iso_code'] !== $sourceLocale || ! preg_match('/^[0-9.]{1,30}$/D', $source['version'])) {
            throw new RuntimeException('Source language or version does not match the requested locale.');
        }
        $catalog = app(ReligiousCatalog::class)->dataset();
        $verseIndex = array_column(app(QuranCatalog::class)->dataset()['verses'], null, 'key');
        $references = [];
        $surahs = [];
        foreach ($catalog['items'] as $item) {
            if ($item['type'] !== 'quran') {
                continue;
            }
            $references[$item['id']] = $item['verseKeys'];
            foreach ($item['verseKeys'] as $verse) {
                if (! isset($verseIndex[$verse])) {
                    throw new RuntimeException('Unknown local verse identity.');
                }
                $surahs[(int) explode(':', $verse)[0]] = true;
            }
        }
        ksort($surahs);
        $responses = [];
        foreach (array_keys($surahs) as $surah) {
            $response = $this->fetch('translation/sura/'.$key.'/'.$surah);
            $seen = [];
            foreach ($response['data']['result'] ?? [] as $row) {
                $id = $row['sura'].':'.$row['aya'];
                if ((int) $row['sura'] !== $surah || ! isset($verseIndex[$id]) || isset($seen[$id]) ||
                    ! is_string($row['translation'] ?? null) || $row['translation'] === '' || ! is_string($row['footnotes'] ?? null)) {
                    throw new RuntimeException('Invalid or duplicate source verse.');
                }
                $seen[$id] = true;
            }
            $expected = count(array_filter(array_keys($verseIndex), fn ($verse) => str_starts_with($verse, $surah.':')));
            if (count($seen) !== $expected) {
                throw new RuntimeException('Incomplete surah translation.');
            }
            // Keep exact response bytes, footnotes and transcript metadata intact.
            $responses[(string) $surah] = ['sha256' => $response['sha256'], 'raw_json' => $response['body']];
        }
        $latest = $this->fetch('translations/list?localization=en');
        $current = collect($latest['data']['translations'] ?? [])->firstWhere('key', $key);
        if (! $current || $current['version'] !== $source['version'] || $current['last_update'] !== $source['last_update']) {
            throw new RuntimeException('Source version changed during download; no staged package committed.');
        }
        $package = ['format' => 'haq.source.review', 'schema' => 1, 'provider' => 'QuranEnc.com',
            'locale' => $locale, 'source_locale' => $sourceLocale, 'source' => $source,
            'policy_url' => 'https://quranenc.com/en/home', 'attribution' => 'QuranEnc.com — '.$source['title'].' — v'.$source['version'],
            'locale_mapping_review_required' => in_array($locale, ['fil', 'zh-Hans', 'ckb'], true),
            'catalog_version' => $catalog['version'], 'references' => $references, 'surahs' => $responses,
            'reviewed' => false, 'fetched_at' => now()->toIso8601String()];
        $json = json_encode($package, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (strlen($json) > 32 * 1024 * 1024) {
            throw new RuntimeException('Staging package exceeds limit.');
        }
        $directory ??= $this->defaultDirectory ?? storage_path('app/private/haq-source-review');
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Cannot create private source staging directory.');
        }
        $digest = hash('sha256', $json);
        $path = $directory.'/'.$locale.'-'.$key.'-'.$source['version'].'-'.substr($digest, 0, 16).'.json';
        $temporary = $path.'.'.bin2hex(random_bytes(8)).'.tmp';
        if (file_put_contents($temporary, $json, LOCK_EX) !== strlen($json) || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('Cannot commit staged source.');
        }

        return ['path' => $path, 'sha256' => $digest, 'source_version' => $source['version'], 'surahs' => count($responses), 'items' => count($references), 'bytes' => strlen($json), 'reviewed' => false];
    }
}
