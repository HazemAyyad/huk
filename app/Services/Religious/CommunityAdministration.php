<?php

namespace App\Services\Religious;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Shared mutations for web and verified in-app administration. */
final class CommunityAdministration
{
    public function __construct(private readonly ReligiousCommunity $community) {}

    public function circle(Request $request)
    {
        $data = $request->validate(['id' => 'nullable|integer|min:1', 'title' => 'required|string|max:100', 'content_id' => 'required|string|max:120', 'goal' => 'required|integer|min:1|max:1000000000000', 'published' => 'required|boolean']);
        $this->community->content($data['content_id']);
        DB::transaction(function () use ($data): void {
            if (! empty($data['id'])) {
                $row = DB::table('religious_circles')->where('id', $data['id'])->lockForUpdate()->first();
                abort_unless($row, 404);
                $sum = (int) DB::table('religious_circle_counts')->where('circle_id', $row->id)->sum('amount');
                if ($sum > 0 && $row->content_id !== $data['content_id']) {
                    throw ValidationException::withMessages(['content_id' => 'لا يمكن تبديل ذكر حلقة لها مساهمات؛ أنشئ حلقة جديدة']);
                }
                if ($sum > (int) $data['goal']) {
                    throw ValidationException::withMessages(['goal' => 'الهدف أقل من المساهمات الموجودة']);
                }
                DB::table('religious_circles')->where('id', $row->id)->update([...array_diff_key($data, ['id' => true]), 'updated_at' => now()]);
            } else {
                DB::table('religious_circles')->insert([...array_diff_key($data, ['id' => true]), 'created_at' => now(), 'updated_at' => now()]);
            }
        });

        return ['saved' => true];
    }

    public function review(Request $request, int $entry)
    {
        $data = $request->validate(['status' => 'required|in:approved,rejected']);
        DB::transaction(function () use ($entry, $data): void {
            $row = DB::table('religious_community_entries')->where('id', $entry)->lockForUpdate()->first();
            abort_unless($row && $row->status !== 'deleted', 404);
            if ($row->parent_id && $data['status'] === 'approved') {
                abort_unless(DB::table('religious_community_entries')->where('id', $row->parent_id)->where('status', 'approved')->exists(), 422);
            }
            DB::table('religious_community_entries')->where('id', $entry)->update(['status' => $data['status'], 'updated_at' => now()]);
        });

        return ['saved' => true];
    }

    public function report(Request $request, int $report)
    {
        $data = $request->validate(['action' => 'required|in:resolve,hide,suspend,restore']);
        DB::transaction(function () use ($report, $data): void {
            $row = DB::table('religious_community_reports')->where('id', $report)->lockForUpdate()->first();
            abort_unless($row, 404);
            if ($data['action'] === 'hide') {
                abort_unless($row->target_type === 'entry', 422);
                DB::table('religious_community_entries')->where('id', $row->target_id)->where('status', '!=', 'deleted')->update(['status' => 'rejected', 'updated_at' => now()]);
            }
            if (in_array($data['action'], ['suspend', 'restore'])) {
                $account = $row->target_type === 'account' ? $row->target_id : DB::table('religious_community_entries')->where('id', $row->target_id)->value('account_id');
                abort_unless($account && DB::table('religious_accounts')->where('id', $account)->lockForUpdate()->first(), 404);
                DB::table('religious_community_accounts')->updateOrInsert(['account_id' => $account], ['suspended' => $data['action'] === 'suspend']);
            }
            DB::table('religious_community_reports')->where('id', $report)->update(['status' => 'resolved', 'updated_at' => now()]);
        });

        return ['saved' => true];
    }

