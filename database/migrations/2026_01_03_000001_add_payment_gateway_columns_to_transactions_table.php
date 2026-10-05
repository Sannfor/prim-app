<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom pendukung pembayaran daring.
 *
 * Dipakai bila PRIM dihubungkan ke penyedia pembayaran seperti Midtrans:
 * token transaksi dari penyedia disimpan agar tautan pembayaran dapat dibuka
 * kembali, dan referensi pembayaran disimpan untuk keperluan rekonsiliasi
 * maupun pencetakan struk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment_method_label');
            $table->string('payment_token')->nullable()->after('payment_reference');
            $table->string('payment_url', 500)->nullable()->after('payment_token');
            $table->timestamp('paid_confirmed_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_reference',
                'payment_token',
                'payment_url',
                'paid_confirmed_at',
            ]);
        });
    }
};
