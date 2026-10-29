<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => 4, // Patron / Student
            'status_id' => 1, // Active
            'username' => 'student_' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(12)),
            'email' => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(12)) . '@example.test',
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'password_hash' => static::$password ??= \Illuminate\Support\Facades\Hash::make('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'is_email_verified' => false,
        ]);
    }
}
