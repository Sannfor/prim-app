<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah penanda Super Admin pada tabel users.
 *
 * Peran tetap tiga nilai (user, admin, provider); Super Admin adalah tingkat di
 * dalam peran admin. Pemisahan lewat kolom boolean dipilih agar tidak mengubah
 * nilai peran yang sudah dipakai middleware, penyaring, dan laporan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
