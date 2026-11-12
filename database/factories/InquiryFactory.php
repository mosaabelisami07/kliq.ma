<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->e164PhoneNumber(),
            'message' => fake()->paragraph(),
            'status' => InquiryStatus::New,
            'locale' => 'en',
        ];
    }

    /**
     * Indicate that the inquiry has the given status.
     */
    public function status(InquiryStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'read_at' => now(),
        ]);
    }
}
