<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $name = $this->faker->company();
        $domain = Str::slug($name);

        return [
            'name' => $name,
            'domain' => $domain,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'contact_person' => $this->faker->name(),
            'database' => 'tenant_' . $domain . '_' . time(),
            'deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the tenant is deleted.
     */
    public function deleted(): static {
        return $this->state(fn(array $attributes) => [
            'deleted' => true,
        ]);
    }
}