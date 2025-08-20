<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Location;
use App\Models\QrCode;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    /**
     * Get total count of satpam users
     */
    public function getTotalSatpamCount(): int
    {
        return User::where('role', 'satpam')->count();
    }

    /**
     * Get total count of locations
     */
    public function getTotalLocationsCount(): int
    {
        return Location::count();
    }

    /**
     * Get count of active QR codes (not expired)
     */
    public function getActiveQrCodesCount(): int
    {
        return QrCode::where('expires_at', '>', Carbon::now())->count();
    }

    /**
     * Get today's attendances
     */
    public function getTodayAttendances()
    {
        return Attendance::whereDate('created_at', Carbon::today())->get();
    }

    /**
     * Get attendances for a specific date range
     */
    public function getAttendancesByDateRange(Carbon $startDate, ?Carbon $endDate = null)
    {
        $query = Attendance::where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get attendance count by date range
     */
    public function getAttendanceCountByDateRange(Carbon $startDate, ?Carbon $endDate = null): int
    {
        $query = Attendance::where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->count();
    }

    /**
     * Get recent activities with relationships
     */
    public function getRecentActivities(int $limit = 10)
    {
        return Attendance::with(['user', 'location'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top late employees for a date range
     */
    public function getTopLateEmployees(Carbon $startDate, int $limit = 5)
    {
        return DB::table('attendances')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as late_count'),
                DB::raw('MAX(locations.name) as location_name')
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
    }

    /**
     * Get attendances by date for chart data
     */
    public function getAttendancesByDate(Carbon $date)
    {
        return Attendance::whereDate('created_at', $date->toDateString())->get();
    }

    /**
     * Get user's today attendance
     */
    public function getUserTodayAttendance(int $userId)
    {
        return Attendance::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->first();
    }

    /**
     * Get user's monthly attendances
     */
    public function getUserMonthlyAttendances(int $userId, Carbon $startOfMonth)
    {
        return Attendance::where('user_id', $userId)
            ->where('created_at', '>=', $startOfMonth)
            ->get();
    }

    /**
     * Get user's recent attendance history
     */
    public function getUserAttendanceHistory(int $userId, int $limit = 7)
    {
        return Attendance::with(['location'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
