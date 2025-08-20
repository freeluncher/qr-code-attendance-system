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
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        // This month statistics
        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->where('attendance_date', '>=', $thisMonth)
            ->get();

        $presentDays = $monthlyAttendances->where('status', '!=', 'absent')->count();
        $onTimeDays = $monthlyAttendances->where('status', 'present')->count();
        $totalWorkingDays = $thisMonth->diffInWeekdays(Carbon::now());

        return response()->json([
            'today' => [
                'has_attendance' => !!$todayAttendance,
                'check_in' => $todayAttendance?->check_in_time,
                'check_out' => $todayAttendance?->check_out_time,
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
            ->whereDate('attendance_date', $today)
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
            'check_in' => $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : null,
            'check_out' => $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : null,
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
            ->where('attendance_date', '>=', $thisMonth)
            ->get();

        $presentCount = $attendances->where('status', '!=', 'absent')->count();
        $onTimeCount = $attendances->where('status', 'present')->count();
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
        $limit = $request->get('limit', 10);

        $attendances = Attendance::where('user_id', $user->id)
            ->with('location')
            ->orderBy('attendance_date', 'desc')
            ->limit($limit)
            ->get();

        $activities = [];

        foreach ($attendances as $attendance) {
            if ($attendance->check_in_time) {
                $activities[] = [
                    'type' => 'check_in',
                    'description' => 'Check-in di ' . ($attendance->location?->name ?? 'Lokasi tidak diketahui'),
                    'time' => $attendance->check_in_time->format('H:i'),
                    'date' => $attendance->attendance_date->format('d M Y')
                ];
            }

            if ($attendance->check_out_time) {
                $activities[] = [
                    'type' => 'check_out',
                    'description' => 'Check-out dari ' . ($attendance->location?->name ?? 'Lokasi tidak diketahui'),
                    'time' => $attendance->check_out_time->format('H:i'),
                    'date' => $attendance->attendance_date->format('d M Y')
                ];
            }
        }

        // Sort activities by date and time
        usort($activities, function($a, $b) {
            return strtotime($b['date'] . ' ' . $b['time']) - strtotime($a['date'] . ' ' . $a['time']);
        });

        return response()->json(array_slice($activities, 0, $limit));
    }

    /**
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

        // Check existing attendance for today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $now = Carbon::now();

        if (!$attendance) {
            // Create new attendance record (Check-in)
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'location_id' => $qrCode->location_id,
                'qr_code_id' => $qrCode->id,
                'attendance_date' => $today,
                'check_in_time' => $now,
                'check_in_latitude' => $request->latitude,
                'check_in_longitude' => $request->longitude,
                'status' => $this->calculateStatus($now, 'check_in')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil',
                'type' => 'check_in',
                'time' => $now->format('H:i'),
                'location' => $qrCode->location->name
            ]);

        } elseif (!$attendance->check_out_time) {
            // Update with check-out
            $attendance->update([
                'check_out_time' => $now,
                'check_out_latitude' => $request->latitude,
                'check_out_longitude' => $request->longitude,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil',
                'type' => 'check_out',
                'time' => $now->format('H:i'),
                'location' => $qrCode->location->name,
                'work_duration' => $this->calculateWorkDuration($attendance->check_in_time, $now)
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-out hari ini'
            ], 400);
        }
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
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate($request->get('per_page', 15));

        $data = $attendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'date' => $attendance->attendance_date->format('Y-m-d'),
                'check_in' => $attendance->check_in_time?->format('H:i'),
                'check_out' => $attendance->check_out_time?->format('H:i'),
                'duration' => $this->calculateWorkDuration($attendance->check_in_time, $attendance->check_out_time),
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
        if (!$attendance->check_in_time) {
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
