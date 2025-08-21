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
        // Generate more realistic risk scores and reasons
        $riskScore = $this->faker->randomFloat(2, 0.3, 1.0); // Only generate risks above 0.3
        $reasons = [
            'Tingkat keterlambatan tinggi (75%)',
            'Sering terlambat (40% dari 20 hari terakhir)',
            'Tren keterlambatan meningkat minggu ini',
            'Sering terlambat di hari Senin',
            'Pola keterlambatan terdeteksi',
            'Tingkat keterlambatan tinggi (60%), Tren keterlambatan meningkat minggu ini'
        ];

        return [
            'user_id' => User::where('role', 'satpam')->inRandomOrder()->first()?->id ??
                        User::factory(['role' => 'satpam']),
            'location_id' => Location::inRandomOrder()->first()?->id ?? Location::factory(),
            'predicted_for_date' => $this->faker->dateTimeBetween('today', '+2 days')->format('Y-m-d'),
            'risk_score' => $riskScore,
            'reason' => $this->faker->randomElement($reasons),
        ];
    }
}
