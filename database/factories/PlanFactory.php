<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $duration = fake()->randomElement([30, 90, 180, 365]);

        return [
            'service_id' => Service::factory(),
            'name' => fake()->randomElement(['Basic', 'Standard', 'Premium']).' '.($duration / 30).' Bulan',
            'price' => fake()->numberBetween(2, 60) * 5000,
            'duration_days' => $duration,
            'max_devices' => fake()->randomElement([1, 2, 4, 6]),
            'description' => fake()->sentence(10),
            'features' => fake()->randomElements([
                'Kualitas hingga 4K',
                'Tanpa iklan',
                'Unduh offline',
                'Streaming bersamaan',
                'Akses seluruh konten',
                'Dukungan prioritas',
                'Akun sharing',
                'Bonus konten eksklusif',
            ], fake()->numberBetween(3, 5)),
            'is_active' => true,
        ];
    }

    /**
     * Paket yang tidak ditawarkan lagi.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
