<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun demo dan contoh transaksi/langganan untuk keperluan demonstrasi.
 *
 * Seluruh akun memakai kata sandi yang sama: "password".
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->seedUsers();
        $this->seedTransactions($admin);
    }

    /**
     * Buat akun administrator dan beberapa pelanggan contoh.
     */
    private function seedUsers(): User
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@prim.test'],
            [
                'name' => 'Admin PRIM',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'phone' => '081100000001',
                'email_verified_at' => now(),
            ]
        );

        $customers = [
            ['name' => 'Ahmadi Hasan', 'email' => 'ahmadi@prim.test', 'phone' => '081100000002'],
            ['name' => 'Hidayatunnisa', 'email' => 'hidayatunnisa@prim.test', 'phone' => '081100000003'],
            ['name' => 'Najwa Karima', 'email' => 'najwa@prim.test', 'phone' => '081100000004'],
            ['name' => 'Misliani', 'email' => 'misliani@prim.test', 'phone' => '081100000005'],
        ];

        $created = [];

        foreach ($customers as $customer) {
            $created[] = User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => Hash::make('password'),
                    'role' => Role::User,
                    'phone' => $customer['phone'],
                    'email_verified_at' => now(),
                ]
            );
        }

        return $admin;
    }

    /**
     * Buat contoh transaksi beserta langganan yang menyertainya.
     */
    private function seedTransactions(User $admin): void
    {
        // Hanya jalankan sekali agar seeder tetap idempoten.
        if (Transaction::query()->exists()) {
            return;
        }

        $customers = User::query()->role(Role::User)->get();

        if ($customers->isEmpty()) {
            return;
        }

        $plans = Plan::query()->with('service')->get();

        if ($plans->isEmpty()) {
            return;
        }

        /*
         * Rancangan contoh data:
         * - 4 transaksi berhasil yang melahirkan langganan aktif/kedaluwarsa
         * - 2 transaksi menunggu pembayaran
         * - 1 transaksi gagal
         */
        $scenarios = [
            ['status' => TransactionStatus::Paid, 'subscription' => 'active', 'days_ago' => 10],
            ['status' => TransactionStatus::Paid, 'subscription' => 'active', 'days_ago' => 40],
            ['status' => TransactionStatus::Paid, 'subscription' => 'expiring', 'days_ago' => 57],
            ['status' => TransactionStatus::Paid, 'subscription' => 'expired', 'days_ago' => 120],
            ['status' => TransactionStatus::Pending, 'subscription' => null, 'days_ago' => 0],
            ['status' => TransactionStatus::Pending, 'subscription' => null, 'days_ago' => 1],
            ['status' => TransactionStatus::Failed, 'subscription' => null, 'days_ago' => 5],
        ];

        foreach ($scenarios as $index => $scenario) {
            $customer = $customers[$index % $customers->count()];
            $plan = $plans[$index % $plans->count()];

            $createdAt = now()->subDays($scenario['days_ago']);

            $transaction = new Transaction([
                'user_id' => $customer->id,
                'plan_id' => $plan->id,
                'amount' => $plan->price,
                'status' => $scenario['status'],
                'payment_method' => $scenario['status'] === TransactionStatus::Paid ? 'transfer' : null,
                'paid_at' => $scenario['status'] === TransactionStatus::Paid ? $createdAt->copy()->addMinutes(12) : null,
                'expires_at' => $scenario['status'] === TransactionStatus::Pending
                    ? now()->addHours(20)
                    : $createdAt->copy()->addDay(),
            ]);

            $transaction->created_at = $createdAt;
            $transaction->updated_at = $createdAt;
            $transaction->save();

            if ($scenario['subscription'] === null) {
                continue;
            }

            $startedAt = $createdAt->copy();
            $endsAt = match ($scenario['subscription']) {
                'active' => $startedAt->copy()->addDays($plan->duration_days),
                'expiring' => now()->addDays(3),
                'expired' => $startedAt->copy()->addDays($plan->duration_days),
                default => $startedAt->copy()->addDays($plan->duration_days),
            };

            $subscription = new Subscription([
                'user_id' => $customer->id,
                'plan_id' => $plan->id,
                'transaction_id' => $transaction->id,
                'status' => $scenario['subscription'] === 'expired'
                    ? SubscriptionStatus::Expired
                    : SubscriptionStatus::Active,
                'started_at' => $startedAt,
                'ends_at' => $endsAt,
                'auto_renew' => false,
            ]);

            $subscription->created_at = $startedAt;
            $subscription->updated_at = $startedAt;
            $subscription->save();
        }
    }
}
