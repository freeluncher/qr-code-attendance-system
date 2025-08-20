<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Location;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiPrediction>
 */
class AiPredictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'satpam')->inRandomOrder()->first()->id ?? User::factory(),
            'location_id' => Location::inRandomOrder()->first()->id ?? Location::factory(),
            'predicted_for_date' => now()->addWeek(),
            'risk_score' => $this->faker->randomFloat(2,0,1),
            'reason' => $this->faker->optional()->sentence(),
        ];
    }
}
