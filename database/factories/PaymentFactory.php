<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'subscriber_id' => \App\Models\Subscriber::factory(),
            'payment_number' => $this->faker->unique()->numerify('PAY-####'),
            'amount' => $this->faker->numberBetween(100, 3000),
            'payment_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'bank_transfer', 'online']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'description' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the payment is completed.
     */
    public function completed(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'failed',
        ]);
    }

    /**
     * Indicate that the payment was refunded.
     */
    public function refunded(): static {
        return $this->state(fn(array $attributes) => [
            'status' => 'refunded',
        ]);
    }
}