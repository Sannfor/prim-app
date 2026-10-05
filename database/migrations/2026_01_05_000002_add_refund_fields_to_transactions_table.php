<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom pengembalian dana pada transaksi.
 *
 * Pengembalian tidak menghapus pesanan maupun mengubah statusnya menjadi gagal,
 * supaya riwayat pembelian dan struk lama tetap utuh. Status pesanan tetap
 * "Selesai" dengan penanda bahwa dananya sudah dikembalikan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('paid_confirmed_at');
            $table->unsignedInteger('refund_amount')->default(0)->after('refunded_at');
            $table->string('refund_reason', 500)->nullable()->after('refund_amount');
            $table->foreignId('refunded_by')->nullable()->after('refund_reason')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('refunded_by');
            $table->dropColumn(['refunded_at', 'refund_amount', 'refund_reason']);
        });
    }
};
