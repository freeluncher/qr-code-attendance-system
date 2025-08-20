<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get admin dashboard statistics
     */
    public function adminStats(Request $request)
    {
        $stats = $this->dashboardService->getAdminStats();
        return response()->json($stats);
    }

    /**
     * Get recent activities for admin dashboard
     */
    public function recentActivities(Request $request)
    {
        $limit = $request->input('limit', 10);
        $activities = $this->dashboardService->getRecentActivities($limit);

        return response()->json([
            'data' => $activities
        ]);
    }

    /**
     * Get top late employees
     */
    public function topLateEmployees(Request $request)
    {
        $days = $request->input('days', 7);
        $limit = $request->input('limit', 5);

        $lateEmployees = $this->dashboardService->getTopLateEmployees($days, $limit);

        return response()->json([
            'data' => $lateEmployees
        ]);
    }

    /**
     * Get attendance chart data for last N days
     */
    public function attendanceChart(Request $request)
    {
        $days = $request->input('days', 7);
        $chartData = $this->dashboardService->getAttendanceChartData($days);

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
        $stats = $this->dashboardService->getSatpamStats($userId);

        return response()->json($stats);
    }

    /**
     * Get satpam recent attendance history
     */
    public function satpamHistory(Request $request)
    {
        $userId = auth()->id();
        $limit = $request->input('limit', 7);

        $history = $this->dashboardService->getSatpamHistory($userId, $limit);

        return response()->json([
            'data' => $history
        ]);
    }
}
