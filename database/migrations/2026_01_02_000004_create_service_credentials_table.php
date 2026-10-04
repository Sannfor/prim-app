<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kredensial akun layanan yang dibagikan setelah pembelian berhasil.
 *
 * Desain Figma memiliki tab "Kode Login" pada pusat pesanan pengguna, berisi
 * kode/kredensial akun (mis. akun Netflix atau ChatGPT) untuk pesanan yang
 * sudah dibayar. Tabel ini menyimpan kredensial tersebut per transaksi agar
 * hanya pembeli terkait yang dapat melihatnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('login_code');
            $table->string('password_code')->nullable();
            $table->string('profile_name')->nullable();
            $table->string('pin_code', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_credentials');
    }
};
