<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shifts = [
            ['name' => 'Pagi', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Siang', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['name' => 'Malam', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
        ];
        return $shifts;
    }
}
