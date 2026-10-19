<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Pemilik akun penyedia
|--------------------------------------------------------------------------
|
| Menautkan satu baris providers ke satu akun pengguna berperan provider.
| Tautan inilah yang dipakai panel penyedia untuk menentukan layanan mana
| yang boleh dilihat dan disunting: hanya layanan milik provider tersebut.
|
| Kolomnya nullable karena seluruh data penyedia yang sudah ada dibuat oleh
| admin dan belum punya akun sendiri. Unik agar satu akun tidak mewakili dua
| penyedia sekaligus.
|
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
