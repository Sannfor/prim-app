<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            TransactionStatus::Pending,
            TransactionStatus::Paid,
            TransactionStatus::Failed,
        ]);

        return [
            'order_code' => Transaction::generateOrderCode(),
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'amount' => fake()->numberBetween(2, 60) * 5000,
            'status' => $status,
            'payment_method' => fake()->randomElement(['transfer', 'qris', 'ewallet']),
            'paid_at' => $status === TransactionStatus::Paid ? now()->subDays(fake()->numberBetween(1, 20)) : null,
            'expires_at' => now()->addHours(Transaction::PAYMENT_WINDOW_HOURS),
            'notes' => null,
        ];
    }

    /**
     * Transaksi yang sudah berhasil dibayar.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Paid,
            'paid_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ]);
    }

    /**
     * Transaksi yang masih menunggu pembayaran.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
            'paid_at' => null,
        ]);
    }

    /**
     * Transaksi yang sudah lewat batas waktu pembayaran.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Expired,
            'paid_at' => null,
            'expires_at' => now()->subDay(),
        ]);
    }
}
