<?php

namespace App\Services\Religious;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Stage immutable audio files; the catalog still requires review and publication. */
final class AppAudioMedia
{
    public function store(string $path, string $type, int $user, ?int $surah, ?string $token): array
    {
        $limit = in_array($type, ['recitation', 'adhkar_recitation'], true) ? 100 * 1024 * 1024 : 20 * 1024 * 1024;
        abort_unless(is_file($path) && filesize($path) >= 1000 && filesize($path) <= $limit, 422, 'حجم التسجيل غير مدعوم');
        $handle = fopen($path, 'rb');
        try {
            $header = fread($handle, 3);
        } finally {
            fclose($handle);
        }
        abort_unless(str_starts_with($header, 'ID3') || (ord($header[0]) === 255 && (ord($header[1]) & 224) === 224), 422, 'اختر ملف MP3 صالحًا');
        if ($token !== null) {
            try {
                $batch = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                abort(422, 'جلسة رفع غير صالحة');
            }
            abort_unless(($batch['user'] ?? null) === $user && ($batch['type'] ?? null) === $type && ($batch['expires'] ?? 0) >= now()->timestamp
                && Str::isUuid($batch['id'] ?? ''), 422, 'انتهت جلسة الرفع أو تغيّر نوع التسجيل');
        } else {
            $batch = ['id' => (string) Str::uuid(), 'user' => $user, 'type' => $type, 'expires' => now()->addDay()->timestamp];
            $token = Crypt::encryptString(json_encode($batch, JSON_THROW_ON_ERROR));
        }
        abort_unless($type !== 'recitation' || ($surah !== null && $surah >= 1 && $surah <= 114), 422, 'حدّد رقم السورة');
        $name = $type === 'recitation' ? sprintf('%03d.mp3', $surah) : 'recording.mp3';
        $folder = 'haq-audio/'.$batch['id'];
        $relative = $folder.'/'.$name;
        $disk = Storage::disk('public');
        $disk->makeDirectory($folder);
        $output = @fopen($disk->path($relative), 'xb');
        abort_unless($output, $disk->exists($relative) ? 409 : 503, 'تعذّر إنشاء ملف جديد؛ لا يمكن استبدال ملف مكتمل');
        $handle = fopen($path, 'rb');
        try {
            abort_unless(stream_copy_to_stream($handle, $output) === filesize($path), 503, 'تعذّر حفظ التسجيل');
        } catch (\Throwable $error) {
            fclose($output);
            $output = null;
            $disk->delete($relative);
            throw $error;
        } finally {
            fclose($handle);
            if (is_resource($output)) {
                fclose($output);
            }
        }

        return ['url' => secure_url($disk->url($type === 'recitation' ? $folder.'/' : $relative)),
            'file_url' => secure_url($disk->url($relative)), 'batch_token' => $token,
            'bytes' => filesize($path), 'sha256' => hash_file('sha256', $path), 'surah' => $surah];
    }

    public function download(string $url, string $type, int $user, ?int $surah, ?string $token, ?array $expected = null): array
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $allowed = in_array($host, ['cdn.aladhan.com', 'media.way2quran.com', 'www.hisnmuslim.com'], true)
            || ($type === 'adhkar_recitation' && $host === 'd1.islamhouse.com' && str_starts_with($parts['path'] ?? '', '/data/ar/ih_sounds/'))
            || $host === 'mp3quran.net' || str_ends_with($host, '.mp3quran.net')
            || ($host === 'raw.githubusercontent.com' && str_starts_with($parts['path'] ?? '', '/Kiwifu/adhan-mp3/'));
        abort_unless(($parts['scheme'] ?? '') === 'https' && ! isset($parts['user']) && ! isset($parts['pass'])
            && ! isset($parts['port']) && ! isset($parts['query']) && ! isset($parts['fragment'])
            && $allowed && str_ends_with(strtolower($parts['path'] ?? ''), '.mp3'), 422, 'اختر رابط MP3 من المصادر المدعومة أو ارفع الملف من جهازك');
        $response = Http::connectTimeout(10)->timeout(180)->withOptions(['stream' => true, 'allow_redirects' => false])->get($url);
        abort_unless($response->status() === 200, 422, 'تعذّر تنزيل التسجيل من المصدر');
        $limit = in_array($type, ['recitation', 'adhkar_recitation'], true) ? 100 * 1024 * 1024 : 20 * 1024 * 1024;
        abort_if((int) $response->header('Content-Length') > $limit, 422, 'التسجيل أكبر من الحجم المدعوم');
        $temporary = tempnam(sys_get_temp_dir(), 'haq-audio-');
        $output = fopen($temporary, 'wb');
        $body = $response->toPsrResponse()->getBody();
        try {
            $size = 0;
            while (! $body->eof()) {
                $chunk = $body->read(65536);
                abort_if($chunk === '' && ! $body->eof(), 422, 'انقطع تنزيل التسجيل');
                $size += strlen($chunk);
                abort_if($size > $limit, 422, 'التسجيل أكبر من الحجم المدعوم');
                abort_unless(fwrite($output, $chunk) === strlen($chunk), 503, 'تعذّر حفظ التنزيل');
            }
            fclose($output);
            $output = null;
            if ($expected !== null) {
                abort_unless(filesize($temporary) === $expected['bytes'] && hash_file('sha256', $temporary) === $expected['sha256'], 422, 'تغيّر تسجيل المصدر؛ أعد مراجعته قبل إضافته');
            }

            return $this->store($temporary, $type, $user, $surah, $token);
        } finally {
            $body->close();
            if (is_resource($output)) {
                fclose($output);
            }
            unlink($temporary);
        }
    }
}
