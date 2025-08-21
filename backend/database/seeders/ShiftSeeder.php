<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all locations for location-specific shifts
        $locations = \App\Models\Location::all();
        $posUtama = $locations->where('name', 'like', '%Utama%')->first();
        $posSelatan = $locations->where('name', 'like', '%Selatan%')->first();
        $posBarat = $locations->where('name', 'like', '%Barat%')->first();

        $shifts = [
            // Global shifts (all locations)
            [
                'name' => 'Shift Pagi',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'location_id' => null, // Available for all locations
                'active_days' => [1,2,3,4,5], // Mon-Fri
                'capacity' => 2,
                'status' => 'active',
                'description' => 'Shift pagi standar untuk hari kerja'
            ],
            [
                'name' => 'Shift Siang',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'location_id' => null,
                'active_days' => [1,2,3,4,5],
                'capacity' => 2,
                'status' => 'active',
                'description' => 'Shift siang standar untuk hari kerja'
            ],
            [
                'name' => 'Shift Malam',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'location_id' => null,
                'active_days' => [1,2,3,4,5],
                'capacity' => 1,
                'status' => 'active',
                'description' => 'Shift malam standar untuk hari kerja (overnight)'
            ],
            [
                'name' => 'Shift Weekend',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'location_id' => null,
                'active_days' => [6,7], // Sat-Sun
                'capacity' => 1,
                'status' => 'active',
                'description' => 'Shift weekend standar'
            ],

            // Location-specific shifts
            [
                'name' => 'Pos Utama - 24/7 Pagi',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'location_id' => $posUtama?->id,
                'active_days' => [1,2,3,4,5,6,7], // All week
                'capacity' => 3,
                'status' => 'active',
                'description' => 'Shift pagi khusus Pos Utama (24/7 operation)'
            ],
            [
                'name' => 'Pos Utama - 24/7 Siang',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'location_id' => $posUtama?->id,
                'active_days' => [1,2,3,4,5,6,7],
                'capacity' => 3,
                'status' => 'active',
                'description' => 'Shift siang khusus Pos Utama (24/7 operation)'
            ],
            [
                'name' => 'Pos Utama - 24/7 Malam',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'location_id' => $posUtama?->id,
                'active_days' => [1,2,3,4,5,6,7],
                'capacity' => 2,
                'status' => 'active',
                'description' => 'Shift malam khusus Pos Utama (24/7 operation - overnight)'
            ],
            [
                'name' => 'Pos Selatan - Extended',
                'start_time' => '07:00:00',
                'end_time' => '19:00:00',
                'location_id' => $posSelatan?->id,
                'active_days' => [1,2,3,4,5], // Mon-Fri only
                'capacity' => 2,
                'status' => 'active',
                'description' => 'Shift panjang 12 jam untuk Pos Selatan (hari kerja)'
            ],
            [
                'name' => 'Pos Barat - Weekend Only',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
                'location_id' => $posBarat?->id,
                'active_days' => [6,7], // Sat-Sun only
                'capacity' => 1,
                'status' => 'active',
                'description' => 'Shift khusus weekend untuk Pos Barat'
            ]
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                [
                    'name' => $shift['name'],
                    'location_id' => $shift['location_id']
                ],
                $shift
            );
        }
    }
}
