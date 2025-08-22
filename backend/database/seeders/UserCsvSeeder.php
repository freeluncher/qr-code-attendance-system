<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('csv/users.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $this->command->info('Loading users from CSV...');

        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            User::updateOrCreate(
                ['email' => $data['email']], // Check by email
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => $data['password'], // Already hashed in CSV
                    'role' => $data['role'],
                    'photo' => $data['photo'] ?: null,
                    'telegram_chat_id' => $data['telegram_chat_id'] ?: null,
                    'telegram_username' => $data['telegram_username'] ?: null,
                    'telegram_notifications_enabled' => (bool) $data['telegram_notifications_enabled'],
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('Successfully seeded ' . count($csvData) . ' users from CSV.');
    }
}
