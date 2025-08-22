<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('csv/attendances.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $this->command->info('Loading attendances from CSV...');

        $csvData = array_map('str_getcsv', file($csvFile));
        $header = array_shift($csvData); // Remove header row

        foreach ($csvData as $row) {
            // Pad row to match header length
            while (count($row) < count($header)) {
                $row[] = '';
            }

            $data = array_combine($header, $row);

            Attendance::create([
                'user_id' => (int) $data['user_id'],
                'location_id' => (int) $data['location_id'],
                'shift_id' => (int) $data['shift_id'],
                'qr_code_id' => (int) $data['qr_code_id'],
                'scanned_at' => Carbon::parse($data['scanned_at']),
                'check_out_time' => $data['check_out_time'] ? Carbon::parse($data['check_out_time']) : null,
                'status' => $data['status'],
                'late_category' => $data['late_category'] ?: null,
                'photo_url' => $data['photo_url'] ?: null,
                'face_photo_url' => $data['face_photo_url'] ?: null,
                'latitude' => (float) $data['latitude'],
                'longitude' => (float) $data['longitude'],
                'check_out_latitude' => $data['check_out_latitude'] ? (float) $data['check_out_latitude'] : null,
                'check_out_longitude' => $data['check_out_longitude'] ? (float) $data['check_out_longitude'] : null,
                'distance' => $data['distance'] ? (float) $data['distance'] : null,
                'face_descriptor' => $data['face_descriptor'] === 'null' ? null : $data['face_descriptor'],
                'face_quality_status' => $data['face_quality_status'] ?: null,
                'face_validation_message' => $data['face_validation_message'] ?: null,
                'notes' => $data['notes'] ?: null,
            ]);
        }

        $this->command->info('Successfully seeded ' . count($csvData) . ' attendances from CSV.');
    }
}
