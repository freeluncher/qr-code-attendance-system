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

    // Ambil semua shifts dengan pagination
    public function getAllShifts($perPage = 10)
    {
        return $this->shiftRepository->getAllShifts($perPage);
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
}
