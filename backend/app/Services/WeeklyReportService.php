<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class WeeklyReportService
{
    /**
     * Generate weekly report data for a specific location and week
     */
    public function generateWeeklyReportData(Location $location, Carbon $weekStart): array
    {
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get all users who have attendance at this location during the week
        $users = User::whereHas('attendances', function ($query) use ($location, $weekStart, $weekEnd) {
            $query->where('location_id', $location->id)
                  ->whereBetween('scanned_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()]);
        })->with(['attendances' => function ($query) use ($location, $weekStart, $weekEnd) {
            $query->where('location_id', $location->id)
                  ->whereBetween('scanned_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                  ->with('shift');
        }])->get();

        $reportData = [
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address
            ],
            'week_period' => [
                'start' => $weekStart->toDateString(),
                'end' => $weekEnd->toDateString(),
                'week_number' => $weekStart->week,
                'year' => $weekStart->year
            ],
            'attendances' => [],
            'summary' => [
                'total_employees' => $users->count(),
                'on_time' => 0,
                'late' => 0,
                'absent' => 0,
                'total_working_days' => 0
            ]
        ];

        foreach ($users as $user) {
            $userAttendances = $this->getUserWeeklyAttendances($user, $location, $weekStart, $weekEnd);
            $reportData['attendances'][] = $userAttendances;

            // Update summary
            foreach ($userAttendances['daily_status'] as $dailyStatus) {
                if ($dailyStatus['status'] === 'On-Time') {
                    $reportData['summary']['on_time']++;
                } elseif ($dailyStatus['status'] === 'Late') {
                    $reportData['summary']['late']++;
                } elseif ($dailyStatus['status'] === 'Absent') {
                    $reportData['summary']['absent']++;
                }
            }
        }

        // Calculate working days (excluding weekends for now)
        $reportData['summary']['total_working_days'] = $this->getWorkingDaysInWeek($weekStart);

        return $reportData;
    }

    /**
     * Get user's weekly attendance data
     */
    private function getUserWeeklyAttendances(User $user, Location $location, Carbon $weekStart, Carbon $weekEnd): array
    {
        $attendances = Attendance::where('user_id', $user->id)
            ->where('location_id', $location->id)
            ->whereBetween('scanned_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
            ->with('shift')
            ->orderBy('scanned_at')
            ->get();

        $userData = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'daily_status' => []
        ];

        // Check each day of the week
        for ($date = $weekStart->copy(); $date->lte($weekEnd); $date->addDay()) {
            $dayAttendance = $attendances->filter(function ($attendance) use ($date) {
                return $attendance->scanned_at->isSameDay($date);
            })->first();

            $status = $this->calculateDayStatus($user, $location, $date, $dayAttendance);

            $userData['daily_status'][] = [
                'date' => $date->toDateString(),
                'day_name' => $date->format('l'),
                'scan_time' => $dayAttendance ? $dayAttendance->scanned_at->format('H:i:s') : null,
                'checkout_time' => $dayAttendance && $dayAttendance->check_out_time ?
                    $dayAttendance->check_out_time->format('H:i:s') : null,
                'status' => $status,
                'shift_name' => $dayAttendance && $dayAttendance->shift ?
                    $dayAttendance->shift->name : 'No Shift',
                'late_minutes' => $status === 'Late' && $dayAttendance ?
                    $this->calculateLateMinutes($dayAttendance) : 0
            ];
        }

        return $userData;
    }

    /**
     * Calculate attendance status for a specific day
     */
    private function calculateDayStatus(User $user, Location $location, Carbon $date, ?Attendance $attendance): string
    {
        // If weekend or no shift, skip
        if ($date->isWeekend()) {
            return 'Weekend';
        }

        if (!$attendance) {
            return 'Absent';
        }

        if (!$attendance->shift) {
            return 'No Shift';
        }

        // Calculate if late (more than 30 minutes after shift start)
        $shiftStartTime = Carbon::parse($attendance->shift->start_time);
        $scanTime = $attendance->scanned_at->format('H:i:s');
        $scanTimeCarbon = Carbon::createFromTimeString($scanTime);

        $lateThresholdMinutes = 30;
        $allowedLatestTime = $shiftStartTime->addMinutes($lateThresholdMinutes);

        if ($scanTimeCarbon->gt($allowedLatestTime)) {
            return 'Late';
        }

        return 'On-Time';
    }

    /**
     * Calculate how many minutes late
     */
    private function calculateLateMinutes(Attendance $attendance): int
    {
        if (!$attendance->shift) {
            return 0;
        }

        $shiftStartTime = Carbon::parse($attendance->shift->start_time);
        $scanTime = Carbon::createFromTimeString($attendance->scanned_at->format('H:i:s'));

        if ($scanTime->gt($shiftStartTime)) {
            return $scanTime->diffInMinutes($shiftStartTime);
        }

        return 0;
    }

    /**
     * Get working days count in a week
     */
    private function getWorkingDaysInWeek(Carbon $weekStart): int
    {
        $workingDays = 0;
        for ($date = $weekStart->copy(); $date->lte($weekStart->copy()->endOfWeek()); $date->addDay()) {
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }
        return $workingDays;
    }

    /**
     * Export weekly report to CSV
     */
    public function exportToCSV(array $reportData): string
    {
        $filename = sprintf(
            'weekly_report_%s_%s_to_%s.csv',
            str_replace(' ', '_', strtolower($reportData['location']['name'])),
            $reportData['week_period']['start'],
            $reportData['week_period']['end']
        );

        $filePath = 'reports/weekly/' . $filename;

        // Debug: Log file path
        \Log::info("Creating CSV file at: " . $filePath);

        $csvContent = $this->generateCSVContent($reportData);

        // Ensure directory exists
        $directory = dirname(storage_path('app/' . $filePath));
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        Storage::disk('local')->put($filePath, $csvContent);

        // Debug: Check if file was created
        if (Storage::disk('local')->exists($filePath)) {
            \Log::info("CSV file created successfully: " . $filePath);
        } else {
            \Log::error("Failed to create CSV file: " . $filePath);
        }

        return $filePath;
    }

    /**
     * Generate CSV content from report data
     */
    private function generateCSVContent(array $reportData): string
    {
        $csv = [];

        // Header
        $csv[] = "Weekly Attendance Report";
        $csv[] = "Location: " . $reportData['location']['name'];
        $csv[] = "Address: " . $reportData['location']['address'];
        $csv[] = "Week Period: " . $reportData['week_period']['start'] . ' to ' . $reportData['week_period']['end'];
        $csv[] = "";

        // Summary
        $csv[] = "SUMMARY";
        $csv[] = "Total Employees," . $reportData['summary']['total_employees'];
        $csv[] = "On-Time Attendance," . $reportData['summary']['on_time'];
        $csv[] = "Late Attendance," . $reportData['summary']['late'];
        $csv[] = "Absent," . $reportData['summary']['absent'];
        $csv[] = "";

        // Detailed attendance data
        $csv[] = "DETAILED ATTENDANCE";
        $csv[] = "Name,Email,Date,Day,Scan Time,Checkout Time,Status,Shift,Late Minutes";

        foreach ($reportData['attendances'] as $userAttendance) {
            foreach ($userAttendance['daily_status'] as $dailyStatus) {
                $csv[] = sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s",%d',
                    $userAttendance['name'],
                    $userAttendance['email'],
                    $dailyStatus['date'],
                    $dailyStatus['day_name'],
                    $dailyStatus['scan_time'] ?? '',
                    $dailyStatus['checkout_time'] ?? '',
                    $dailyStatus['status'],
                    $dailyStatus['shift_name'],
                    $dailyStatus['late_minutes']
                );
            }
        }

        return implode("\n", $csv);
    }

    /**
     * Create or update weekly report record
     */
    public function createWeeklyReport(Location $location, Carbon $weekStart, ?int $createdBy = null): WeeklyReport
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        $reportData = $this->generateWeeklyReportData($location, $weekStart);

        $filePath = $this->exportToCSV($reportData);

        return WeeklyReport::updateOrCreate(
            [
                'location_id' => $location->id,
                'week_start_date' => $weekStart->toDateString(),
            ],
            [
                'week_end_date' => $weekEnd->toDateString(),
                'report_data' => $reportData,
                'file_path' => $filePath,
                'created_by' => $createdBy,
            ]
        );
    }

    /**
     * Get all locations for weekly report generation
     */
    public function getAllActiveLocations(): Collection
    {
        return Location::all(); // Remove is_active filter since column doesn't exist
    }
}
