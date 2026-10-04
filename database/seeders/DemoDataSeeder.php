<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Enums\UserStatus;
use App\Models\Plan;
use App\Models\ServiceCredential;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun demo dan contoh transaksi/langganan untuk keperluan demonstrasi.
 *
 * Nama pengguna, kanal pembayaran, status pesanan, dan status akun mengikuti
 * data contoh yang tampil pada frame admin di desain Figma. Seluruh akun
 * memakai kata sandi yang sama: "password".
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Daftar akun pelanggan contoh beserta status dan waktu login terakhirnya.
     *
     * @var list<array<string, mixed>>
     */
    private const CUSTOMERS = [
        ['name' => 'Andi Susanto', 'email' => 'andi@prim.test', 'phone' => '0812-3456-7890', 'status' => UserStatus::Aktif, 'login' => '2 jam lalu'],
        ['name' => 'Rina Kusuma', 'email' => 'rina@prim.test', 'phone' => '0813-9876-5432', 'status' => UserStatus::Aktif, 'login' => '1 hari lalu'],
        ['name' => 'Budi Wahyu', 'email' => 'budi@prim.test', 'phone' => '0811-2233-4455', 'status' => UserStatus::Aktif, 'login' => '5 menit lalu'],
        ['name' => 'Siti Aminah', 'email' => 'siti@prim.test', 'phone' => '0856-7788-9900', 'status' => UserStatus::NonAktif, 'login' => '30 hari lalu'],
        ['name' => 'Doni Perdana', 'email' => 'doni@prim.test', 'phone' => '0821-5544-3322', 'status' => UserStatus::Aktif, 'login' => '3 jam lalu'],
        ['name' => 'Maya Putri', 'email' => 'maya@prim.test', 'phone' => '0878-1122-3344', 'status' => UserStatus::Suspend, 'login' => '45 hari lalu'],
        ['name' => 'Hendra Wijaya', 'email' => 'hendra@prim.test', 'phone' => '0819-6677-8899', 'status' => UserStatus::Aktif, 'login' => 'Hari ini'],
        ['name' => 'Fitri Handayani', 'email' => 'fitri@prim.test', 'phone' => '0852-4433-2211', 'status' => UserStatus::Aktif, 'login' => '1 jam lalu'],
        ['name' => 'Ahmad Faisal', 'email' => 'faisal@prim.test', 'phone' => '0813-5566-7788', 'status' => UserStatus::Aktif, 'login' => '6 jam lalu'],
        ['name' => 'Dewi Lestari', 'email' => 'dewi@prim.test', 'phone' => '0817-2233-4455', 'status' => UserStatus::Aktif, 'login' => '2 hari lalu'],
        ['name' => 'Eko Prasetyo', 'email' => 'eko@prim.test', 'phone' => '0815-9988-7766', 'status' => UserStatus::Aktif, 'login' => '4 jam lalu'],
        ['name' => 'Mega Utami', 'email' => 'mega@prim.test', 'phone' => '0818-3344-5566', 'status' => UserStatus::NonAktif, 'login' => '12 hari lalu'],
        ['name' => 'Rizky Aditya', 'email' => 'rizky@prim.test', 'phone' => '0816-7788-9900', 'status' => UserStatus::Aktif, 'login' => '20 menit lalu'],
        ['name' => 'Fitriani', 'email' => 'fitriani@prim.test', 'phone' => '0814-2211-3344', 'status' => UserStatus::Aktif, 'login' => '1 hari lalu'],
        ['name' => 'Citra Kirana', 'email' => 'citra@prim.test', 'phone' => '0812-8899-0011', 'status' => UserStatus::Aktif, 'login' => '8 jam lalu'],
        ['name' => 'Guntur Bumi', 'email' => 'guntur@prim.test', 'phone' => '0813-4455-6677', 'status' => UserStatus::Aktif, 'login' => '3 hari lalu'],
    ];

    /**
     * Contoh transaksi: layanan, kanal pembayaran, dan status.
     *
     * @var list<array<string, mixed>>
     */
    private const TRANSACTIONS = [
        ['service' => 'netflix', 'plan' => '2 Perangkat', 'user' => 'andi@prim.test', 'method' => 'E-Wallet (Dana)', 'code' => 'ewallet_dana', 'status' => TransactionStatus::Paid, 'days' => 0],
        ['service' => 'spotify-premium', 'plan' => 'Bulanan', 'user' => 'rina@prim.test', 'method' => 'Bank Transfer', 'code' => 'bank_transfer', 'status' => TransactionStatus::Processed, 'days' => 0],
        ['service' => 'chatgpt-plus', 'plan' => '1 Perangkat', 'user' => 'budi@prim.test', 'method' => 'QRIS', 'code' => 'qris', 'status' => TransactionStatus::Paid, 'days' => 0],
        ['service' => 'canva-pro', 'plan' => 'Bulanan Host', 'user' => 'siti@prim.test', 'method' => 'E-Wallet (OVO)', 'code' => 'ewallet_ovo', 'status' => TransactionStatus::Pending, 'days' => 1],
        ['service' => 'disney-plus-hotstar', 'plan' => '1 Perangkat', 'user' => 'doni@prim.test', 'method' => 'Bank Transfer', 'code' => 'bank_transfer', 'status' => TransactionStatus::Cancelled, 'days' => 1],
        ['service' => 'apple-music', 'plan' => 'Bulanan', 'user' => 'maya@prim.test', 'method' => 'QRIS', 'code' => 'qris', 'status' => TransactionStatus::Paid, 'days' => 1],
        ['service' => 'netflix', 'plan' => '2 Perangkat', 'user' => 'dewi@prim.test', 'method' => 'E-Wallet (Dana)', 'code' => 'ewallet_dana', 'status' => TransactionStatus::Accepted, 'days' => 2],
        ['service' => 'chatgpt-plus', 'plan' => '1 Perangkat', 'user' => 'eko@prim.test', 'method' => 'Bank Transfer', 'code' => 'bank_transfer', 'status' => TransactionStatus::Paid, 'days' => 2],
        ['service' => 'canva-pro', 'plan' => 'Bulanan Reguler', 'user' => 'mega@prim.test', 'method' => 'QRIS', 'code' => 'qris', 'status' => TransactionStatus::Cancelled, 'days' => 3],
        ['service' => 'spotify-premium', 'plan' => 'Promo 3 & 6 Bulan', 'user' => 'rizky@prim.test', 'method' => 'E-Wallet (Gopay)', 'code' => 'ewallet_gopay', 'status' => TransactionStatus::Paid, 'days' => 3],
        ['service' => 'youku', 'plan' => 'Bulanan', 'user' => 'fitriani@prim.test', 'method' => 'QRIS', 'code' => 'qris', 'status' => TransactionStatus::Paid, 'days' => 4],
        ['service' => 'disney-plus-hotstar', 'plan' => '1 Perangkat', 'user' => 'hendra@prim.test', 'method' => 'E-Wallet (OVO)', 'code' => 'ewallet_ovo', 'status' => TransactionStatus::WaitingProcess, 'days' => 4],
        ['service' => 'netflix', 'plan' => '1 Perangkat', 'user' => 'citra@prim.test', 'method' => 'Bank Transfer', 'code' => 'bank_transfer', 'status' => TransactionStatus::Paid, 'days' => 5],
        ['service' => 'vidio-premier', 'plan' => 'Bulanan', 'user' => 'guntur@prim.test', 'method' => 'QRIS', 'code' => 'qris', 'status' => TransactionStatus::Processed, 'days' => 5],
        ['service' => 'google-one', 'plan' => 'Tahunan', 'user' => 'faisal@prim.test', 'method' => 'E-Wallet (Dana)', 'code' => 'ewallet_dana', 'status' => TransactionStatus::FollowUp, 'days' => 6],
        ['service' => 'viu-premium', 'plan' => 'Bulanan', 'user' => 'fitri@prim.test', 'method' => 'Bank Transfer', 'code' => 'bank_transfer', 'status' => TransactionStatus::Paid, 'days' => 7],
        ['service' => 'wetv', 'plan' => 'Bulanan', 'user' => 'andi@prim.test', 'method' => 'QRIS', 'code' => 'qris', 'status' => TransactionStatus::Paid, 'days' => 9],
        ['service' => 'max', 'plan' => 'Bulanan', 'user' => 'rina@prim.test', 'method' => 'E-Wallet (Gopay)', 'code' => 'ewallet_gopay', 'status' => TransactionStatus::ProofRenewal, 'days' => 12],
        ['service' => 'iqiyi', 'plan' => 'Bulanan', 'user' => 'budi@prim.test', 'method' => 'Bank Transfer', 'code' => 'bank_transfer', 'status' => TransactionStatus::Grace, 'days' => 20],
        ['service' => 'zoom-pro', 'plan' => 'Bulanan', 'user' => 'dewi@prim.test', 'method' => 'QRIS', 'code' => 'qris', 'status' => TransactionStatus::ProofRevision, 'days' => 25],
    ];

    public function run(): void
    {
        $this->seedUsers();
        $this->seedTransactions();
    }

    /**
     * Buat akun administrator dan pelanggan contoh.
     */
    private function seedUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@prim.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'status' => UserStatus::Aktif,
                'phone' => '+62 858 2812 0489',
                'address' => 'Banjarbaru, Kalimantan Selatan',
                'last_login_at' => now()->subMinutes(5),
                'email_verified_at' => now(),
            ]
        );

        // Akun admin lama tetap dipertahankan agar tautan dokumentasi lama
        // dan pengujian yang memakai alamat ini tidak putus.
        User::updateOrCreate(
            ['email' => 'admin@prim.test'],
            [
                'name' => 'Admin PRIM',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'status' => UserStatus::Aktif,
                'phone' => '081100000001',
                'last_login_at' => now()->subHours(2),
                'email_verified_at' => now(),
            ]
        );

        foreach (self::CUSTOMERS as $index => $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => Hash::make('password'),
                    'role' => Role::User,
                    'status' => $customer['status'],
                    'phone' => $customer['phone'],
                    'address' => 'Banjarmasin, Kalimantan Selatan',
                    'last_login_at' => $this->resolveLastLogin($customer['login']),
                    'email_verified_at' => now()->subDays(30 - $index),
                    'created_at' => now()->subDays(120 - ($index * 3)),
                ]
            );
        }
    }

    /**
     * Ubah label waktu login pada desain menjadi waktu nyata.
     */
    private function resolveLastLogin(string $label): \Illuminate\Support\Carbon
    {
        return match (true) {
            $label === 'Hari ini' => now()->subHour(),
            str_contains($label, 'menit') => now()->subMinutes((int) $label),
            str_contains($label, 'jam') => now()->subHours((int) $label),
            str_contains($label, 'hari') => now()->subDays((int) $label),
            default => now()->subHour(),
        };
    }

    /**
     * Buat contoh transaksi, langganan, dan kredensial akun.
     */
    private function seedTransactions(): void
    {
        // Hanya jalankan sekali agar seeder tetap idempoten.
        if (Transaction::query()->exists()) {
            return;
        }

        foreach (self::TRANSACTIONS as $scenario) {
            $plan = Plan::query()
                ->whereHas('service', fn ($q) => $q->where('slug', $scenario['service']))
                ->where('name', $scenario['plan'])
                ->first();

            $user = User::query()->where('email', $scenario['user'])->first();

            if (! $plan || ! $user) {
                continue;
            }

            $createdAt = now()->subDays($scenario['days'])->setTime(12, 30);

            $transaction = new Transaction([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'amount' => $plan->price,
                'status' => $scenario['status'],
                'payment_method' => $scenario['code'],
                'payment_method_label' => $scenario['method'],
                'paid_at' => $scenario['status']->isSuccessful() ? $createdAt->copy()->addMinutes(12) : null,
                'expires_at' => $scenario['status'] === TransactionStatus::Pending
                    ? now()->addHours(20)
                    : $createdAt->copy()->addDay(),
                'notes' => 'Data contoh untuk keperluan demonstrasi akademik.',
            ]);

            $transaction->created_at = $createdAt;
            $transaction->updated_at = $createdAt;
            $transaction->save();

            // Kredensial akun hanya dibagikan pada pesanan yang sudah berhasil.
            if ($scenario['status']->isSuccessful()) {
                $this->seedCredential($transaction, $user, $plan, $createdAt);
            }

            // Sebagian transaksi berhasil melahirkan langganan aktif.
            if ($scenario['status']->isSuccessful() && $scenario['days'] <= 12) {
                $endsAt = $createdAt->copy()->addDays($plan->duration_days);

                $subscription = new Subscription([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'transaction_id' => $transaction->id,
                    'status' => SubscriptionStatus::Active,
                    'started_at' => $createdAt,
                    'ends_at' => $endsAt,
                    'auto_renew' => $scenario['days'] % 2 === 0,
                ]);

                $subscription->created_at = $createdAt;
                $subscription->updated_at = $createdAt;
                $subscription->save();
            }
        }
    }

    /**
     * Buat kredensial akun layanan contoh untuk sebuah pesanan.
     */
    private function seedCredential(Transaction $transaction, User $user, Plan $plan, \Illuminate\Support\Carbon $createdAt): void
    {
        $service = $plan->service;

        $credential = new ServiceCredential([
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'label' => $service->name.' — '.$plan->groupLabel(),
            'login_code' => strtolower(str_replace([' ', '+'], '', $service->slug)).'@prim.id',
            'password_code' => 'PRIM'.now()->format('y').strtoupper(\Illuminate\Support\Str::random(4)),
            'profile_name' => $plan->groupLabel(),
            'pin_code' => (string) random_int(1000, 9999),
            'notes' => 'Data contoh. Ganti kata sandi setelah login pertama.',
            'delivered_at' => $createdAt->copy()->addMinutes(15),
        ]);

        $credential->created_at = $createdAt->copy()->addMinutes(15);
        $credential->updated_at = $credential->created_at;
        $credential->save();
    }
}
