<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Location;
use App\Models\Shift;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QrCode>
 */
class QrCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::inRandomOrder()->first()->id ?? Location::factory(),
            'shift_id' => Shift::inRandomOrder()->first()->id ?? Shift::factory(),
            'code' => Str::uuid(),
            'expires_at' => now()->addHour(),
        ];
    }
}
