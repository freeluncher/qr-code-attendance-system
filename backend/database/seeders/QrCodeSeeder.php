<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QrCode;
use App\Models\Location;
use App\Models\Shift;

class QrCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = Location::all();
        $shifts = Shift::all();

        foreach ($locations as $location) {
            foreach ($shifts as $shift) {
                QrCode::factory()->create([
                    'location_id' => $location->id,
                    'shift_id' => $shift->id,
                ]);
            }
        }
    }
}
