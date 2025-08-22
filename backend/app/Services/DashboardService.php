<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use App\Services\AIPredictionService;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private DashboardRepository $dashboardRepository,
        private AIPredictionService $aiPredictionService
    ) {}

    /**
     * Get admin dashboard statistics
     */
    public function getAdminStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Total counts
        $totalSatpam = $this->dashboardRepository->getTotalSatpamCount();
        $totalLocations = $this->dashboardRepository->getTotalLocationsCount();
        $totalQrCodes = $this->dashboardRepository->getActiveQrCodesCount();

        // Today's attendance statistics
        $todayAttendances = $this->dashboardRepository->getTodayAttendances();
        $todayAttendanceCount = $todayAttendances->count();

        // Count unique users who attended today (for correct attendance rate)
        $todayUniqueAttendees = $this->dashboardRepository->getTodayUniqueAttendeesCount();
        $todayUniqueLateCount = $this->dashboardRepository->getTodayUniqueLateCount();
        $todayUniqueOnTimeCount = $this->dashboardRepository->getTodayUniqueOnTimeCount();

        // This week and month statistics
        $weekAttendances = $this->dashboardRepository->getAttendanceCountByDateRange($thisWeek);
        $monthAttendances = $this->dashboardRepository->getAttendanceCountByDateRange($thisMonth);

        // Average daily attendance this month
        $daysInMonth = Carbon::now()->daysInMonth;
        $avgDailyAttendance = $monthAttendances > 0 ? round($monthAttendances / $daysInMonth, 1) : 0;

        return [
            'total_satpam' => $totalSatpam,
            'total_locations' => $totalLocations,
            'total_qr_codes' => $totalQrCodes,
            'today' => [
                'total_attendance' => $todayUniqueAttendees, // Show unique attendees, not total records
                'late_count' => $todayUniqueLateCount,
                'on_time_count' => $todayUniqueOnTimeCount,
                'attendance_rate' => $totalSatpam > 0 ? round(($todayUniqueAttendees / $totalSatpam) * 100, 1) : 0,
            ],
            'this_week' => [
                'total_attendance' => $weekAttendances,
            ],
            'this_month' => [
                'total_attendance' => $monthAttendances,
                'avg_daily_attendance' => $avgDailyAttendance,
            ],
        ];
    }

    /**
     * Get recent activities formatted for admin dashboard
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $activities = $this->dashboardRepository->getRecentActivities($limit);

        return $activities->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'type' => 'attendance',
                'user_name' => $attendance->user->name,
                'action' => 'melakukan presensi',
                'location_name' => $attendance->location->name,
                'status' => $attendance->status,
                'created_at' => $attendance->scanned_at,
                'formatted_time' => $attendance->scanned_at->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Get top late employees formatted
     */
    public function getTopLateEmployees(int $days = 7, int $limit = 5): array
    {
        $startDate = Carbon::now()->subDays($days);
        $lateEmployees = $this->dashboardRepository->getTopLateEmployees($startDate, $limit);

        return $lateEmployees->map(function ($employee) {
            return [
                'user_id' => $employee->id,
                'name' => $employee->name,
                'location' => $employee->location_name,
                'late_count' => $employee->late_count,
            ];
        })->toArray();
    }

    /**
     * Get attendance chart data for specified days
     */
    public function getAttendanceChartData(int $days = 7): array
    {
        $chartData = collect();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayAttendances = $this->dashboardRepository->getAttendancesByDate($date);

            $total = $dayAttendances->count();
            $onTime = $dayAttendances->where('status', 'on_time')->count();
            $late = $dayAttendances->where('status', 'late')->count();

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

        return $chartData->toArray();
    }

    /**
     * Get satpam dashboard statistics
     */
    public function getSatpamStats(int $userId): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Today's attendance
        $todayAttendance = $this->dashboardRepository->getUserTodayAttendance($userId);

        // This month's attendance
        $monthAttendances = $this->dashboardRepository->getUserMonthlyAttendances($userId, $thisMonth);
        $monthTotal = $monthAttendances->count();
        $monthOnTime = $monthAttendances->where('status', 'on_time')->count();
        $monthLate = $monthAttendances->where('status', 'late')->count();

        // Expected working days this month
        $workingDays = $this->getWorkingDaysInMonth();

        return [
            'today' => [
                'has_attendance' => $todayAttendance ? true : false,
                'status' => $todayAttendance ? $todayAttendance->status : null,
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
        ];
    }

    /**
     * Get satpam attendance history formatted
     */
    public function getSatpamHistory(int $userId, int $limit = 7): array
    {
        $history = $this->dashboardRepository->getUserAttendanceHistory($userId, $limit);

        return $history->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'date' => $attendance->scanned_at->format('Y-m-d'),
                'formatted_date' => $attendance->scanned_at->format('l, d F Y'),
                'location' => $attendance->location->name,
                'scanned_at' => $attendance->scanned_at->format('H:i'),
                'status' => $attendance->status,
                'status_label' => $attendance->status === 'on_time' ? 'Tepat Waktu' : 'Terlambat',
                'photo_url' => $attendance->photo_url ? asset($attendance->photo_url) : null,
            ];
        })->toArray();
    }

    /**
     * Calculate working days in current month (excluding weekends)
     */
    private function getWorkingDaysInMonth(): int
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
     * Get AI predictions for dashboard
     */
    public function getAIPredictions(int $limit = 6): array
    {
        return $this->aiPredictionService->getCurrentPredictions($limit)->toArray();
    }

    /**
     * Generate new AI predictions
     */
    public function generateAIPredictions(): int
    {
        return $this->aiPredictionService->generatePredictions();
    }
}
