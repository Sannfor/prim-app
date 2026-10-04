<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom tampilan pada tabel `transactions`.
 *
 * Desain admin menampilkan kolom "Metode Pembayaran" bertuliskan nama kanal
 * seperti "E-Wallet (Dana)" atau "Bank Transfer", bukan kode internal. Kolom
 * `payment_method` tetap dipakai sebagai kode, sedangkan `payment_method_label`
 * menyimpan teks yang ditampilkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method_label', 60)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method_label');
        });
    }
};
