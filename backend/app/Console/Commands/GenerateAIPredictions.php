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

        // Generate new predictions
        $this->info('🔮 Generating new predictions...');
        $generated = $this->aiPredictionService->generatePredictions();

        if ($generated > 0) {
            $this->info("✅ Successfully generated {$generated} AI predictions!");
        } else {
            $this->warn('⚠️  No predictions were generated (insufficient data or low risk scores)');
        }

        $this->info('🎯 AI prediction generation completed!');
        return Command::SUCCESS;
    }
}
