<?php

namespace App\Services\Religious;

final class AdhanCatalog
{
    public function dataset(): array
    {
        $path = config('religious_adhans.manifest');
        abort_unless(is_string($path) && is_file($path), 503, 'Adhan catalog unavailable');
        $data = json_decode(file_get_contents($path), true);
        abort_unless(is_array($data) && is_array($data['voices'] ?? null), 503, 'Invalid adhan catalog');

        return $data;
    }

    public function recording(string $id, string $kind): array
    {
        abort_unless(preg_match('/\Anusuk-[1-9][0-9]*\z/', $id) && in_array($kind, ['full', 'takbeer'], true), 404);
        foreach ($this->dataset()['voices'] as $voice) {
            if (($voice['id'] ?? null) === $id) {
                $recording = $voice[$kind] ?? null;
                abort_unless(is_array($recording), 404);

                return $recording;
            }
        }
        abort(404);
    }

    public function file(array $recording): string
    {
        $name = $recording['file'] ?? '';
        abort_unless(is_string($name) && preg_match('/\A(?:athan|takbeer)_[1-9][0-9]*\.mp3\z/', $name), 404);
        $root = realpath(config('religious_adhans.directory'));
        abort_unless($root !== false, 404);
        $path = realpath($root.DIRECTORY_SEPARATOR.$name);
        abort_unless($path !== false && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path), 404);
        abort_unless(filesize($path) === ($recording['bytes'] ?? null)
            && hash_equals((string) ($recording['sha256'] ?? ''), hash_file('sha256', $path)), 503, 'Adhan recording integrity mismatch');

        return $path;
    }
}
