<?php

namespace Database\Seeders;

use App\Models\AiPrediction;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AiPredictionCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('csv/ai_predictions.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $this->command->info('Loading AI predictions from CSV...');

        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            AiPrediction::updateOrCreate(
                [
                    'user_id' => (int) $data['user_id'],
                    'location_id' => (int) $data['location_id'],
                    'predicted_for_date' => Carbon::parse($data['predicted_for_date']),
                ],
                [
                    'risk_score' => (float) $data['risk_score'],
                    'reason' => $data['reason'],
                ]
            );
        }

        $this->command->info('Successfully seeded ' . count($csvData) . ' AI predictions from CSV.');
    }
}
