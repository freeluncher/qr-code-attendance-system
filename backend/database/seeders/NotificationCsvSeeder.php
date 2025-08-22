<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NotificationCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('csv/notifications.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $this->command->info('Loading notifications from CSV...');

        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            Notification::create([
                'user_id' => (int) $data['user_id'],
                'type' => $data['type'],
                'message' => $data['message'],
                'sent_at' => Carbon::parse($data['sent_at']),
            ]);
        }

        $this->command->info('Successfully seeded ' . count($csvData) . ' notifications from CSV.');
    }
}
