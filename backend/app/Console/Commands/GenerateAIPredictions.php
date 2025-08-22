<?php

namespace App\Console\Commands;

use App\Services\AIPredictionService;
use Illuminate\Console\Command;

class GenerateAIPredictions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:generate-predictions
                          {--clean : Clean old predictions before generating new ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI predictions for late attendance risk';

    protected AIPredictionService $aiPredictionService;

    public function __construct(AIPredictionService $aiPredictionService)
    {
        parent::__construct();
        $this->aiPredictionService = $aiPredictionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Starting AI prediction generation...');

        // Clean old predictions if requested
        if ($this->option('clean')) {
            $this->info('🧹 Cleaning old predictions...');
            $cleaned = $this->aiPredictionService->cleanOldPredictions();
            $this->info("✅ Cleaned {$cleaned} old predictions");
        }

        // Show some debug info
        $this->info('📊 Checking data availability...');
        $satpamCount = \App\Models\User::where('role', 'satpam')->count();
        $last7DaysAttendances = \App\Models\Attendance::where('scanned_at', '>=', \Carbon\Carbon::now()->subDays(7))->count();
        $lateAttendances = \App\Models\Attendance::where('scanned_at', '>=', \Carbon\Carbon::now()->subDays(7))
            ->where('status', 'late')->count();

        $this->info("- Total satpam: {$satpamCount}");
        $this->info("- Attendances in last 7 days: {$last7DaysAttendances}");
        $this->info("- Late attendances in last 7 days: {$lateAttendances}");

        // Generate new predictions
        $this->info('🔮 Generating new predictions...');
        $generated = $this->aiPredictionService->generatePredictions();

        if ($generated > 0) {
            $this->info("✅ Successfully generated {$generated} AI predictions!");

            // Show the predictions
            $predictions = $this->aiPredictionService->getCurrentPredictions(5); // Get up to 5 to see if there are more
            $this->info("📋 Top risky users for next week:");
            foreach ($predictions as $index => $prediction) {
                $this->info("  " . ($index + 1) . ". {$prediction['name']} ({$prediction['riskScore']}%): {$prediction['reason']}");
            }

            if ($predictions->count() < 3) {
                $this->warn("⚠️  Only {$predictions->count()} users have sufficient risk scores (>10%)");
            }
        } else {
            $this->warn('⚠️  No predictions were generated (insufficient data or low risk scores)');
        }

        $this->info('🎯 AI prediction generation completed!');
        return Command::SUCCESS;
    }
}
