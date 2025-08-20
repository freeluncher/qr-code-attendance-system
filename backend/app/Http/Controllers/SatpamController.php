<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SatpamController extends Controller
{
    /**
     * Get satpam dashboard statistics
     */
    public function getDashboardStats()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'User not authenticated'
            ], 401);
        }

        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('scanned_at', $today)
            ->with('location')
            ->first();

        // This month statistics
        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('scanned_at', '>=', $thisMonth)
            ->get();

        $presentDays = $monthlyAttendances->where('status', '!=', 'absent')->count();
        $onTimeDays = $monthlyAttendances->where('status', 'on_time')->count();
        $totalWorkingDays = $thisMonth->diffInWeekdays(Carbon::now());

        return response()->json([
            'today' => [
                'has_attendance' => !!$todayAttendance,
                'check_in' => $todayAttendance?->scanned_at?->format('H:i'),
                'check_out' => null, // Not supported in current schema
                'location' => $todayAttendance?->location?->name,
                'status' => $todayAttendance?->status ?? 'not_checked_in'
            ],
            'this_month' => [
                'present_days' => $presentDays,
                'total_days' => $totalWorkingDays,
                'on_time_count' => $onTimeDays,
                'on_time_rate' => $totalWorkingDays > 0 ? round(($onTimeDays / $totalWorkingDays) * 100, 1) : 0
            ]
        ]);
    }

    /**
     * Get today's attendance status
     */
    public function getTodayAttendance()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('scanned_at', $today)
            ->with('location')
            ->first();

        if (!$attendance) {
            return response()->json([
                'check_in' => null,
                'check_out' => null,
                'location' => null,
                'status' => null
            ]);
        }

        return response()->json([
            'check_in' => $attendance->scanned_at ? $attendance->scanned_at->format('H:i') : null,
            'check_out' => null, // Not supported in current schema
            'location' => $attendance->location?->name,
            'status' => $this->getAttendanceStatus($attendance)
        ]);
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStats()
    {
        $user = Auth::user();
        $thisMonth = Carbon::now()->startOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereDate('scanned_at', '>=', $thisMonth)
            ->get();

        $presentCount = $attendances->where('status', '!=', 'absent')->count();
        $onTimeCount = $attendances->where('status', 'on_time')->count();
        $totalWorkingDays = $thisMonth->diffInWeekdays(Carbon::now());

        return response()->json([
            'present_days' => $presentCount,
            'total_days' => $totalWorkingDays,
            'on_time_days' => $onTimeCount,
            'on_time_percentage' => $totalWorkingDays > 0 ? round(($onTimeCount / $totalWorkingDays) * 100, 1) : 0
        ]);
    }

    /**
     * Get today's schedule
     */
    public function getTodaySchedule()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // For now, return mock schedule data
        // In a real implementation, you would query a shifts/schedules table
        $schedule = [
            'shift_name' => 'Shift Pagi',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'duration' => 8,
            'location' => [
                'name' => 'Pos Utama',
                'address' => 'Gedung A, Lantai 1'
            ],
            'notes' => 'Shift normal hari kerja'
        ];

        return response()->json($schedule);
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'User not authenticated'
            ], 401);
        }

        $limit = $request->get('limit', 10);

        $attendances = Attendance::where('user_id', $user->id)
            ->with('location')
            ->orderBy('scanned_at', 'desc')
            ->limit($limit)
            ->get();

        $activities = [];

        foreach ($attendances as $attendance) {
            $activities[] = [
                'type' => 'check_in',
                'description' => 'Presensi di ' . ($attendance->location?->name ?? 'Lokasi tidak diketahui'),
                'time' => $attendance->scanned_at->format('H:i'),
                'date' => $attendance->scanned_at->format('d M Y'),
                'location' => $attendance->location?->name ?? 'Lokasi tidak diketahui'
            ];
        }

        return response()->json($activities);
    }    /**
     * Process QR Code attendance
     */
    public function processQrAttendance(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        $user = Auth::user();
        $today = Carbon::today();

        // Verify QR code
        $qrCode = QrCode::where('code', $request->qr_code)
            ->where('is_active', true)
            ->with('location')
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah tidak aktif'
            ], 400);
        }

        // Check if user already has attendance for today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('scanned_at', $today)
            ->first();

        $now = Carbon::now();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi hari ini'
            ], 400);
        }

        // Create new attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'location_id' => $qrCode->location_id,
            'qr_code_id' => $qrCode->id,
            'scanned_at' => $now,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'present' // or calculate based on time if needed
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil',
            'time' => $now->format('H:i'),
            'location' => $qrCode->location->name,
            'status' => $attendance->status
        ]);
    }

    /**
     * Get attendance history
     */
    public function getAttendanceHistory(Request $request)
    {
        $user = Auth::user();

        $query = Attendance::where('user_id', $user->id)
            ->with('location');

        // Apply filters
        if ($request->start_date) {
            $query->whereDate('scanned_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('scanned_at', '<=', $request->end_date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        $attendances = $query->orderBy('scanned_at', 'desc')
            ->paginate($request->get('per_page', 15));

        $data = $attendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'date' => $attendance->scanned_at->format('Y-m-d'),
                'time' => $attendance->scanned_at->format('H:i'),
                'location' => $attendance->location?->name,
                'status' => $attendance->status,
                'notes' => $attendance->notes ?? ''
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total()
            ]
        ]);
    }

    /**
     * Get schedule data
     */
    public function getSchedule(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        // For now, return mock schedule data
        // In a real implementation, you would query actual shift assignments
        $schedules = [
            [
                'id' => 1,
                'date' => Carbon::create($year, $month, 21)->format('Y-m-d'),
                'shift_name' => 'Shift Pagi',
                'type' => 'pagi',
                'time' => '06:00 - 14:00',
                'location' => 'Pos Utama',
                'duration' => 8,
                'notes' => 'Shift normal hari kerja',
                'status' => 'scheduled'
            ],
            [
                'id' => 2,
                'date' => Carbon::create($year, $month, 22)->format('Y-m-d'),
                'shift_name' => 'Shift Pagi',
                'type' => 'pagi',
                'time' => '06:00 - 14:00',
                'location' => 'Pos Selatan',
                'duration' => 8,
                'notes' => '',
                'status' => 'scheduled'
            ]
        ];

        return response()->json($schedules);
    }

    /**
     * Calculate attendance status based on time
     */
    private function calculateStatus($time, $type)
    {
        if ($type === 'check_in') {
            $scheduledTime = Carbon::createFromTime(8, 0); // 08:00 AM
            return $time->lte($scheduledTime) ? 'present' : 'late';
        }

        return 'present';
    }

    /**
     * Get attendance status text
     */
    private function getAttendanceStatus($attendance)
    {
        if (!$attendance->scanned_at) {
            return 'not_checked_in';
        }

        if ($attendance->status === 'late') {
            return 'late';
        }

        return 'present';
    }

    /**
     * Calculate work duration
     */
    private function calculateWorkDuration($checkIn, $checkOut)
    {
        if (!$checkIn || !$checkOut) {
            return null;
        }

        $diff = $checkIn->diff($checkOut);

        $hours = $diff->h;
        $minutes = $diff->i;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        } else {
            return "{$minutes} menit";
        }
    }
}
