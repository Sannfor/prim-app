<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subDays(fake()->numberBetween(1, 60));

        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'transaction_id' => Transaction::factory()->paid(),
            'status' => SubscriptionStatus::Active,
            'started_at' => $startedAt,
            'ends_at' => $startedAt->copy()->addDays(30),
            'auto_renew' => false,
            'cancelled_at' => null,
        ];
    }

    /**
     * Langganan yang masa aktifnya sudah habis.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Expired,
            'started_at' => now()->subDays(90),
            'ends_at' => now()->subDays(60),
        ]);
    }

    /**
     * Langganan yang tinggal beberapa hari lagi.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Active,
            'started_at' => now()->subDays(28),
            'ends_at' => now()->addDays(3),
        ]);
    }
}
