<?php

namespace Database\Seeders;

use App\Models\QrCode;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class QrCodeCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('csv/qr_codes.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $this->command->info('Loading QR codes from CSV...');

        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            QrCode::updateOrCreate(
                ['code' => $data['code']], // Check by code
                [
                    'location_id' => (int) $data['location_id'],
                    'shift_id' => (int) $data['shift_id'],
                    'expires_at' => Carbon::parse($data['expires_at']),
                    'scan_count' => isset($data['scan_count']) ? (int) $data['scan_count'] : 0,
                ]
            );
        }

        $this->command->info('Successfully seeded ' . count($csvData) . ' QR codes from CSV.');
    }
}
