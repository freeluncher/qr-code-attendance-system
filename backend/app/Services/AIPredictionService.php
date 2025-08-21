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
     * Generate AI predictions for all users based on historical attendance data
     */
    public function generatePredictions(): int
    {
        $predictionsGenerated = 0;
        $users = User::where('role', 'satpam')->get();
        $tomorrow = Carbon::tomorrow();

        foreach ($users as $user) {
            // Get user's locations from recent attendance
            $userLocations = $this->getUserActiveLocations($user->id);

            foreach ($userLocations as $location) {
                $riskScore = $this->calculateLateRisk($user->id, $location->id);
                $reason = $this->generateRiskReason($user->id, $location->id);

                // Only create prediction if risk score is significant (> 0.3)
                if ($riskScore > 0.3) {
                    AiPrediction::updateOrCreate([
                        'user_id' => $user->id,
                        'location_id' => $location->id,
                        'predicted_for_date' => $tomorrow->toDateString(),
                    ], [
                        'risk_score' => $riskScore,
                        'reason' => $reason,
                    ]);

                    $predictionsGenerated++;
                }
            }
        }

        Log::info("Generated {$predictionsGenerated} AI predictions for {$tomorrow->toDateString()}");
        return $predictionsGenerated;
    }

    /**
     * Get current AI predictions for dashboard
     */
    public function getCurrentPredictions(int $limit = 10): Collection
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        return AiPrediction::with(['user', 'location'])
            ->whereIn('predicted_for_date', [$today->toDateString(), $tomorrow->toDateString()])
            ->orderByDesc('risk_score')
            ->limit($limit)
            ->get()
            ->map(function ($prediction) {
                return [
                    'id' => $prediction->id,
                    'name' => $prediction->user->name,
                    'location' => $prediction->location->name,
                    'riskScore' => round($prediction->risk_score * 100), // Convert to percentage
                    'reason' => $prediction->reason,
                    'predicted_for_date' => $prediction->predicted_for_date,
                    'user_id' => $prediction->user_id,
                    'location_id' => $prediction->location_id,
                ];
            });
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
        $lateAttendances = $attendances->where('late_category', '!=', 'tepat_waktu')->count();

        // Base late ratio
        $lateRatio = $totalAttendances > 0 ? $lateAttendances / $totalAttendances : 0;

        // Recent trend analysis (last 7 days weighted more)
        $recentAttendances = $attendances->where('scanned_at', '>=', Carbon::now()->subWeek());
        $recentLateRatio = 0;

        if ($recentAttendances->count() > 0) {
            $recentLates = $recentAttendances->where('late_category', '!=', 'tepat_waktu')->count();
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
            $sameDayLates = $sameDayAttendances->where('late_category', '!=', 'tepat_waktu')->count();
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
        $lateAttendances = $attendances->where('late_category', '!=', 'tepat_waktu')->count();
        $latePercentage = round(($lateAttendances / $totalAttendances) * 100);

        // Recent pattern
        $recentAttendances = $attendances->where('scanned_at', '>=', Carbon::now()->subWeek());
        $recentLates = $recentAttendances->where('late_category', '!=', 'tepat_waktu')->count();

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
            $sameDayLates = $sameDayAttendances->where('late_category', '!=', 'tepat_waktu')->count();
            $sameDayLateRatio = $sameDayLates / $sameDayAttendances->count();

            if ($sameDayLateRatio > 0.5) {
                $reasons[] = "Sering terlambat di hari {$dayName}";
            }
        }

        return !empty($reasons) ? implode(', ', $reasons) : "Pola keterlambatan terdeteksi";
    }

    /**
     * Clean old predictions (keep only last 7 days)
     */
    public function cleanOldPredictions(): int
    {
        $cutoffDate = Carbon::now()->subWeek();

        $deleted = AiPrediction::where('predicted_for_date', '<', $cutoffDate->toDateString())
            ->delete();

        Log::info("Cleaned {$deleted} old AI predictions");
        return $deleted;
    }
}
