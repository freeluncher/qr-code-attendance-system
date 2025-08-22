<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('csv/shifts.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $this->command->info('Loading shifts from CSV...');

        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            Shift::updateOrCreate(
                ['name' => $data['name']], // Check by name
                [
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                ]
            );
        }

        $this->command->info('Successfully seeded ' . count($csvData) . ' shifts from CSV.');
    }
}
