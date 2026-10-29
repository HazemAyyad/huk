<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\AdhanCatalog;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AdhanController extends Controller
{
    public function __construct(private readonly AdhanCatalog $catalog) {}

    public function catalog(): JsonResponse
    {
        $data = $this->catalog->dataset();
        $voices = array_map(function (array $voice): array {
            $item = array_intersect_key($voice, array_flip(['id', 'name', 'englishName', 'bundled']));
            foreach (['full', 'takbeer'] as $kind) {
                $item[$kind] = [
                    'url' => route('religious.adhans.audio', ['voice' => $voice['id'], 'kind' => $kind]),
                    'bytes' => $voice[$kind]['bytes'],
                    'sha256' => $voice[$kind]['sha256'],
                ];
            }

            return $item;
        }, $data['voices']);

        return response()->json(['data' => [
            'version' => $data['version'],
            'source' => $data['source'],
            'voices' => $voices,
        ]])->header('Cache-Control', 'public, max-age=3600');
    }

    public function audio(string $voice, string $kind): BinaryFileResponse
    {
        $recording = $this->catalog->recording($voice, $kind);

        return response()->file($this->catalog->file($recording), [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-SHA256' => $recording['sha256'],
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
