<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

/**
 * Voucher contoh untuk keperluan demonstrasi.
 *
 * Kode yang tersedia sengaja dibuat mudah diingat agar dapat dicoba langsung
 * pada halaman konfirmasi pemesanan.
 */
class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $voucher = [
            [
                'code' => 'HEMAT20',
                'description' => 'Potongan 20% untuk semua layanan',
                'type' => Voucher::TYPE_PERCENT,
                'value' => 20,
                'max_discount' => 50000,
                'min_purchase' => 0,
                'usage_limit' => 200,
                'usage_limit_per_user' => 1,
            ],
            [
                'code' => 'POTONGAN10K',
                'description' => 'Potongan Rp10.000 tanpa minimum belanja',
                'type' => Voucher::TYPE_FIXED,
                'value' => 10000,
                'max_discount' => null,
                'min_purchase' => 0,
                'usage_limit' => 200,
                'usage_limit_per_user' => 2,
            ],
            [
                'code' => 'PRIM50K',
                'description' => 'Potongan Rp50.000 untuk belanja minimal Rp150.000',
                'type' => Voucher::TYPE_FIXED,
                'value' => 50000,
                'max_discount' => null,
                'min_purchase' => 150000,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
            ],
            [
                'code' => 'NEWMEMBER',
                'description' => 'Potongan 15% khusus pengguna baru',
                'type' => Voucher::TYPE_PERCENT,
                'value' => 15,
                'max_discount' => 30000,
                'min_purchase' => 0,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
            ],
        ];

        foreach ($voucher as $data) {
            Voucher::updateOrCreate(
                ['code' => $data['code']],
                $data + [
                    'used_count' => 0,
                    'starts_at' => null,
                    'expires_at' => now()->addMonths(6),
                    'is_active' => true,
                ],
            );
        }
    }
}
