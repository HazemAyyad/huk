<?php

namespace App\Services\Religious;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Only explicit local-ID -> publisher-ID mappings are staged, never guessed. */
final class HadithTranslationStager
{
    public function __construct(private readonly HadithSourceArchive $archive, private readonly ?string $defaultDirectory = null) {}

    private function directory(?string $directory): string
    {
        $directory ??= $this->defaultDirectory ?? storage_path('app/private/haq-source-review');
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('hadith_staging_unavailable');
        }

        return $directory;
    }

    public function stage(string $locale, array $mapping, ?string $directory = null): array
    {
        $language = $this->archive->language($locale);
        $this->archive->references($mapping);
        $directory = $this->directory($directory);
        $temporary = $directory.'/hadith-download-'.bin2hex(random_bytes(12)).'.tmp';
        $output = fopen($temporary, 'x+b');
        if (! $output) {
            throw new RuntimeException('hadith_staging_unavailable');
        }
        $body = null;
        try {
            // Fixed host/path and bounded streaming; the caller cannot supply a URL.
            $sourceLocale = $language['source_locale'];
            $response = Http::connectTimeout(8)->timeout(90)->withOptions(['stream' => true, 'allow_redirects' => false])
                ->get('https://hadeethenc.com/en/browse/download/'.$sourceLocale.'/csv')->throw();
            if ($response->status() !== 200) {
                throw new RuntimeException('hadith_download_failed');
            }
            $body = $response->toPsrResponse()->getBody();
            $size = 0;
            while (! $body->eof()) {
                $chunk = $body->read(65536);
                if ($chunk === '' && $body->eof()) {
                    break;
                }
                $size += strlen($chunk);
                if ($chunk === '' || $size > 32 * 1024 * 1024 || fwrite($output, $chunk) !== strlen($chunk)) {
                    throw new RuntimeException('hadith_download_invalid');
                }
            }
            fclose($output);
            $output = null;
            $result = $this->stageFromFile($locale, $mapping, $temporary, $directory);
            $destination = $directory.'/hadeethenc-'.$sourceLocale.'-'.$result['source_version'].'-'.substr(hash_file('sha256', $temporary), 0, 16).'.csv';
            if (! rename($temporary, $destination)) {
                throw new RuntimeException('hadith_archive_commit_failed');
            }

            return $result;
        } finally {
            $body?->close();
            if (is_resource($output)) {
                fclose($output);
            }
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    public function stageFromFile(string $locale, array $mapping, string $path, ?string $directory = null): array
    {
        $language = $this->archive->language($locale);
        $references = $this->archive->references($mapping);
        if (! is_file($path) || filesize($path) < 1 || filesize($path) > 32 * 1024 * 1024) {
            throw new RuntimeException('hadith_archive_size_invalid');
        }
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw new RuntimeException('hadith_archive_unavailable');
        }
        try {
            if (! flock($handle, LOCK_SH)) {
                throw new RuntimeException('hadith_archive_unavailable');
            }
            $hash = hash_init('sha256');
            hash_update_stream($hash, $handle);
            $archiveSha = hash_final($hash);
            $archiveBytes = ftell($handle);
            rewind($handle);
            fgetcsv($handle, 0, ',', '"', '');
            fgetcsv($handle, 0, ',', '"', '');
            $end = ftell($handle);
            rewind($handle);
            $rawHeader = fread($handle, $end);
            $metadata = $this->archive->header($rawHeader, $language['source_locale']);
            $wanted = array_fill_keys(array_values($mapping), true);
            $seen = $records = [];
            $count = 0;
            while (true) {
                $start = ftell($handle);
                $fields = fgetcsv($handle, 0, ',', '"', '');
                if ($fields === false) {
                    break;
                }
                $end = ftell($handle);
                if (count($fields) !== count(HadithSourceArchive::COLUMNS) || ! preg_match('/^[1-9][0-9]{0,9}$/D', $fields[0] ?? '') ||
                    isset($seen[$fields[0]]) || $fields[13] !== $language['source_locale'] || $end - $start > 1024 * 1024) {
                    throw new RuntimeException('hadith_archive_record_invalid');
                }
                $seen[$fields[0]] = true;
                $count++;
                if (isset($wanted[$fields[0]])) {
                    fseek($handle, $start);
                    $raw = fread($handle, $end - $start);
                    $row = $this->archive->record($raw, $language['source_locale'], $fields[0]);
                    $records[$fields[0]] = ['raw_csv' => $raw, 'sha256' => hash('sha256', $raw), 'fields' => $row];
                }
            }
            if (count($records) !== count($wanted)) {
                throw new RuntimeException('hadith_mapped_record_missing');
            }
            rewind($handle);
            $hash = hash_init('sha256');
            hash_update_stream($hash, $handle);
            if (! hash_equals($archiveSha, hash_final($hash)) || ftell($handle) !== $archiveBytes) {
                throw new RuntimeException('hadith_archive_changed');
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        $source = [...$metadata, 'header_csv' => $rawHeader, 'archive_sha256' => $archiveSha,
            'archive_bytes' => $archiveBytes, 'archive_records' => $count];
        $data = ['format' => 'haq.hadith.source.review', 'schema' => 1, 'provider' => 'HadeethEnc.com', 'locale' => $locale,
            'source_locale' => $language['source_locale'], 'direction' => $language['direction'], 'source' => $source,
            'attribution' => 'HadeethEnc.com — v'.$source['version'], 'policy_url' => 'https://hadeethenc.com/en/home',
            'references' => $references, 'records' => $records, 'reviewed' => false, 'fetched_at' => now()->toIso8601String()];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $sha256 = hash('sha256', $json);
        $this->archive->validate($json, $locale, $sha256);
        $directory = $this->directory($directory);
        $destination = $directory.'/'.$locale.'-hadeethenc-'.$source['version'].'-'.substr($sha256, 0, 16).'.json';
        $temporary = $destination.'.'.bin2hex(random_bytes(8)).'.tmp';
        if (file_put_contents($temporary, $json, LOCK_EX) !== strlen($json) || ! rename($temporary, $destination)) {
            @unlink($temporary);
            throw new RuntimeException('hadith_artifact_commit_failed');
        }

        return ['path' => $destination, 'sha256' => $sha256, 'source_version' => $source['version'], 'items' => count($references),
            'records' => count($records), 'bytes' => strlen($json), 'reviewed' => false];
    }
}
