<?php
namespace App\Services;

use App\Repositories\LocationRepository;

class LocationService
{
    protected $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    // Ambil semua lokasi dengan pagination
    public function getAllLocations($perPage = 10)
    {
        return $this->locationRepository->getAll($perPage);
    }

    // Ambil detail satu lokasi
    public function getLocationById($id)
    {
        return $this->locationRepository->findById($id);
    }

    // Buat lokasi baru
    public function createLocation(array $data)
    {
        return $this->locationRepository->create($data);
    }

    // Update lokasi
    public function updateLocation($id, array $data)
    {
        return $this->locationRepository->update($id, $data);
    }

    // Hapus lokasi
    public function deleteLocation($id)
    {
        return $this->locationRepository->delete($id);
    }
}
