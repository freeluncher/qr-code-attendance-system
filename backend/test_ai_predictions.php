<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Services\AIPredictionService;
use Carbon\Carbon;

try {
    $service = new AIPredictionService();

    echo "=== Testing AI Prediction Service ===\n";
    echo "Current date: " . Carbon::now()->format('Y-m-d H:i') . "\n";
    echo "Next week: " . Carbon::now()->addWeek()->startOfWeek()->format('Y-m-d') . " to " . Carbon::now()->addWeek()->endOfWeek()->format('Y-m-d') . "\n\n";

    // Clean old predictions first
    $cleaned = $service->cleanOldPredictions();
    echo "Cleaned old predictions: {$cleaned}\n";

    // Generate new predictions
    $generated = $service->generatePredictions();
    echo "Generated new predictions: {$generated}\n\n";

    // Get current predictions
    $predictions = $service->getCurrentPredictions();

    echo "=== Top 3 Risky Satpam for Next Week ===\n";
    foreach ($predictions as $prediction) {
        echo "- {$prediction['name']} at {$prediction['location']}\n";
        echo "  Risk Score: {$prediction['riskScore']}%\n";
        echo "  Reason: {$prediction['reason']}\n";
        echo "  Week: {$prediction['predicted_for_week']}\n\n";
    }

    if ($predictions->isEmpty()) {
        echo "No high-risk predictions found.\n";
        echo "This might mean:\n";
        echo "1. No satpam have attendance history in the last 7 days\n";
        echo "2. All satpam have good attendance records\n";
        echo "3. Risk scores are below the threshold (0.2)\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
