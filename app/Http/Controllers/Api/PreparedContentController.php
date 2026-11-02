<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\PreparedContentCatalog;

final class PreparedContentController extends Controller
{
    public function index(PreparedContentCatalog $catalog)
    {
        abort_unless(config('haq_app.enabled'), 503);

        return response()->json(['data' => $catalog->catalog()])->header('Cache-Control', 'no-store');
    }

    public function download(PreparedContentCatalog $catalog, string $locale, string $kind)
    {
        abort_unless(config('haq_app.enabled'), 503);
        abort_unless(in_array($locale, ['en', 'tr'], true) && in_array($kind, ['quran', 'tafsir', 'dhikr', 'names', 'hadith'], true), 404);
        $metadata = $catalog->metadata($locale, $kind);

        return response($catalog->bytes($locale, $kind), 200, [
            'Content-Type' => 'application/gzip',
            'Content-Length' => (string) $metadata['bytes'],
            'Cache-Control' => 'public, max-age=3600, must-revalidate',
            'ETag' => '"'.$metadata['sha256'].'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
