<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private $apiToken;
    private $enabled;

    public function __construct()
    {
        $this->apiToken = config('services.locationiq.access_token');
        $this->enabled = config('services.locationiq.enabled', true);
    }

    /**
     * Get latitude and longitude from address
     */
    public function getCoordinatesFromAddress(string $address): ?array
    {
        if (!$this->enabled || !$this->apiToken) {
            Log::warning('Geocoding service is disabled or access token not set', [
                'enabled' => $this->enabled,
                'has_token' => !empty($this->apiToken)
            ]);
            return null;
        }

        Log::info('Geocoding request started', ['address' => $address]);

        try {
            $url = 'https://us1.locationiq.com/v1/search';
            $params = [
                'key' => $this->apiToken,
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => 'ID' // Restrict to Indonesia
            ];

            Log::info('Making geocoding API request', [
                'url' => $url,
                'params' => array_merge($params, ['key' => 'HIDDEN'])
            ]);

            $response = Http::timeout(10)->get($url, $params);

            Log::info('Geocoding API response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'response_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Geocoding response data', ['data' => $data]);

                if (isset($data[0])) {
                    $result = [
                        'latitude' => (float) $data[0]['lat'],
                        'longitude' => (float) $data[0]['lon'],
                        'display_name' => $data[0]['display_name'] ?? null,
                        'boundingbox' => $data[0]['boundingbox'] ?? null
                    ];

                    Log::info('Geocoding successful', $result);
                    return $result;
                }
            } else {
                Log::warning('Geocoding API failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

            Log::warning('Geocoding API returned empty results', ['address' => $address]);
            return null;

        } catch (\Exception $e) {
            Log::error('Geocoding API error', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Validate if coordinates are within Indonesia bounds
     */
    public function validateIndonesianCoordinates(float $latitude, float $longitude): bool
    {
        // Indonesia approximate bounds
        $minLat = -11.0;
        $maxLat = 6.0;
        $minLng = 95.0;
        $maxLng = 141.0;

        return $latitude >= $minLat && $latitude <= $maxLat &&
               $longitude >= $minLng && $longitude <= $maxLng;
    }
}
