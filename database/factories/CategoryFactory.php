<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'icon' => fake()->randomElement([
                'film', 'musical-note', 'book-open', 'briefcase',
                'paint-brush', 'code-bracket', 'shield-check', 'cloud',
            ]),
            'description' => fake()->sentence(12),
        ];
    }
}