    public function publication(Request $request)
    {
        $data = $request->validate(['id' => 'nullable|integer|min:1', 'channel' => 'required|in:message,story', 'kind' => 'required|in:quran,hadith,dua,dhikr,tasbih,video,link',
            'title' => 'required|string|max:100', 'body' => 'nullable|string|max:2000', 'content_id' => 'nullable|string|max:120',
            'youtube_id' => 'nullable|regex:/^[A-Za-z0-9_-]{11}$/', 'link' => 'nullable|url:https|max:2048',
            'status' => 'required|in:draft,scheduled,published,hidden', 'publish_at' => 'nullable|date', 'expires_at' => 'nullable|date|after:publish_at',
            'media' => 'nullable|file|mimetypes:image/jpeg,image/png,video/mp4|max:20480', 'clear_media' => 'sometimes|boolean']);
        $existing = empty($data['id']) ? null : DB::table('religious_publications')->find($data['id']);
        if (! empty($data['id'])) {
            abort_unless($existing, 404);
        }
        $content = empty($data['content_id']) ? null : $this->community->content($data['content_id']);
        if (in_array($data['kind'], ['quran', 'hadith', 'dua', 'dhikr', 'tasbih']) && $content === null) {
            throw ValidationException::withMessages(['content_id' => 'اختر المحتوى الديني الموثق؛ لا يُكتب نصه يدويًا هنا']);
        }
        if ($content !== null && ! empty($data['body'])) {
            throw ValidationException::withMessages(['body' => 'المحتوى الديني يُعرض حرفيًا من المصدر؛ اترك الوصف فارغًا']);
        }
        if ($content !== null && (($data['kind'] === 'quran' && ! in_array($content['id'], array_column(array_filter(app(ReligiousCatalog::class)->dataset()['items'], fn ($i) => $i['type'] === 'quran'), 'id'))) ||
            ($data['kind'] === 'hadith' && ! in_array($content['id'], array_column(array_filter(app(ReligiousCatalog::class)->dataset()['items'], fn ($i) => $i['type'] === 'hadith'), 'id'))))) {
            throw ValidationException::withMessages(['content_id' => 'نوع المحتوى لا يطابق التصنيف']);
        }
        if (in_array($data['status'], ['published', 'scheduled']) && empty($data['publish_at'])) {
            throw ValidationException::withMessages(['publish_at' => 'وقت النشر مطلوب']);
        }
        if ($data['kind'] === 'link' && empty($data['link'])) {
            throw ValidationException::withMessages(['link' => 'الرابط مطلوب']);
        }
        if (! empty($data['link']) && parse_url($data['link'], PHP_URL_USER) !== null) {
            throw ValidationException::withMessages(['link' => 'رابط غير صالح']);
        }
        $file = $request->file('media');
        foreach (['publish_at', 'expires_at'] as $dateField) {
            if (! empty($data[$dateField])) {
                $data[$dateField] = \Illuminate\Support\Carbon::parse($data[$dateField], config('app.timezone'))->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            }
        }
        $newPath = null;
        if ($file) {
            $newPath = $file->store('religious-community', 'public');
        }
        try {
            $path = $newPath ?? ($request->boolean('clear_media') ? null : $existing?->media_path);
            if ($data['kind'] === 'video' && empty($data['youtube_id']) && (! $path || ! str_ends_with($path, '.mp4'))) {
                throw ValidationException::withMessages(['media' => 'اختر فيديو MP4 أو معرف YouTube']);
            }
            if ($data['kind'] !== 'video' && $path && str_ends_with($path, '.mp4')) {
                throw ValidationException::withMessages(['media' => 'الفيديو يحتاج تصنيف فيديو']);
            }
            if ($data['kind'] !== 'video' && ! empty($data['youtube_id'])) {
                throw ValidationException::withMessages(['youtube_id' => 'معرف يوتيوب خاص بالفيديو']);
            }
            $values = [...array_diff_key($data, ['id' => true, 'media' => true, 'clear_media' => true]), 'media_path' => $path, 'updated_at' => now()];
            if ($existing) {
                DB::table('religious_publications')->where('id', $existing->id)->update($values);
            } else {
                DB::table('religious_publications')->insert([...$values, 'created_at' => now()]);
            }
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }throw $e;
        }

        return ['saved' => true];
    }
}
