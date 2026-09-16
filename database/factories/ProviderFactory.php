<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'PT '.fake()->unique()->company().' Digital';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => 'https://'.fake()->unique()->domainName(),
            'description' => fake()->paragraph(3),
            'logo_path' => null,
        ];
    }
}
