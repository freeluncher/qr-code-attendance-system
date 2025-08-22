<?php

namespace App\Services;

use App\Models\AiPrediction;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIPredictionService
{
    /**
     * Generate AI predictions for top 3 satpam at risk for next week based on last 7 days
     */
    public function generatePredictions(): int
    {
        $predictionsGenerated = 0;
        $users = User::where('role', 'satpam')->get();
        $nextWeekStart = Carbon::now()->addWeek()->startOfWeek();
        $nextWeekEnd = Carbon::now()->addWeek()->endOfWeek();

        // Calculate risk scores for all users
        $userRisks = collect();

        foreach ($users as $user) {
            $userLocations = $this->getUserActiveLocations($user->id);

            foreach ($userLocations as $location) {
                $riskScore = $this->calculateWeeklyLateRisk($user->id, $location->id);

                if ($riskScore > 0.1) { // Lower threshold for weekly predictions
                    $userRisks->push([
                        'user_id' => $user->id,
                        'location_id' => $location->id,
                        'risk_score' => $riskScore,
                        'user' => $user,
                        'location' => $location
                    ]);
                }
            }
        }

        // Sort by risk score and take top 3
        $topRiskyUsers = $userRisks->sortByDesc('risk_score')->take(3);

        // Generate predictions for each day of next week for top 3 users
        foreach ($topRiskyUsers as $userRisk) {
            for ($day = 0; $day < 7; $day++) {
                $predictedDate = $nextWeekStart->copy()->addDays($day);
                $reason = $this->generateWeeklyRiskReason($userRisk['user_id'], $userRisk['location_id']);

                AiPrediction::updateOrCreate([
                    'user_id' => $userRisk['user_id'],
                    'location_id' => $userRisk['location_id'],
                    'predicted_for_date' => $predictedDate->toDateString(),
                ], [
                    'risk_score' => $userRisk['risk_score'],
                    'reason' => $reason,
                ]);

                $predictionsGenerated++;
            }
        }

        Log::info("Generated {$predictionsGenerated} weekly AI predictions for top 3 risky users");
        return $predictionsGenerated;
    }

    /**
     * Get current AI predictions for dashboard (top 3 risky users for next week)
     */
    public function getCurrentPredictions(int $limit = 3): Collection
    {
        $nextWeekStart = Carbon::now()->addWeek()->startOfWeek();
        $nextWeekEnd = Carbon::now()->addWeek()->endOfWeek();

        // Get unique users with highest risk scores for next week
        $predictions = AiPrediction::with(['user', 'location'])
            ->whereBetween('predicted_for_date', [$nextWeekStart->toDateString(), $nextWeekEnd->toDateString()])
            ->orderByDesc('risk_score')
            ->get()
            ->groupBy('user_id') // Group by user to avoid duplicates
            ->map(function ($userPredictions) {
                // Take the highest risk score for each user
                return $userPredictions->sortByDesc('risk_score')->first();
            })
            ->sortByDesc('risk_score')
            ->take($limit)
            ->values() // Reset array keys to 0,1,2...
            ->map(function ($prediction) {
                return [
                    'id' => $prediction->id,
                    'name' => $prediction->user->name,
                    'location' => $prediction->location->name,
                    'riskScore' => round($prediction->risk_score * 100), // Convert to percentage
                    'reason' => $prediction->reason,
                    'predicted_for_week' => Carbon::parse($prediction->predicted_for_date)->startOfWeek()->format('d M')
                        . ' - ' . Carbon::parse($prediction->predicted_for_date)->endOfWeek()->format('d M Y'),
                    'user_id' => $prediction->user_id,
                    'location_id' => $prediction->location_id,
                ];
            });        return $predictions;
    }

    /**
     * Calculate late risk score for next week based on last 7 days pattern (0-1 scale)
     */
    private function calculateWeeklyLateRisk(int $userId, int $locationId): float
    {
        $last7Days = Carbon::now()->subDays(7);

        // Get attendance history for last 7 days for this user and location
        $attendances = Attendance::where('user_id', $userId)
            ->where('location_id', $locationId)
            ->where('scanned_at', '>=', $last7Days)
            ->get();

        if ($attendances->isEmpty()) {
            return 0.0; // No recent history, no risk
        }

        $totalAttendances = $attendances->count();
        $lateAttendances = $attendances->where('status', 'late')->count();

        // Base late ratio from last 7 days
        $lateRatio = $totalAttendances > 0 ? $lateAttendances / $totalAttendances : 0;

        // Trend analysis: are they getting worse?
        $last3Days = $attendances->where('scanned_at', '>=', Carbon::now()->subDays(3));
        $recentLateRatio = 0;

        if ($last3Days->count() > 0) {
            $recentLates = $last3Days->where('status', 'late')->count();
            $recentLateRatio = $recentLates / $last3Days->count();
        }

        // Consecutive late days pattern
        $consecutiveLateBonus = 0;
        $attendancesSorted = $attendances->sortByDesc('scanned_at');
        $consecutiveLate = 0;

        foreach ($attendancesSorted as $attendance) {
            if ($attendance->status === 'late') {
                $consecutiveLate++;
            } else {
                break;
            }
        }

        if ($consecutiveLate >= 2) {
            $consecutiveLateBonus = min(0.3, $consecutiveLate * 0.1); // Max 0.3 bonus
        }

        // Day frequency - more attendances means more reliable pattern
        $frequencyMultiplier = min(1.0, $totalAttendances / 5); // Max reliability at 5+ days

        // Weighted risk calculation focusing on recent trend
        $riskScore = (
            $lateRatio * 0.5 +              // 50% overall 7-day ratio
            $recentLateRatio * 0.3 +        // 30% last 3 days trend
            $consecutiveLateBonus           // Bonus for consecutive lates
        ) * $frequencyMultiplier;

        return min(1.0, max(0.0, $riskScore)); // Ensure 0-1 range
    }

    /**
     * Calculate late risk score for a user at specific location (0-1 scale)
     */
    private function calculateLateRisk(int $userId, int $locationId): float
    {
        $daysToAnalyze = 30; // Analyze last 30 days
        $startDate = Carbon::now()->subDays($daysToAnalyze);

        // Get attendance history for this user and location
        $attendances = Attendance::where('user_id', $userId)
            ->where('location_id', $locationId)
            ->where('scanned_at', '>=', $startDate)
            ->get();

        if ($attendances->isEmpty()) {
            return 0.0; // No history, no risk
        }

        $totalAttendances = $attendances->count();
        $lateAttendances = $attendances->where('status', 'late')->count();

        // Base late ratio
        $lateRatio = $totalAttendances > 0 ? $lateAttendances / $totalAttendances : 0;

        // Recent trend analysis (last 7 days weighted more)
        $recentAttendances = $attendances->where('scanned_at', '>=', Carbon::now()->subWeek());
        $recentLateRatio = 0;

        if ($recentAttendances->count() > 0) {
            $recentLates = $recentAttendances->where('status', 'late')->count();
            $recentLateRatio = $recentLates / $recentAttendances->count();
        }

        // Day of week pattern analysis
        $tomorrow = Carbon::tomorrow();
        $dayOfWeek = $tomorrow->dayOfWeek; // 0 = Sunday, 6 = Saturday

        $sameDayAttendances = $attendances->filter(function ($attendance) use ($dayOfWeek) {
            return Carbon::parse($attendance->scanned_at)->dayOfWeek === $dayOfWeek;
        });

        $sameDayLateRatio = 0;
        if ($sameDayAttendances->count() > 0) {
            $sameDayLates = $sameDayAttendances->where('status', 'late')->count();
            $sameDayLateRatio = $sameDayLates / $sameDayAttendances->count();
        }

        // Weighted risk calculation
        $riskScore = (
            $lateRatio * 0.4 +           // 40% overall history
            $recentLateRatio * 0.4 +     // 40% recent trend
            $sameDayLateRatio * 0.2      // 20% same day pattern
        );

        // Apply frequency penalty (more attendances = more reliable data)
        $frequencyFactor = min(1.0, $totalAttendances / 10); // Max reliability at 10+ attendances
        $riskScore *= $frequencyFactor;

        return min(1.0, max(0.0, $riskScore)); // Ensure 0-1 range
    }

    /**
     * Get active locations for a user based on recent attendance
     */
    private function getUserActiveLocations(int $userId): Collection
    {
        $recentDays = 14;

        return Location::whereHas('attendances', function ($query) use ($userId, $recentDays) {
            $query->where('user_id', $userId)
                  ->where('scanned_at', '>=', Carbon::now()->subDays($recentDays));
        })->get();
    }

    /**
     * Generate human-readable reason for weekly risk prediction based on last 7 days
     */
    private function generateWeeklyRiskReason(int $userId, int $locationId): string
    {
        $last7Days = Carbon::now()->subDays(7);

        $attendances = Attendance::where('user_id', $userId)
            ->where('location_id', $locationId)
            ->where('scanned_at', '>=', $last7Days)
            ->get();

        if ($attendances->isEmpty()) {
            return 'Tidak ada data riwayat 7 hari terakhir';
        }

        $totalAttendances = $attendances->count();
        $lateAttendances = $attendances->where('status', 'late')->count();
        $latePercentage = round(($lateAttendances / $totalAttendances) * 100);

        // Check for consecutive late days
        $attendancesSorted = $attendances->sortByDesc('scanned_at');
        $consecutiveLate = 0;

        foreach ($attendancesSorted as $attendance) {
            if ($attendance->status === 'late') {
                $consecutiveLate++;
            } else {
                break;
            }
        }

        $reasons = [];

        // High late percentage
        if ($latePercentage >= 60) {
            $reasons[] = "Sangat sering terlambat ({$latePercentage}% dari {$totalAttendances} hari)";
        } elseif ($latePercentage >= 40) {
            $reasons[] = "Sering terlambat ({$latePercentage}% dari {$totalAttendances} hari)";
        } elseif ($latePercentage >= 20) {
            $reasons[] = "Cenderung terlambat ({$latePercentage}% dari {$totalAttendances} hari)";
        }

        // Consecutive late pattern
        if ($consecutiveLate >= 3) {
            $reasons[] = "Terlambat {$consecutiveLate} hari berturut-turut";
        } elseif ($consecutiveLate >= 2) {
            $reasons[] = "Terlambat 2 hari berturut-turut";
        }

        // Recent trend (last 3 days)
        $last3Days = $attendances->where('scanned_at', '>=', Carbon::now()->subDays(3));
        if ($last3Days->count() >= 2) {
            $recentLates = $last3Days->where('status', 'late')->count();
            if ($recentLates == $last3Days->count()) {
                $reasons[] = "Semua presensi 3 hari terakhir terlambat";
            } elseif ($recentLates >= $last3Days->count() * 0.7) {
                $reasons[] = "Mayoritas terlambat dalam 3 hari terakhir";
            }
        }

        // Pattern analysis
        $weekdayLates = $attendances->filter(function ($attendance) {
            $dayOfWeek = Carbon::parse($attendance->scanned_at)->dayOfWeek;
            return $dayOfWeek >= 1 && $dayOfWeek <= 5; // Monday to Friday
        })->where('status', 'late')->count();

        $totalWeekdays = $attendances->filter(function ($attendance) {
            $dayOfWeek = Carbon::parse($attendance->scanned_at)->dayOfWeek;
            return $dayOfWeek >= 1 && $dayOfWeek <= 5;
        })->count();

        if ($totalWeekdays > 0 && ($weekdayLates / $totalWeekdays) > 0.5) {
            $reasons[] = "Sering terlambat di hari kerja";
        }

        if (empty($reasons)) {
            $reasons[] = "Pola keterlambatan ringan terdeteksi";
        }

        return implode(', ', array_slice($reasons, 0, 2)); // Max 2 reasons untuk readability
    }

    /**
     * Generate human-readable reason for risk prediction
     */
    private function generateRiskReason(int $userId, int $locationId): string
    {
        $daysToAnalyze = 14;
        $startDate = Carbon::now()->subDays($daysToAnalyze);

        $attendances = Attendance::where('user_id', $userId)
            ->where('location_id', $locationId)
            ->where('scanned_at', '>=', $startDate)
            ->get();

        if ($attendances->isEmpty()) {
            return 'Data riwayat tidak cukup';
        }

        $totalAttendances = $attendances->count();
        $lateAttendances = $attendances->where('status', 'late')->count();
        $latePercentage = round(($lateAttendances / $totalAttendances) * 100);

        // Recent pattern
        $recentAttendances = $attendances->where('scanned_at', '>=', Carbon::now()->subWeek());
        $recentLates = $recentAttendances->where('status', 'late')->count();

        $reasons = [];

        if ($latePercentage > 50) {
            $reasons[] = "Tingkat keterlambatan tinggi ({$latePercentage}%)";
        } elseif ($latePercentage > 30) {
            $reasons[] = "Sering terlambat ({$latePercentage}% dari {$totalAttendances} hari terakhir)";
        }

        if ($recentLates >= 2 && $recentAttendances->count() <= 5) {
            $reasons[] = "Tren keterlambatan meningkat minggu ini";
        }

        // Day pattern analysis
        $tomorrow = Carbon::tomorrow();
        $dayName = $tomorrow->isoFormat('dddd');
        $sameDayAttendances = $attendances->filter(function ($attendance) use ($tomorrow) {
            return Carbon::parse($attendance->scanned_at)->dayOfWeek === $tomorrow->dayOfWeek;
        });

        if ($sameDayAttendances->count() >= 2) {
            $sameDayLates = $sameDayAttendances->where('status', 'late')->count();
            $sameDayLateRatio = $sameDayLates / $sameDayAttendances->count();

            if ($sameDayLateRatio > 0.5) {
                $reasons[] = "Sering terlambat di hari {$dayName}";
            }
        }

        return !empty($reasons) ? implode(', ', $reasons) : "Pola keterlambatan terdeteksi";
    }

    /**
     * Clean old predictions (keep current week and next week predictions only)
     */
    public function cleanOldPredictions(): int
    {
        $currentWeekStart = Carbon::now()->startOfWeek();

        $deleted = AiPrediction::where('predicted_for_date', '<', $currentWeekStart->toDateString())
            ->delete();

        Log::info("Cleaned {$deleted} old AI predictions before {$currentWeekStart->toDateString()}");
        return $deleted;
    }
}
