<?php
namespace App\Services;

use App\Repositories\AttendanceRepository;
use App\Models\qrCode;
use App\Models\AttendanceAudit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    protected $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function getAllAttendances($perPage = 10)
    {
        return $this->attendanceRepository->getAllAttendances($perPage);
    }

    public function getAttendanceById($id)
    {
        return $this->attendanceRepository->findAttendanceById($id);
    }

    public function createAttendance(array $data, $user)
    {
        DB::beginTransaction();
        try {
            // Validasi QR Code
            $qrCode = qrCode::where('code', $data['qr_code'])->first();
            if (!$qrCode || now()->gt($qrCode->expires_at)) {
                throw new \Exception('QR Code tidak valid atau sudah kadaluarsa.');
            }

            //Validasi GPS (radius 30m)
            $distance = $this->calculateDistance(
                $data['latitude'],
                $data['longitude'],
                $qrCode->location->latitude,
                $qrCode->location->longitude
            );
            if ($distance > 30) {
                throw new \Exception('Scan harus dalam 30 meter dari lokasi QR Code. Silakan coba lagi.');
            }

            // Upload Foto
            $photoPath = $data['photo']->store('attendances', 'public');
            $photoUrl = Storage::url($photoPath);

            // Tentukan status absensi
            $status = $this->determineAttendanceStatus($qrCode, $data['scanned_at'] ?? now());

            //Menyimpan data absensi
            $attendanceData = [
                'user_id' => $user->id,
                'location_id' => $qrCode->location_id,
                'shift_id' => $qrCode->shift_id,
                'qr_code_id' => $qrCode->id,
                'scanned_at' => now(),
                'status' => $status,
                'late_category' => $data['late_category'] ?? null,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'photo_url' => $photoUrl,
                'distance' => $distance,
            ];
            $attendance = $this->attendanceRepository->createAttendance($attendanceData);

            // Audit Log
            AttendanceAudit::create([
                'attendance_id' => $attendance->id,
                'action' => 'scan',
                'description' => 'Presensi berhasil',
            ]);

            DB::commit();
            return $attendance;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    // Helper: Hitung jarak GPS (harversine)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Jarak dalam meter
    }

    //Helper: tentukan status absensi
    private function determineAttendanceStatus($qrCode, $scannedAt)
    {
        // Misal on time jika scan <= shift start + 10 menit, late jika > 10 menit
        $shiftStart = $qrCode->shift->start_time;
        $scanTime = date('H:i', strtotime($scannedAt));
        return ($scanTime <= date('H:i', strtotime($shiftStart . ' + 10 minutes'))) ? 'on_time' : 'late';
    }

}
