<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    // /**
    //  * The current password being used by the factory.
    //  */
    // protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = $this->faker->randomElement(['admin', 'satpam']);
        return [
            'name' => $role === 'admin' ? 'Admin' . $this->faker->firstName : 'Pak Satpam' . $this->faker->firstName,
            'email' => $this->faker->unique()->safeEmail(),
            'username' => $role === 'admin' ? 'admin' . Str::random(4) : 'satpam' . $this->faker->unique()->numerify('###'),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Use a default password for testing
            'role' => $role,
            'photo' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
