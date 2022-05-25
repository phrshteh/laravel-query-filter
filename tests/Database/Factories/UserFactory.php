<?php

namespace Omalizadeh\QueryFilter\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Omalizadeh\QueryFilter\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'phone' => $this->faker->phoneNumber,
            'paid_amount' => $this->faker->randomFloat(2, 100, 1000),
            'is_active' => $this->faker->boolean,
        ];
    }
}
