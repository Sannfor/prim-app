<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom yang diminta desain Figma pada tabel `users`.
 *
 * Tabel "Manajemen Pengguna" pada desain admin menampilkan kolom Status
 * (Aktif / Non-aktif / Suspend) dan Terakhir Login, yang belum ada sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('aktif')->after('role');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('address')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_login_at', 'address']);
        });
    }
};
