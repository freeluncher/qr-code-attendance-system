<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use APp\Models\Location;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Pos' . $this->faker->city(),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(-7.5, -6.5),
            'longitude' => $this->faker->longitude(106.5, 107.5),
        ];
    }
}
