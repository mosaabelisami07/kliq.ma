<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
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
            'category_id' => Category::factory(),
            'slug' => Str::slug($title),
            'title' => ['en' => $title],
            'client' => fake()->company(),
            'summary' => ['en' => fake()->sentence()],
            'body' => ['en' => fake()->paragraph()],
            'services_provided' => ['en' => "Branding\nContent"],
            'year' => null,
            'is_published' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the project is hidden from the public website.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
