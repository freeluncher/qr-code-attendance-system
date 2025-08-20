<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class UserCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the CSV file
        $csvPath = base_path('data-dummy-users.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $this->command->info('Loading users from CSV...');

        // Read CSV file
        $csv = array_map('str_getcsv', file($csvPath));
        $header = array_shift($csv); // Remove header row

        DB::beginTransaction();

        try {
            // Clear existing users (optional - remove if you want to keep existing data)
            // DB::table('users')->truncate();

            foreach ($csv as $row) {
                // Combine header with row data
                $data = array_combine($header, $row);

                // Clean up data (remove quotes if any)
                $userData = [
                    'id' => (int) $data['id'],
                    'name' => trim($data['name'], '"'),
                    'email' => trim($data['email'], '"'),
                    'username' => trim($data['username'], '"'),
                    'password' => trim($data['password'], '"'), // Already hashed in CSV
                    'role' => trim($data['role'], '"'),
                    'photo' => trim($data['photo'], '"'),
                    'email_verified_at' => Carbon::parse($data['email_verified_at']),
                    'created_at' => Carbon::parse($data['created_at']),
                    'updated_at' => Carbon::parse($data['updated_at']),
                ];

                DB::table('users')->insert($userData);
            }

            DB::commit();
            $this->command->info('Successfully imported ' . count($csv) . ' users from CSV');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Error importing users: ' . $e->getMessage());
            throw $e;
        }
    }
}
