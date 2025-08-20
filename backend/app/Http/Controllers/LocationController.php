<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocationService;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    protected $locationService;
    protected $geocodingService;

    public function __construct(LocationService $locationService, GeocodingService $geocodingService)
    {
        $this->locationService = $locationService;
        $this->geocodingService = $geocodingService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $locations = $this->locationService->getAllLocations($perPage);
        return response()->json($locations);
    }

    public function show($id)
    {
        $location = $this->locationService->getLocationById($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }
        return response()->json($location);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:locations,name,NULL,id,latitude,' . $request->latitude . ',longitude,' . $request->longitude
            ],
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Auto-fill coordinates if not provided
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $coordinates = $this->geocodingService->getCoordinatesFromAddress($data['address']);
            if ($coordinates) {
                $data['latitude'] = $coordinates['latitude'];
                $data['longitude'] = $coordinates['longitude'];
            } else {
                return response()->json([
                    'message' => 'Tidak dapat menemukan koordinat untuk alamat tersebut. Silakan masukkan latitude dan longitude secara manual.',
                    'error' => 'GEOCODING_FAILED'
                ], 422);
            }
        }

        // Validate if coordinates are within Indonesia
        if (!$this->geocodingService->validateIndonesianCoordinates($data['latitude'], $data['longitude'])) {
            return response()->json([
                'message' => 'Koordinat tidak valid atau berada di luar Indonesia.',
                'error' => 'INVALID_COORDINATES'
            ], 422);
        }

        $location = $this->locationService->createLocation($data);

        return response()->json([
            'message' => 'Location created successfully.',
            'location' => $location,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:locations,name,' . $id . ',id,latitude,' . $request->latitude . ',longitude,' . $request->longitude
            ],
            'address' => 'sometimes|required|string|max:255',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'status' => 'sometimes|required|in:aktif,nonaktif',
        ]);

        // Auto-fill coordinates if address changed but coordinates not provided
        if (isset($data['address']) && (empty($data['latitude']) || empty($data['longitude']))) {
            $coordinates = $this->geocodingService->getCoordinatesFromAddress($data['address']);
            if ($coordinates) {
                $data['latitude'] = $coordinates['latitude'];
                $data['longitude'] = $coordinates['longitude'];
            }
        }

        // Validate coordinates if provided
        if (isset($data['latitude']) && isset($data['longitude'])) {
            if (!$this->geocodingService->validateIndonesianCoordinates($data['latitude'], $data['longitude'])) {
                return response()->json([
                    'message' => 'Koordinat tidak valid atau berada di luar Indonesia.',
                    'error' => 'INVALID_COORDINATES'
                ], 422);
            }
        }

        $location = $this->locationService->updateLocation($id, $data);

        return response()->json([
            'message' => 'Location updated successfully.',
            'location' => $location,
        ]);
    }

    public function destroy($id)
    {
        $this->locationService->deleteLocation($id);
        return response()->json(['message' => 'Location deleted successfully']);
    }

    /**
     * Get coordinates from address using geocoding API
     */
    public function geocode(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500'
        ]);

        Log::info('Geocoding request received', ['address' => $request->address]);

        try {
            $coordinates = $this->geocodingService->getCoordinatesFromAddress($request->address);

            if (!$coordinates) {
                Log::warning('Geocoding failed for address', ['address' => $request->address]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menemukan koordinat untuk alamat tersebut. Silakan coba alamat yang lebih spesifik.',
                    'error' => 'GEOCODING_FAILED',
                    'suggestions' => [
                        'Pastikan alamat mencakup nama kota',
                        'Gunakan nama jalan yang lengkap',
                        'Coba tambahkan kode pos'
                    ]
                ], 422);
            }

            Log::info('Geocoding successful', ['address' => $request->address, 'coordinates' => $coordinates]);

            return response()->json([
                'success' => true,
                'data' => $coordinates,
                'message' => 'Koordinat berhasil ditemukan.'
            ]);

        } catch (\Exception $e) {
            Log::error('Geocoding controller error', [
                'address' => $request->address,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari koordinat.',
                'error' => 'GEOCODING_ERROR'
            ], 500);
        }
    }
}
