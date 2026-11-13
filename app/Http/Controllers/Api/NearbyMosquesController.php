<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NearbyMosquesController
{
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'between:500,5000'],
            'language' => ['required', 'string', 'regex:/^[a-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/D', 'max:20'],
        ]);

        $unavailable = response()->json(['message' => 'Mosque search is unavailable.'], 503);
        $key = config('services.google_places.key');
        if (blank($key)) {
            return $unavailable;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-Goog-Api-Key' => $key,
                    'X-Goog-FieldMask' => 'places.id,places.displayName,places.location,places.attributions',
                ])
                ->connectTimeout(4)
                ->timeout(10)
                ->post('https://places.googleapis.com/v1/places:searchNearby', [
                    'includedTypes' => ['mosque'],
                    'maxResultCount' => 20,
                    'rankPreference' => 'DISTANCE',
                    'languageCode' => $search['language'],
                    'locationRestriction' => [
                        'circle' => [
                            'center' => [
                                'latitude' => (float) $search['latitude'],
                                'longitude' => (float) $search['longitude'],
                            ],
                            'radius' => (int) $search['radius'],
                        ],
                    ],
                ]);
        } catch (ConnectionException) {
            return $unavailable;
        }

        $payload = $response->json();
        if (! $response->ok() || ! is_array($payload) || ! is_array($payload['places'] ?? [])) {
            return $unavailable;
        }

        return response()->json(['places' => $payload['places'] ?? []])
            ->header('Cache-Control', 'no-store');
    }
}
