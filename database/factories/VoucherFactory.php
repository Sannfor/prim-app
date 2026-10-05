<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'description' => 'Voucher '.$this->faker->word(),
            'type' => Voucher::TYPE_PERCENT,
            'value' => 10,
            'max_discount' => 50000,
            'min_purchase' => 0,
            'usage_limit' => null,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    /**
     * Voucher potongan nominal tetap.
     */
    public function fixed(int $value = 10000): static
    {
        return $this->state(fn () => [
            'type' => Voucher::TYPE_FIXED,
            'value' => $value,
            'max_discount' => null,
        ]);
    }

    /**
     * Voucher potongan persen.
     */
    public function percent(int $value = 20, ?int $maxDiscount = 50000): static
    {
        return $this->state(fn () => [
            'type' => Voucher::TYPE_PERCENT,
            'value' => $value,
            'max_discount' => $maxDiscount,
        ]);
    }

    /**
     * Voucher yang sudah tidak aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Voucher yang masa berlakunya sudah lewat.
     */
    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    /**
     * Voucher yang kuotanya sudah habis.
     */
    public function quotaExhausted(): static
    {
        return $this->state(fn () => [
            'usage_limit' => 5,
            'used_count' => 5,
        ]);
    }
}
