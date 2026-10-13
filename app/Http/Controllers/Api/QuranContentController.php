<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Quran\QuranCatalog;
use App\Services\Quran\QuranRemoteContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

final class QuranContentController extends Controller
{
    public function __construct(private readonly QuranRemoteContent $content, private readonly QuranCatalog $catalog) {}

    private function respond(callable $read, string $source): JsonResponse
    {
        try {
            return response()->json(['data' => $read(), 'source' => $source]);
        } catch (Throwable $error) {
            report($error);

            return response()->json(['message' => 'تعذّر تحميل المحتوى من المصدر. أعد المحاولة لاحقًا.'], 502);
        }
    }

    private function reference(string $surah, string $ayah): string
    {
        $data = Validator::make(compact('surah', 'ayah'), ['surah' => 'required|integer|between:1,114', 'ayah' => 'required|integer|min:1'])->validate();
        Validator::make($data, ['ayah' => 'integer|max:'.$this->catalog->surah((int) $data['surah'])['ayahCount']])->validate();

        return $surah.':'.$ayah;
    }

    public function page(string $page): JsonResponse
    {
        Validator::make(compact('page'), ['page' => 'required|integer|between:1,604'])->validate();

        return $this->respond(fn () => $this->content->page((int) $page), 'Quran.com • Mushaf QCF V2 • Quran Complex');
    }

    public function tafsir(string $source, string $surah, string $ayah): JsonResponse
    {
        Validator::make(compact('source'), ['source' => ['required', 'integer', Rule::in(array_keys(QuranRemoteContent::TAFSIRS))]])->validate();
        $key = $this->reference($surah, $ayah);

        return $this->respond(fn () => $this->content->tafsir((int) $source, $key), 'Quran.com • '.QuranRemoteContent::TAFSIRS[(int) $source]);
    }

    public function meanings(string $surah, string $ayah): JsonResponse
    {
        $this->reference($surah, $ayah);

        return $this->respond(fn () => $this->content->meanings((int) $surah, (int) $ayah), 'QuranEnc.com • السراج في بيان غريب القرآن • API v1');
    }

    public function recitations(): JsonResponse
    {
        return $this->respond(fn () => $this->content->recitations(), 'MP3Quran.net • Hafs');
    }

    public function words(string $surah, string $ayah): JsonResponse
    {
        $key = $this->reference($surah, $ayah);

        return $this->respond(fn () => $this->content->words($key), 'Quran.com • Word audio');
    }
}
