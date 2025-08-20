<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceAudit>
 */
class AttendanceAuditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::inRandomOrder()->first()->id ?? Attendance::factory(),
            'action' => $this->faker->randomElement(['scan', 'validate', 'fail']),
            'description' => $this->faker->sentence(),
        ];
    }
}
