<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

final class ReligiousAdhkarController extends Controller
{
    private function raw(): string
    {
        return file_get_contents(resource_path('religious/adhkar.json'));
    }

    public function manifest()
    {
        $raw = $this->raw();
        $data = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

        return response()->json(['data' => ['schema_version' => 1, 'version' => $data['version'],
            'sha256' => hash('sha256', $raw), 'groups' => count($data['groups']), 'items' => count($data['items']),
            'url' => url('/api/v1/religious/adhkar/dataset')]]);
    }

    public function dataset()
    {
        return response($this->raw())->header('Content-Type', 'application/json; charset=UTF-8');
    }
}
