<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        // Build a valid E.164 phone: +1 followed by 10 digits
        $phone = '+1' . fake()->numerify('##########');

        return [
            'name'  => fake()->name(),
            'phone' => $phone,
            'email' => fake()->unique()->safeEmail(),
        ];
    }

    public function withoutEmail(): static
    {
        return $this->state(['email' => null]);
    }

    public function withoutPhone(): static
    {
        return $this->state(['phone' => null]);
    }
}
