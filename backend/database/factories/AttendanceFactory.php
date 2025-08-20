<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $location = Location::inRandomOrder()->first();
        $shift = Shift::inRandomOrder()->first();

        return [
            'user_id' => User::where('role', 'satpam')->inRandomOrder()->first()->id ?? User::factory(),
            'location_id' => $location->id ?? Location::factory(),
            'shift_id' => $shift->id ?? Shift::factory(),
            'qr_code_id' => QrCode::inRandomOrder()->first()->id ?? QrCode::factory(),
            'scanned_at' => now()->subMinutes(rand(0,60)),
            'status' => $this->faker->randomElement(['on_time', 'late', 'absent']),
            'late_category' => $this->faker->optional()->randomElement(['Transportasi', 'Kesehatan', '15_minutes', 'Keluarga']),
            'photo_url' => $this->faker->imageUrl(),
            'latitude' => $location->latitude ?? $this->faker->latitude,
            'longitude' => $location->longitude ?? $this->faker->longitude,
            'distance' => $this->faker->randomFloat(2, 0, 50),
        ];
    }
}
