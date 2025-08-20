<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Location;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();
        return [
            'location_id' => Location::inRandomOrder()->first()->id ?? Location::factory(),
            'week_start' => $start,
            'week_end' => $end,
            'file_url' => $this->faker->url(),
        ];
    }
}

