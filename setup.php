<?php

/**
 * Penyiapan PRIM sekali jalan.
 *
 * Menyiapkan berkas .env, memasang dependensi PHP dan JavaScript, membuat basis
 * data SQLite, mengisi data contoh, lalu membangun aset antarmuka.
 *
 * Jalankan dari dalam folder prim:
 *     php setup.php
 */

declare(strict_types=1);

$root = __DIR__;

/** Cetak judul langkah. */
function step(string $title): void
{
    echo PHP_EOL.'=== '.$title.' ==='.PHP_EOL;
}

/** Jalankan perintah di shell, hentikan bila gagal. */
function run(string $command, bool $required = true): bool
{
    echo '  $ '.$command.PHP_EOL;

    $output = [];
    $code = 0;
    exec($command.' 2>&1', $output, $code);

    foreach ($output as $line) {
        echo '    '.$line.PHP_EOL;
    }

    if ($code !== 0 && $required) {
        echo PHP_EOL.'  PERINTAH GAGAL (kode '.$code.'). Perbaiki dulu, lalu jalankan ulang php setup.php'.PHP_EOL;
        exit(1);
    }

    return $code === 0;
}

/** Apakah sebuah perintah tersedia. */
function has(string $binary): bool
{
    $check = stripos(PHP_OS_FAMILY, 'Windows') === 0 ? 'where' : 'command -v';
    exec($check.' '.$binary.' 2>&1', $out, $code);

    return $code === 0;
}

echo '================================================='.PHP_EOL;
echo '  PRIM — Penyiapan Lingkungan Pengembangan'.PHP_EOL;
echo '================================================='.PHP_EOL;

// ---------------------------------------------------------------------------
step('1. Memeriksa kebutuhan sistem');

$requiredExtensions = ['pdo_sqlite', 'sqlite3', 'mbstring', 'openssl', 'tokenizer', 'xml', 'curl', 'fileinfo', 'zip'];
$missingExtensions = [];

foreach ($requiredExtensions as $extension) {
    if (! extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

printf("  PHP %s — %s%s", PHP_VERSION, $missingExtensions === [] ? 'seluruh ekstensi tersedia' : 'ekstensi kurang: '.implode(', ', $missingExtensions), PHP_EOL);

if (PHP_VERSION_ID < 80200) {
    echo PHP_EOL.'  PHP minimal 8.2. Silakan pasang versi yang lebih baru.'.PHP_EOL;
    exit(1);
}

if ($missingExtensions !== []) {
    echo PHP_EOL.'  Aktifkan ekstensi di atas pada php.ini, lalu jalankan ulang.'.PHP_EOL;
    exit(1);
}

foreach (['composer', 'npm'] as $binary) {
    if (! has($binary)) {
        echo PHP_EOL.'  Perintah "'.$binary.'" tidak ditemukan. Pasang dulu, lalu jalankan ulang.'.PHP_EOL;
        exit(1);
    }

    echo '  '.$binary.' — tersedia'.PHP_EOL;
}

// ---------------------------------------------------------------------------
step('2. Menyiapkan berkas .env');

if (file_exists($root.'/.env')) {
    echo '  .env sudah ada, dilewati'.PHP_EOL;
} else {
    copy($root.'/.env.example', $root.'/.env');
    echo '  .env dibuat dari .env.example'.PHP_EOL;
}

// ---------------------------------------------------------------------------
step('3. Memasang dependensi PHP (composer install)');
run('composer install --no-interaction --prefer-dist');

// ---------------------------------------------------------------------------
step('4. Membuat kunci aplikasi');
run('php artisan key:generate --ansi');

// ---------------------------------------------------------------------------
step('5. Membuat berkas basis data SQLite');

$database = $root.'/database/database.sqlite';

if (file_exists($database)) {
    echo '  database/database.sqlite sudah ada, dilewati'.PHP_EOL;
} else {
    touch($database);
    echo '  database/database.sqlite dibuat'.PHP_EOL;
}

// ---------------------------------------------------------------------------
step('6. Membangun tabel dan mengisi data contoh');
run('php artisan migrate:fresh --seed --force');

// ---------------------------------------------------------------------------
step('7. Memasang dependensi JavaScript (npm install)');
run('npm install');

// ---------------------------------------------------------------------------
step('8. Membangun aset antarmuka (npm run build)');
run('npm run build');

// ---------------------------------------------------------------------------
echo PHP_EOL.'================================================='.PHP_EOL;
echo '  SELESAI — PRIM siap dijalankan'.PHP_EOL;
echo '================================================='.PHP_EOL;
echo PHP_EOL.'Jalankan server pengembangan:'.PHP_EOL;
echo '  php artisan serve'.PHP_EOL;
echo PHP_EOL.'Lalu buka: http://127.0.0.1:8000'.PHP_EOL;
echo PHP_EOL.'Akun demo (kata sandi semuanya: password)'.PHP_EOL;
echo '  Administrator : admin@prim.com'.PHP_EOL;
echo '  Pelanggan     : andi@prim.test'.PHP_EOL;
echo PHP_EOL.'Panel pengelola: http://127.0.0.1:8000/admin'.PHP_EOL;
echo 'Rincian lengkap ada di TUTORIAL-MENJALANKAN.txt'.PHP_EOL;
