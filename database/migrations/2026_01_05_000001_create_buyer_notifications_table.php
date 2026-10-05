<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notifikasi dalam aplikasi untuk pembeli.
 *
 * Dibuat terpisah dari tabel notifications bawaan Laravel karena PRIM hanya
 * memerlukan notifikasi di dalam aplikasi (bukan surel atau siaran), sehingga
 * skemanya bisa dibuat lebih sederhana dan mudah dibaca.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Jenis notifikasi, dipetakan ke enum BuyerNotificationType.
            $table->string('type', 40);

            $table->string('title');
            $table->text('body')->nullable();

            // Tautan tujuan saat notifikasi diklik.
            $table->string('url', 500)->nullable();

            // Transaksi terkait, bila ada. Notifikasi tetap ada walau pesanan
            // dihapus, hanya tautannya yang dilepas.
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_notifications');
    }
};
