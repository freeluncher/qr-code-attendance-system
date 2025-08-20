<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocationService;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
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
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
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
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
        ]);
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
}
