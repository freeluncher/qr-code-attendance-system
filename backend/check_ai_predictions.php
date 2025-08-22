<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Services\AIPredictionService;
use App\Models\AiPrediction;
use Carbon\Carbon;

try {
    echo "=== AI Predictions Summary ===\n";
    echo "Current date: " . Carbon::now()->format('Y-m-d H:i') . "\n";
    echo "Next week: " . Carbon::now()->addWeek()->startOfWeek()->format('Y-m-d') . " to " . Carbon::now()->addWeek()->endOfWeek()->format('Y-m-d') . "\n\n";

    // Get all predictions from database
    $nextWeekStart = Carbon::now()->addWeek()->startOfWeek();
    $nextWeekEnd = Carbon::now()->addWeek()->endOfWeek();

    $allPredictions = AiPrediction::with(['user', 'location'])
        ->whereBetween('predicted_for_date', [$nextWeekStart->toDateString(), $nextWeekEnd->toDateString()])
        ->orderByDesc('risk_score')
        ->get();

    echo "Total predictions in database: " . $allPredictions->count() . "\n\n";

    // Group by user and show top risk scores
    $userPredictions = $allPredictions->groupBy('user_id')->map(function ($userPreds) {
        return [
            'user' => $userPreds->first()->user,
            'location' => $userPreds->first()->location,
            'max_risk_score' => $userPreds->max('risk_score'),
            'reason' => $userPreds->first()->reason,
            'prediction_count' => $userPreds->count()
        ];
    })->sortByDesc('max_risk_score');

    echo "=== Top Risky Users for Next Week ===\n";
    $counter = 1;
    foreach ($userPredictions->take(3) as $pred) {
        echo "{$counter}. {$pred['user']->name} at {$pred['location']->name}\n";
        echo "   Risk Score: " . round($pred['max_risk_score'] * 100) . "%\n";
        echo "   Reason: {$pred['reason']}\n";
        echo "   Predictions: {$pred['prediction_count']} days\n\n";
        $counter++;
    }

    if ($userPredictions->count() < 3) {
        echo "Note: Only " . $userPredictions->count() . " user(s) have risk scores above the threshold.\n";
    }

    echo "=== AI Model Summary ===\n";
    echo "- Algorithm: Weekly risk assessment based on last 7 days\n";
    echo "- Factors: Late frequency, consecutive late days, weekday patterns\n";
    echo "- Threshold: Risk score > 10%\n";
    echo "- Predictions: Generated for top 3 risky users for next week (7 days each)\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
