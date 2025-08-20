<?php
namespace App\Repositories;

use App\Models\Attendance;

class AttendanceRepository
{
    public function getAllAttendances($perPage = 10)
    {
        return Attendance::with(['user','location','shift', 'qrCode'])->paginate($perPage);
    }

    public function findAttendanceById($id)
    {
        return Attendance::with(['user','location','shift', 'qrCode'])->findOrFail($id);
    }

    public function createAttendance(array $data)
    {
        return Attendance::create($data);
    }

    public function updateAttendance($id, array $data)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($data);
        return $attendance;
    }

    public function deleteAttendance($id)
    {
        $attendance = Attendance::findOrFail($id);
        return $attendance->delete();
    }

}
