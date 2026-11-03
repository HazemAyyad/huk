<?php

namespace App\Services\Religious;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Versioned editorial drafts; public clients see only reviewed, published snapshots. */
final class AppManagedContent
{
    public function path(): string
    {
        return config('haq_app.managed_path', storage_path('app/haq-admin/content.json'));
    }

    private function seed(): array
    {
        $seed = json_decode(file_get_contents(resource_path('haq-admin/managed.json')), true, 512, JSON_THROW_ON_ERROR);
        $seed['adhkar'] = json_decode(file_get_contents(resource_path('religious/adhkar.json')), true, 512, JSON_THROW_ON_ERROR);

        return $seed;
    }

    public function state(): array
    {
        $path = $this->path();
        if (! is_file($path)) {
            $seed = $this->seed();

            return ['revision' => 0, 'reviewed_revision' => null, 'draft' => $seed, 'published' => $seed];
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    public function published(): array
    {
        return $this->state()['published'];
    }

    public function change(int $revision, callable $change): array
    {
        $path = $this->path();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }
        $lock = fopen($path.'.lock', 'c');
        abort_unless($lock, 503, 'تعذّر فتح مخزن المحتوى');
        try {
            abort_unless(flock($lock, LOCK_EX), 503, 'تعذّر قفل مخزن المحتوى');
            $state = $this->state();
            abort_unless($state['revision'] === $revision, 409, 'تغيّر المحتوى؛ حدّث الصفحة قبل الحفظ');
            $state = $change($state);
            $temporary = $path.'.'.bin2hex(random_bytes(6)).'.tmp';
            try {
                $encoded = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                abort_unless(file_put_contents($temporary, $encoded, LOCK_EX) === strlen($encoded), 503, 'تعذّر إكمال كتابة المحتوى');
                abort_unless(rename($temporary, $path), 503, 'تعذّر حفظ المحتوى');
            } finally {
                if (is_file($temporary)) {
                    unlink($temporary);
                }
            }

            return $state;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function collection(string $section): array
    {
        return match ($section) {
            'adhkar-items' => ['adhkar', 'items'], 'adhkar-groups' => ['adhkar', 'groups'],
            'sakinah', 'daily', 'audio', 'tafsirs' => [$section],
            default => abort(404),
        };
    }

    public function records(array $document, string $section): array
    {
        $keys = $this->collection($section);

        return count($keys) === 1 ? $document[$keys[0]] : $document[$keys[0]][$keys[1]];
    }

    public function save(string $section, array $record, int $revision): array
    {
        return $this->change($revision, function ($state) use ($section, $record) {
            $keys = $this->collection($section);
            $rows = $this->records($state['draft'], $section);
            $originalPosition = array_search($record['id'] ?? '', array_column($rows, 'id'), true);
            $existing = collect($rows)->firstWhere('id', $record['id'] ?? '');
            $record = $this->validate($section, $record, $state['draft'], $existing);
            $rows = array_values(array_filter($rows, fn ($row) => $row['id'] !== $record['id']));
            $position = isset($record['position']) ? max(0, min(count($rows), (int) $record['position'] - 1)) : ($originalPosition === false ? count($rows) : $originalPosition);
            unset($record['position']);
            array_splice($rows, $position, 0, [$record]);
            if (count($keys) === 1) {
                $state['draft'][$keys[0]] = $rows;
            } else {
                $state['draft'][$keys[0]][$keys[1]] = $rows;
            }
            $state['revision']++;
            if (str_starts_with($section, 'adhkar-')) {
                $state['draft']['adhkar']['version'] = 'admin-'.$state['revision'];
            }
            $state['reviewed_revision'] = null;
            abort_if(strlen(json_encode($state['draft'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) > 2 * 1024 * 1024, 422, 'تجاوز إصدار المحتوى الحجم المدعوم');

            return $state;
        });
    }

    private function validate(string $section, array $input, array $document, ?array $existing): array
    {
        $base = ['id' => ['required', 'string', 'max:96', 'regex:/^[a-z0-9][a-z0-9:_-]*$/D']];
        $rules = match ($section) {
            'tafsirs' => ['id' => ['required', Rule::in(['91', '15', '14', '90'])], 'title' => 'required|string|max:200', 'published' => 'required|boolean'],
            'sakinah' => ['id' => ['required', Rule::in(['calm', 'peace', 'gratitude', 'repentance', 'trust'])],
                'name' => 'required|string|max:100', 'tagline' => 'required|string|max:500', 'dhikr' => 'present|array|max:30', 'dhikr.*' => 'string|max:2000',
                'verses' => 'required|array|min:1|max:30', 'verses.*' => 'string', 'closing_dua' => 'required|string|max:5000', 'closing_source' => 'required|string|max:1500'],
            'daily' => ['date' => 'required|date_format:Y-m-d', 'verse_key' => 'required|string', 'published' => 'required|boolean'],
            'audio' => ['title' => 'required|string|max:200', 'type' => 'required|in:adhan,reminder,recitation,religious',
                'url' => 'required|url:https|max:2000', 'source' => 'required|string|max:500', 'source_url' => 'required|url:https|max:2000', 'published' => 'required|boolean',
                'surahs' => 'sometimes|array|max:114', 'surahs.*' => 'integer|min:1|max:114|distinct'],
            'adhkar-groups' => ['title' => 'required|string|max:200', 'icon' => 'required|string|max:40',
                'position' => 'nullable|integer|min:1|max:1000',
                'item_ids' => 'required|array|min:1|max:1000', 'item_ids.*' => ['required', 'distinct', Rule::in(array_column($document['adhkar']['items'], 'id'))]],
            'adhkar-items' => ['title' => 'required|string|max:200', 'text' => 'required|string|max:30000', 'target' => 'required|integer|min:1|max:1000000',
                'source' => 'required|string|max:1500', 'source_url' => 'required|url:https|max:2000',
                'audio_url' => ['nullable', 'regex:~^https://www\.hisnmuslim\.com/audio/ar/[0-9]+\.mp3$~']],
        };
        $data = Validator::make($input, array_replace($base, $rules))->validate();
        $verses = array_column(app(\App\Services\Quran\QuranCatalog::class)->dataset()['verses'], null, 'key');
        if ($section === 'sakinah') {
            abort_if(array_diff($data['verses'], array_keys($verses)), 422, 'آيات غير معروفة');
            abort_if($data['id'] === 'peace' && $data['dhikr'] !== [], 422, 'مسار الطمأنينة لا يتضمن أذكارًا أثناء التنفس');
        }
        if ($section === 'tafsirs') {
            abort_if($data['id'] === '91' && ! $data['published'], 422, 'يبقى مصدر التفسير الافتراضي متاحًا');
        }
        if ($section === 'daily') {
            abort_unless(isset($verses[$data['verse_key']]), 422, 'اختر آية معروفة');
            abort_if(collect($document['daily'])->contains(fn ($row) => $row['id'] !== $data['id'] && $row['date'] === $data['date']), 422, 'يوجد محتوى لهذا اليوم');
        }
        if ($section === 'audio') {
            $data['surahs'] = array_map('intval', $data['surahs'] ?? []);
            abort_if(parse_url($data['url'], PHP_URL_USER) !== null, 422, 'الرابط لا يقبل بيانات دخول');
            if ($data['type'] === 'recitation') {
                abort_unless(! empty($data['surahs']) && str_ends_with($data['url'], '/'), 422, 'للتلاوة: اختر السور وأدخل رابط مجلد ينتهي بشرطة مائلة، بملفات 001.mp3 وما يليها');
            }
        }
        if ($section === 'adhkar-items') {
            // Preserve source/scripture metadata; text containing scripture must still match its reference.
            $data = array_replace($existing ?? ['scripture' => [], 'haq_content_id' => '', 'chapter_id' => 0], $data);
            foreach ($data['scripture'] as $part) {
                $canonical = implode(' ', array_map(fn ($key) => $verses[$key]['text'] ?? '', $part['keys']));
                abort_unless(str_contains($canonical, $part['text']) && str_contains($data['text'], '﴿'.$part['text'].'﴾'), 422, 'النص القرآني يجب أن يبقى مطابقًا لمرجعه');
            }
        }

        return array_replace($existing ?? [], $data);
    }

    public function review(int $revision): array
    {
        return $this->change($revision, function ($state) {
            $state['reviewed_revision'] = $state['revision'];

            return $state;
        });
    }

    public function publish(int $revision): array
    {
        return $this->change($revision, function ($state) {
            abort_unless($state['reviewed_revision'] === $state['revision'], 422, 'راجع آخر تعديلات المحتوى قبل نشره');
            $state['published'] = $state['draft'];
            $state['published_revision'] = $state['revision'];

            return $state;
        });
    }
}
