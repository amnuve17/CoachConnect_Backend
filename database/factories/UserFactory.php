<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        $genders = ['male', 'female', 'other'];
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // oppure bcrypt('password')
            'role' => $this->faker->randomElement(['trainer', 'client']),
            'phone_number' => $this->faker->numerify('3#########'),
            'gender' => $this->faker->randomElement($genders),
            'birthdate' => $this->faker->date('Y-m-d', '2004-12-31'), // max 20 anni
            'remember_token' => Str::random(10),
        ];
    }
}
