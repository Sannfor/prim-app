<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Voucher atau kode promo.
 *
 * Voucher dipakai pembeli pada halaman konfirmasi pemesanan, sebelum transaksi
 * dibuat. Potongan yang dihasilkan disimpan pada transaksi yang bersangkutan
 * agar struk lama tetap menampilkan nilai yang sama walau aturan voucher
 * berubah di kemudian hari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            // Kode yang diketik pembeli, selalu disimpan dalam huruf besar.
            $table->string('code', 40)->unique();

            $table->string('description')->nullable();

            // 'percent' memotong sekian persen, 'fixed' memotong nominal tetap.
            $table->string('type', 10)->default('percent');

            // Nilai potongan: persen (1-100) atau rupiah.
            $table->unsignedInteger('value');

            // Batas maksimal potongan untuk tipe persen, dalam rupiah.
            $table->unsignedInteger('max_discount')->nullable();

            // Belanja minimum agar voucher dapat dipakai.
            $table->unsignedInteger('min_purchase')->default(0);

            // Batas pemakaian: total seluruh pembeli dan per pembeli.
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedSmallInteger('usage_limit_per_user')->default(1);
            $table->unsignedInteger('used_count')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'expires_at']);
        });

        /*
         * Catatan pemakaian voucher. Satu baris untuk setiap pemakaian, sehingga
         * batas pemakaian per pembeli dapat diperiksa tanpa menghitung ulang
         * dari tabel transaksi.
         */
        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('discount_amount');
            $table->timestamps();

            $table->index(['voucher_id', 'user_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('plan_id')->constrained()->nullOnDelete();

            // Potongan yang benar-benar diberikan, dalam rupiah.
            $table->unsignedInteger('discount_amount')->default(0)->after('amount');

            // Harga asli paket sebelum potongan, agar struk dapat menampilkan
            // rincian "harga paket − diskon = total".
            $table->unsignedInteger('subtotal_amount')->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn(['discount_amount', 'subtotal_amount']);
        });

        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('vouchers');
    }
};
