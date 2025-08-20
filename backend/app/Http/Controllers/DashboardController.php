<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Location;
use App\Models\QrCode;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function adminStats(Request $request)
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Total counts with specific filters
        $totalSatpam = User::where('role', 'satpam')->count();
        $totalLocations = Location::count();
        // Count active QR codes (not expired)
        $totalQrCodes = $this->getActiveQrCodesCount();

        // Today's attendance statistics
        $todayAttendances = Attendance::whereDate('created_at', $today)->get();
        $todayAttendanceCount = $todayAttendances->count();
        $todayLateCount = $todayAttendances->where('late_category', '!=', 'tepat_waktu')->count();
        $todayOnTimeCount = $todayAttendances->where('late_category', 'tepat_waktu')->count();

        // This week statistics
        $weekAttendances = Attendance::where('created_at', '>=', $thisWeek)->count();

        // This month statistics
        $monthAttendances = Attendance::where('created_at', '>=', $thisMonth)->count();

        // Average daily attendance this month
        $daysInMonth = Carbon::now()->daysInMonth;
        $avgDailyAttendance = $monthAttendances > 0 ? round($monthAttendances / $daysInMonth, 1) : 0;

        return response()->json([
            'total_satpam' => $totalSatpam,
            'total_locations' => $totalLocations,
            'total_qr_codes' => $totalQrCodes,
            'today' => [
                'total_attendance' => $todayAttendanceCount,
                'late_count' => $todayLateCount,
                'on_time_count' => $todayOnTimeCount,
                'attendance_rate' => $totalSatpam > 0 ? round(($todayAttendanceCount / $totalSatpam) * 100, 1) : 0,
            ],
            'this_week' => [
                'total_attendance' => $weekAttendances,
            ],
            'this_month' => [
                'total_attendance' => $monthAttendances,
                'avg_daily_attendance' => $avgDailyAttendance,
            ],
        ]);
    }

    /**
     * Get recent activities for admin dashboard
     */
    public function recentActivities(Request $request)
    {
        $limit = $request->input('limit', 10);

        $activities = Attendance::with(['user', 'location'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'type' => 'attendance',
                    'user_name' => $attendance->user->name,
                    'action' => 'melakukan presensi',
                    'location_name' => $attendance->location->name,
                    'status' => $attendance->late_category,
                    'created_at' => $attendance->created_at,
                    'formatted_time' => $attendance->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'data' => $activities
        ]);
    }

    /**
     * Get top late employees
     */
    public function topLateEmployees(Request $request)
    {
        $days = $request->input('days', 7); // Default 7 days
        $limit = $request->input('limit', 5);

        $startDate = Carbon::now()->subDays($days);

        $lateEmployees = DB::table('attendances')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as late_count'),
                DB::raw('MAX(locations.name) as location_name') // Get most recent location
            )
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->join('locations', 'attendances.location_id', '=', 'locations.id')
            ->where('attendances.created_at', '>=', $startDate)
            ->where('attendances.late_category', '!=', 'tepat_waktu')
            ->where('users.role', 'satpam')
            ->groupBy('users.id', 'users.name')
            ->orderBy('late_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $lateEmployees->map(function ($employee) {
                return [
                    'user_id' => $employee->id,
                    'name' => $employee->name,
                    'location' => $employee->location_name,
                    'late_count' => $employee->late_count,
                ];
            })
        ]);
    }

    /**
     * Get attendance chart data for last N days
     */
    public function attendanceChart(Request $request)
    {
        $days = $request->input('days', 7);

        $chartData = collect();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            $dayAttendances = Attendance::whereDate('created_at', $date->toDateString())->get();

            $total = $dayAttendances->count();
            $onTime = $dayAttendances->where('late_category', 'tepat_waktu')->count();
            $late = $total - $onTime;

            $chartData->push([
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('D'),
                'formatted_date' => $date->format('d M'),
                'total' => $total,
                'on_time' => $onTime,
                'late' => $late,
                'on_time_percentage' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
            ]);
        }

        return response()->json([
            'data' => $chartData
        ]);
    }

    /**
     * Get satpam dashboard statistics
     */
    public function satpamStats(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Today's attendance
        $todayAttendance = Attendance::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->first();

        // This month's attendance
        $monthAttendances = Attendance::where('user_id', $userId)
            ->where('created_at', '>=', $thisMonth)
            ->get();

        $monthTotal = $monthAttendances->count();
        $monthOnTime = $monthAttendances->where('late_category', 'tepat_waktu')->count();
        $monthLate = $monthTotal - $monthOnTime;

        // Expected working days this month (excluding weekends)
        $workingDays = $this->getWorkingDaysInMonth();

        return response()->json([
            'today' => [
                'has_attendance' => $todayAttendance ? true : false,
                'status' => $todayAttendance ? $todayAttendance->late_category : null,
                'scanned_at' => $todayAttendance ? $todayAttendance->scanned_at : null,
                'location' => $todayAttendance ? $todayAttendance->location->name : null,
            ],
            'this_month' => [
                'total_attendance' => $monthTotal,
                'on_time_count' => $monthOnTime,
                'late_count' => $monthLate,
                'working_days' => $workingDays,
                'attendance_rate' => $workingDays > 0 ? round(($monthTotal / $workingDays) * 100, 1) : 0,
                'on_time_rate' => $monthTotal > 0 ? round(($monthOnTime / $monthTotal) * 100, 1) : 0,
            ],
        ]);
    }

    /**
     * Get satpam recent attendance history
     */
    public function satpamHistory(Request $request)
    {
        $userId = auth()->id();
        $limit = $request->input('limit', 7);

        $history = Attendance::with(['location'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->created_at->format('Y-m-d'),
                    'formatted_date' => $attendance->created_at->format('l, d F Y'),
                    'location' => $attendance->location->name,
                    'scanned_at' => $attendance->scanned_at->format('H:i'),
                    'status' => $attendance->late_category,
                    'status_label' => $attendance->late_category === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat',
                    'photo_url' => $attendance->photo_url ? asset($attendance->photo_url) : null,
                ];
            });

        return response()->json([
            'data' => $history
        ]);
    }

    /**
     * Calculate working days in current month (excluding weekends)
     */
    private function getWorkingDaysInMonth()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $workingDays = 0;

        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }

    /**
     * Get active QR codes (not expired)
     */
    private function getActiveQrCodesCount()
    {
        return QrCode::where('expires_at', '>', Carbon::now())->count();
    }

    /**
     * Check if a QR code is active (not expired)
     */
    private function isQrCodeActive($qrCode)
    {
        return $qrCode->expires_at > Carbon::now();
    }
}
