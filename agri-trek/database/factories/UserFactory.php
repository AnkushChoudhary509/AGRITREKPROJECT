<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role'     => 'farmer',
        ];
    }

    public function admin():  static { return $this->state(['role' => 'admin']); }
    public function farmer(): static { return $this->state(['role' => 'farmer']); }
}
