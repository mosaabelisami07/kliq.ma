<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(2, true));

        return [
            'slug' => Str::slug($title),
            'title' => ['en' => $title],
            'summary' => ['en' => fake()->sentence()],
            'body' => ['en' => fake()->paragraph()],
            'deliverables' => ['en' => "Strategy\nDesign"],
            'icon' => 'megaphone',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the service is hidden from the public website.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
