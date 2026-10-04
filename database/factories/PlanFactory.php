<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
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
        $discount = fake()->randomElement([0, 0, 5, 10]);
        $price = fake()->numberBetween(2, 60) * 5000;

        return [
            'service_id' => Service::factory(),
            'name' => fake()->randomElement(['Basic', 'Standard', 'Premium']).' '.($duration / 30).' Bulan',
            'variant_group' => fake()->randomElement(['Bulanan', 'Tahunan', '1 Perangkat', '2 Perangkat', 'User Host']),
            'price' => $price,
            'compare_at_price' => $discount > 0 ? (int) round($price / (1 - $discount / 100)) : null,
            'discount_percent' => $discount,
            'duration_days' => $duration,
            'periods_label' => fake()->randomElement(['1 bln', '1, 2, 3 bln', '1, 3, 6 bln', '12 bln']),
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
            'is_preorder' => fake()->boolean(10),
            'stock' => fake()->numberBetween(0, 60),
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
