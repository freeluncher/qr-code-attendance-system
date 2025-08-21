<?php
namespace App\Repositories;

use App\Models\Shift;

class ShiftRepository
{
    public function getAllShifts($perPage = 10, $locationId = null, $dayOfWeek = null)
    {
        $query = Shift::with('location')->active();

        if ($locationId) {
            $query->forLocation($locationId);
        }

        if ($dayOfWeek) {
            $query->forDay($dayOfWeek);
        }

        return $query->paginate($perPage);
    }

    public function getShiftById($id)
    {
        return Shift::with('location')->find($id);
    }

    public function createShift(array $data)
    {
        return Shift::create($data);
    }

    public function updateShift($id, array $data)
    {
        $shift = Shift::findOrFail($id);
        $shift->update($data);
        return $shift->fresh();
    }

    public function deleteShift($id)
    {
        $shift = Shift::findOrFail($id);
        return $shift->delete();
    }

    public function getShiftsForLocation($locationId)
    {
        return Shift::forLocation($locationId)
            ->active()
            ->orderBy('start_time')
            ->get();
    }

    public function getAvailableShiftsForLocationAndDay($locationId, $dayOfWeek)
    {
        return Shift::forLocation($locationId)
            ->forDay($dayOfWeek)
            ->active()
            ->orderBy('start_time')
            ->get();
    }
}
