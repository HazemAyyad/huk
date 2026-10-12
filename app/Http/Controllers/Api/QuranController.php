<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Quran\QuranCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class QuranController extends Controller
{
    public function __construct(private readonly QuranCatalog $catalog) {}

    public function manifest(): JsonResponse
    {
        return response()->json(['data' => $this->catalog->manifest() + [
            'dataset_url' => route('quran.dataset'),
        ]]);
    }

    public function dataset(): BinaryFileResponse
    {
        return response()->file($this->catalog->path(), [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-SHA256' => hash_file('sha256', $this->catalog->path()),
        ]);
    }

    public function surahs(): JsonResponse
    {
        return response()->json(['data' => $this->catalog->surahs()]);
    }

    public function juzs(): JsonResponse
    {
        return response()->json(['data' => $this->catalog->juzs()]);
    }

    public function verses(Request $request): JsonResponse
    {
        $input = $request->validate([
            'surah' => ['nullable', 'integer', 'between:1,114'],
            'juz' => ['nullable', 'integer', 'between:1,30'],
            'page' => ['nullable', 'integer', 'between:1,604'],
            'cursor' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $items = $this->catalog->verses(
            isset($input['surah']) ? (int) $input['surah'] : null,
            isset($input['juz']) ? (int) $input['juz'] : null,
            isset($input['page']) ? (int) $input['page'] : null,
        );

        return $this->paginate($items, $input);
    }

    public function search(Request $request): JsonResponse
    {
        $input = $request->validate([
            'q' => ['required', 'string', 'max:120'],
            'surah' => ['nullable', 'integer', 'between:1,114'],
            'juz' => ['nullable', 'integer', 'between:1,30'],
            'cursor' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $items = $this->catalog->search($input['q'],
            isset($input['surah']) ? (int) $input['surah'] : null,
            isset($input['juz']) ? (int) $input['juz'] : null,
        );

        return $this->paginate($items, $input);
    }

    private function paginate(array $items, array $input): JsonResponse
    {
        $cursor = (int) ($input['cursor'] ?? 0);
        $limit = (int) ($input['limit'] ?? 50);
        $total = count($items);

        return response()->json([
            'data' => array_slice($items, $cursor, $limit),
            'meta' => ['total' => $total, 'cursor' => $cursor,
                'next_cursor' => $cursor + $limit < $total ? $cursor + $limit : null,
                'version' => $this->catalog->manifest()['version']],
        ]);
    }
}
