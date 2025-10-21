<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscriber>
 */
class SubscriberFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'apartment_number' => $this->faker->numberBetween(1, 999) . $this->faker->randomElement(['', 'A', 'B', 'C']),
            'building_number' => $this->faker->numberBetween(1, 50),
            'status' => $this->faker->randomElement(['active', 'bad']),
            'balance' => $this->faker->numberBetween(-5000, 5000),
            'registration_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the subscriber is active.
     */
    public function active(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'balance' => $this->faker->numberBetween(0, 5000),
        ]);
    }

    /**
     * Indicate that the subscriber is bad.
     */
    public function bad(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'bad',
            'balance' => $this->faker->numberBetween(-5000, 0),
        ]);
    }

    /**
     * Indicate that the subscriber has positive balance.
     */
    public function positiveBalance(): static {
        return $this->state(fn(array $attributes) => [
            'balance' => $this->faker->numberBetween(1, 10000),
        ]);
    }

    /**
     * Indicate that the subscriber has negative balance.
     */
    public function negativeBalance(): static {
        return $this->state(fn(array $attributes) => [
            'balance' => $this->faker->numberBetween(-10000, -1),
        ]);
    }
}