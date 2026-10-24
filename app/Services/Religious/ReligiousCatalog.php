<?php

namespace App\Services\Religious;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/** A bundled catalog plus an atomically locked administration copy. No live DB import. */
final class ReligiousCatalog
{
    public const TYPES = ['quran', 'dua', 'hadith', 'sunnah', 'name', 'playlist'];

    public const IMAGES = ['quran', 'prophetic', 'beads', 'kaaba', 'ruqya', 'names', 'hands', 'lantern', 'books', 'daily', 'wash', 'perfume', 'mosque', 'open_quran', 'hall', 'minbar', 'prayer', 'madina'];

    public function __construct(private readonly ?string $overridePath = null) {}

    private function path(): string
    {
        return $this->overridePath ?? storage_path('app/religious/catalog.json');
    }

    public function all(): array
    {
        $directory = dirname($this->path());
        if (! is_dir($directory)) {
            return $this->extendLegacy($this->read(resource_path('religious/catalog.json')));
        }
        $lock = fopen($directory.'/catalog.lock', 'c');
        if (! $lock) {
            throw new RuntimeException('Cannot lock catalog.');
        }
        try {
            flock($lock, LOCK_SH);

            return $this->extendLegacy($this->read(is_file($this->path()) ? $this->path() : resource_path('religious/catalog.json')));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function read(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw new RuntimeException('Religious catalog unavailable.');
        }
        try {
            flock($handle, LOCK_SH);

            return json_decode(stream_get_contents($handle), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function extendLegacy(array $data): array
    {
        if (($data['schema_version'] ?? 1) >= 2) {
            return $data;
        }
        $bundle = $this->read(resource_path('religious/catalog.json'));
        $extensions = $bundle['bundled_extensions'] ?? null;
        if (! $extensions) {
            return $data;
        }
        $categories = array_column($data['categories'], null, 'id');
        foreach ($bundle['categories'] as $category) {
            if (in_array($category['id'], $extensions['category_ids'], true) && ! isset($categories[$category['id']])) {
                $categories[$category['id']] = $category;
            }
        }
        $fresh = array_column($bundle['items'], null, 'id');
        $items = array_column($data['items'], null, 'id');
        foreach ($items as $id => &$record) {
            $added = array_intersect($fresh[$id]['categories'] ?? [], $extensions['category_ids']);
            $record['categories'] = array_values(array_unique([...$record['categories'], ...$added]));
        }
        unset($record);
        foreach ($extensions['item_ids'] as $id) {
            $items[$id] ??= $fresh[$id];
        }
        $result = [...$data, 'schema_version' => 2, 'categories' => array_values($categories), 'items' => array_values($items),
            'moods' => $bundle['moods'], 'ramadan_days' => $bundle['ramadan_days'], 'ramadan_days_note' => $bundle['ramadan_days_note'],
            'bundled_extensions' => $extensions];
        $result['version'] = 'bundle-'.hash('sha256', $bundle['version'].json_encode($data, JSON_THROW_ON_ERROR));

        return $result;
    }

    public function dataset(): array
    {
        $data = $this->all();
        $categories = array_column($data['categories'], null, 'id');
        $visible = function (string $id, array $seen = []) use (&$visible, $categories): bool {
            if (isset($seen[$id]) || ! isset($categories[$id]) || ! ($categories[$id]['published'] ?? false)) {
                return false;
            }
            $seen[$id] = true;

            return empty($categories[$id]['parent']) || $visible($categories[$id]['parent'], $seen);
        };
        $data['categories'] = array_values(array_filter($data['categories'], fn ($c) => $visible($c['id'])));
        $ids = array_column($data['categories'], 'id');
        $data['items'] = array_values(array_filter(array_map(function ($item) use ($ids) {
            $item['categories'] = array_values(array_intersect($item['categories'], $ids));

            return $item;
        }, $data['items']), fn ($i) => ($i['published'] ?? false) && count($i['categories']) > 0));
        $published = array_column($data['items'], 'id');
        if (isset($data['moods'])) {
            $data['moods'] = array_values(array_map(function (array $mood) use ($published): array {
                $mood['item_ids'] = array_values(array_intersect($mood['item_ids'], $published));

                return $mood;
            }, array_filter($data['moods'], fn ($mood) => in_array('mood-'.$mood['id'], $ids, true))));
        }
        if (isset($data['ramadan_days'])) {
            $data['ramadan_days'] = array_values(array_filter($data['ramadan_days'], fn ($day) => in_array($day['item_id'], $published, true)));
        }
        foreach ($data['items'] as &$item) {
            $item['related'] = array_values(array_intersect($item['related'] ?? [], $published));
        }

        return $data;
    }

    public function json(): string
    {
        return json_encode($this->dataset(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public function item(string $id, bool $publishedOnly = true): ?array
    {
        foreach (($publishedOnly ? $this->dataset() : $this->all())['items'] as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    public function saveItem(array $input): void
    {
        $input += ['tags' => [], 'verseKeys' => [], 'related' => [], 'sections' => []];
        $data = Validator::make($input, [
            'id' => ['required', 'regex:/^[a-z0-9][a-z0-9_-]{0,95}$/D'],
            'type' => ['required', Rule::in(self::TYPES)],
            'title' => 'required|string|max:300', 'text' => 'required|string|max:150000',
            'summary' => 'nullable|string|max:2000', 'source' => 'required|string|max:1500',
            'sourceUrl' => ['required', 'url:https', 'max:2000'],
            'image' => ['required', Rule::in(self::IMAGES)],
            'categories' => 'required|array|min:1|max:22', 'categories.*' => 'required|string|distinct',
            'tags' => 'present|array|max:20', 'tags.*' => 'required|string|max:100|distinct',
            'number' => 'nullable|integer|min:0|max:10000',
            'verseKeys' => 'present|array|max:300', 'verseKeys.*' => 'required|string|distinct',
            'audioUrl' => 'nullable|url:https|max:2000', 'reciter' => 'nullable|string|max:200',
            'transliteration' => 'nullable|string|max:200',
            'related' => 'present|array|max:100', 'related.*' => 'required|string|distinct',
            'sections' => 'present|array|max:20', 'sections.*' => 'array:title,text,source',
            'sections.*.title' => 'required|string|max:200', 'sections.*.text' => 'required|string|max:150000',
            'sections.*.source' => 'nullable|string|max:1500', 'published' => 'required|boolean',
        ])->validate();
        $this->mutate(function (array $catalog) use ($data): array {
            $categoryIds = array_column($catalog['categories'], 'id');
            $contentIds = array_column($catalog['items'], 'id');
            $verses = array_column(app(\App\Services\Quran\QuranCatalog::class)->dataset()['verses'], null, 'key');
            if (array_diff($data['categories'], $categoryIds) || array_diff($data['related'], $contentIds) ||
                in_array($data['id'], $data['related'], true) || array_diff($data['verseKeys'], array_keys($verses))) {
                throw ValidationException::withMessages(['categories' => 'تحقق من التصنيفات والمواد والآيات المرتبطة.']);
            }
            if ($data['type'] === 'quran') {
                $expected = implode("\n", array_map(fn ($key) => $verses[$key]['text'], $data['verseKeys']));
                if (empty($data['verseKeys']) || $data['text'] !== $expected) {
                    throw ValidationException::withMessages(['text' => 'النص القرآني يجب أن يطابق الآيات المعتمدة حرفيًا.']);
                }
            }
            if ($data['type'] === 'playlist' && empty($data['related'])) {
                throw ValidationException::withMessages(['related' => 'حدد مقاطع قائمة الاستماع.']);
            }
            foreach ($data['related'] as $id) {
                $related = collect($catalog['items'])->firstWhere('id', $id);
                if ($data['type'] === 'playlist' && ($related['type'] ?? '') === 'playlist') {
                    throw ValidationException::withMessages(['related' => 'أضف مواد صوتية مباشرة إلى القائمة.']);
                }
            }
            foreach ($catalog['items'] as &$item) {
                if ($item['id'] === $data['id']) {
                    $item = $data;

                    return $catalog;
                }
            }
            $catalog['items'][] = $data;

            return $catalog;
        });
    }

    public function saveCategory(array $input): void
    {
        $data = Validator::make($input, [
            'id' => ['required', 'regex:/^[a-z0-9][a-z0-9_-]{0,95}$/D'],
            'title' => 'required|string|max:200', 'description' => 'required|string|max:1000',
            'parent' => 'nullable|string|max:96', 'image' => ['required', Rule::in(self::IMAGES)],
            'header' => ['required', Rule::in(['home', 'ruqya', 'sunnah', 'quran', 'reading', 'medina', 'sleep'])],
            'kind' => ['required', Rule::in(['list', 'categories', 'names', 'ruqya', 'verses'])],
            'published' => 'required|boolean',
            'position' => 'nullable|integer|min:1|max:1000',
        ])->validate();
        $this->mutate(function (array $catalog) use ($data): array {
            $categories = array_column($catalog['categories'], null, 'id');
            $categories[$data['id']] = $data;
            $parent = $data['parent'];
            $seen = [$data['id'] => true];
            while ($parent) {
                if (isset($seen[$parent]) || ! isset($categories[$parent])) {
                    throw ValidationException::withMessages(['parent' => 'التصنيف الأب غير صالح أو يسبب حلقة.']);
                }
                $seen[$parent] = true;
                $parent = $categories[$parent]['parent'] ?? null;
            }
            if (in_array($data['id'], ['quran-duas', 'prophetic-duas', 'sunnah', 'friday', 'nawawi', 'ruqya', 'names'], true) &&
                (! $data['published'] || $data['parent'])) {
                throw ValidationException::withMessages(['published' => 'الأقسام الأساسية السبعة تبقى منشورة في المستوى الأول.']);
            }
            $ordered = array_values(array_filter($categories, fn ($category) => $category['id'] !== $data['id']));
            $position = min(($data['position'] ?? count($ordered) + 1) - 1, count($ordered));
            unset($data['position']);
            array_splice($ordered, $position, 0, [$data]);
            $catalog['categories'] = $ordered;

            return $catalog;
        });
    }

    private function mutate(callable $change): void
    {
        $dir = dirname($this->path());
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('Cannot create catalog directory.');
        }
        $lock = fopen($dir.'/catalog.lock', 'c');
        if (! $lock) {
            throw new RuntimeException('Cannot lock catalog.');
        }
        try {
            flock($lock, LOCK_EX);
            $data = $change($this->extendLegacy($this->read(is_file($this->path()) ? $this->path() : resource_path('religious/catalog.json'))));
            $data['version'] = 'admin-'.hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
            // Write a complete sibling, then replace while readers hold the directory lock.
            $temporary = $dir.'/catalog-'.bin2hex(random_bytes(8)).'.tmp';
            $output = fopen($temporary, 'xb');
            if (! $output) {
                throw new RuntimeException('Cannot save catalog.');
            }
            try {
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
                if (fwrite($output, $json) !== strlen($json)) {
                    throw new RuntimeException('Incomplete catalog write.');
                }
                fflush($output);
            } finally {
                fclose($output);
            }
            if (! rename($temporary, $this->path())) {
                @unlink($temporary);
                throw new RuntimeException('Cannot replace catalog.');
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
