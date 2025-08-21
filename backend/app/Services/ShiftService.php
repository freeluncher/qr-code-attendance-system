<?php
namespace App\Services;

use App\Repositories\ShiftRepository;

class ShiftService
{
    protected $shiftRepository;

    public function __construct(ShiftRepository $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }

    // Ambil semua shifts dengan pagination dan filter
    public function getAllShifts($perPage = 10, $locationId = null, $dayOfWeek = null)
    {
        return $this->shiftRepository->getAllShifts($perPage, $locationId, $dayOfWeek);
    }

    // Ambil satu shift berdasarkan id
    public function getShiftById($id)
    {
        return $this->shiftRepository->getShiftById($id);
    }

    // Buat shift baru
    public function createShift(array $data)
    {
        return $this->shiftRepository->createShift($data);
    }

    // Update shift by id
    public function updateShift($id, array $data)
    {
        return $this->shiftRepository->updateShift($id, $data);
    }

    // Delete shift by id
    public function deleteShift($id)
    {
        return $this->shiftRepository->deleteShift($id);
    }

    // Get shifts for specific location
    public function getShiftsForLocation($locationId)
    {
        return $this->shiftRepository->getShiftsForLocation($locationId);
    }

    // Get available shifts for location on specific day
    public function getAvailableShiftsForLocationAndDay($locationId, $dayOfWeek)
    {
        return $this->shiftRepository->getAvailableShiftsForLocationAndDay($locationId, $dayOfWeek);
    }
}
