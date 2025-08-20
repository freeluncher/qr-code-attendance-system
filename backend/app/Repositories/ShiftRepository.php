<?php
namespace App\Repositories;

use App\Models\Shift;

class ShiftRepository
{
    public function getAllShifts($perPage = 10)
    {
        return Shift::paginate($perPage);
    }

    public function getShiftById($id)
    {
        return Shift::find($id);
    }

    public function createShift(array $data)
    {
        return Shift::create($data);
    }

    public function updateShift($id, array $data)
    {
        $shift = Shift::findOrFail($id);
        $shift->update($data);
        return $shift;
    }

    public function deleteShift($id)
    {
        $shift = Shift::findOrFail($id);
        return $shift->delete();
    }
}
