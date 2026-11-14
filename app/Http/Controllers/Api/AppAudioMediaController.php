<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\AdhanCatalog;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppAudioMedia;
use Illuminate\Http\Request;

final class AppAudioMediaController extends Controller
{
    public function __invoke(Request $request, AppAdminAccess $access, AppAudioMedia $media)
    {
        $grant = $access->require($request->user(), 'catalogs.manage');
        abort_unless(json_decode($grant->locales, true) === [], 403);
        $data = $request->validate([
            'type' => 'required|in:adhan,reminder,dhikr,adhkar_recitation,recitation,religious',
            'file' => 'nullable|file|max:102400', 'url' => 'nullable|url:https|max:2000',
            'voice' => ['nullable', 'regex:/\Anusuk-[1-9][0-9]*\z/'],
            'source_id' => 'nullable|string|max:96',
            'surah' => 'nullable|integer|min:1|max:114', 'batch_token' => 'nullable|string|max:3000',
        ]);
        abort_unless((int) $request->hasFile('file') + (int) isset($data['url']) + (int) isset($data['voice']) === 1, 422, 'اختر ملفًا أو مصدرًا واحدًا');
        $args = [$data['type'], (int) $request->user()->id, isset($data['surah']) ? (int) $data['surah'] : null, $data['batch_token'] ?? null];
        $expected = null;
        if (isset($data['source_id'])) {
            abort_unless($data['type'] === 'adhkar_recitation' && isset($data['url']), 422);
            $sources = json_decode(file_get_contents(config('haq_app.dhikr_readers', resource_path('haq-admin/dhikr-readers.json'))), true, 512, JSON_THROW_ON_ERROR);
            $expected = collect($sources)->firstWhere('id', $data['source_id']);
            abort_unless($expected !== null && $data['url'] === $expected['url'], 422, 'مصدر قراءة غير معروف');
        }
        if (isset($data['voice'])) {
            abort_unless($data['type'] === 'adhan', 422);
            $catalog = app(AdhanCatalog::class);
            $result = $media->store($catalog->file($catalog->recording($data['voice'], 'full')), ...$args);
        } elseif ($request->hasFile('file')) {
            $result = $media->store($request->file('file')->getRealPath(), ...$args);
        } else {
            $result = $media->download($data['url'], ...[...$args, $expected]);
        }
        $access->audit($request->user()->id, 'audio.file.staged', $data['type'], ['sha256' => $result['sha256'], 'bytes' => $result['bytes']]);

        return response()->json(['data' => $result])->header('Cache-Control', 'no-store');
    }
}
