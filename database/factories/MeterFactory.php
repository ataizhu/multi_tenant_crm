<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meter>
 */
class MeterFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'subscriber_id' => \App\Models\Subscriber::factory(),
            'number' => $this->faker->unique()->numerify('METER-####'),
            'type' => $this->faker->randomElement(['water', 'electricity', 'gas', 'heating']),
            'model' => $this->faker->word() . ' ' . $this->faker->numberBetween(100, 9999),
            'installation_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'last_reading' => $this->faker->numberBetween(1000, 99999),
            'last_reading_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'broken', 'replaced']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the meter is active.
     */
    public function active(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the meter is broken.
     */
    public function broken(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'broken',
        ]);
    }
}