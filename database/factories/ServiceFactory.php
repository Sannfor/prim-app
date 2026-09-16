<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'provider_id' => Provider::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'tagline' => 'Nikmati '.Str::lower($name).' tanpa batas',
            'description' => fake()->paragraph(4),
            'logo_path' => null,
            'website' => 'https://'.fake()->unique()->domainName(),
            'is_active' => true,
        ];
    }

    /**
     * Layanan yang disembunyikan dari katalog publik.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
