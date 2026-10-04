<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom yang diminta desain Figma pada tabel `plans`.
 *
 * Desain menampilkan setiap layanan sebagai kartu berisi beberapa *varian*
 * paket. Tiap varian memiliki nama grup ("Bulanan", "1 Perangkat", "User Host"),
 * harga, daftar periode tagihan yang tersedia ("1, 2, 3, 6 bln"), serta pita
 * diskon / preorder di sudut kartu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Nama grup varian, mis. "Bulanan", "1 Perangkat", "User Host".
            $table->string('variant_group', 60)->nullable()->after('name');

            // Daftar periode tagihan yang dapat dipilih, mis. "1, 2, 3, 6 bln".
            $table->string('periods_label', 40)->nullable()->after('duration_days');

            // Persentase diskon yang ditampilkan sebagai pita pada kartu.
            $table->unsignedTinyInteger('discount_percent')->default(0)->after('price');

            // Harga sebelum diskon, dipakai untuk mencoret harga dan menghitung diskon.
            $table->unsignedInteger('compare_at_price')->nullable()->after('discount_percent');

            // Penanda paket preorder (pita "Preorder" pada kartu).
            $table->boolean('is_preorder')->default(false)->after('is_active');

            // Jumlah stok akun yang tersedia (kolom "Stok" pada admin produk).
            $table->unsignedInteger('stock')->default(0)->after('is_preorder');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'variant_group',
                'periods_label',
                'discount_percent',
                'compare_at_price',
                'is_preorder',
                'stock',
            ]);
        });
    }
};
