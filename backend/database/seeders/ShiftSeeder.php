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
        $shifts = [
            [
                'name' => 'Pagi',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
            ],
            [
                'name' => 'Siang',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
            ],
            [
                'name' => 'Malam',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
            ],
            [
                'name' => 'Pagi (07-15)',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
            ],
            [
                'name' => 'Sore (15-23)',
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['name' => $shift['name']],
                $shift
            );
        }
    }
}
