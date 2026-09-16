# PRIM — Platform Digital Aggregator Layanan Premium

Aplikasi web untuk **mencari, membandingkan, dan berlangganan** layanan digital premium
dalam satu wadah. Proyek ini merupakan implementasi dari dokumen *Software Requirements
Specification (SRS)* Kelompok 7, Ilmu Komputer FMIPA Universitas Lambung Mangkurat.

---

## Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Teknologi](#teknologi)
3. [Persyaratan Sistem](#persyaratan-sistem)
4. [Instalasi](#instalasi)
5. [Akun Demo](#akun-demo)
6. [Peta URL](#peta-url)
7. [Struktur Proyek](#struktur-proyek)
8. [Model Data](#model-data)
9. [Pengujian](#pengujian)
10. [Catatan Simulasi Pembayaran](#catatan-simulasi-pembayaran)
11. [Pemecahan Masalah](#pemecahan-masalah)

---

## Fitur Utama

### Katalog (publik, tanpa login)
- Pencarian layanan berdasarkan nama, tagline, dan deskripsi
- Filter kategori, penyedia, dan batas harga
- Pengurutan: terbaru, harga terendah/tertinggi, nama
- Halaman detail layanan: daftar paket, harga, fitur, dan ulasan pengguna
- Komparasi side-by-side hingga 4 layanan dengan penanda nilai paling menguntungkan

### Transaksi (wajib login)
- Checkout paket dengan pilihan metode pembayaran
- Peringatan otomatis bila pengguna sudah punya langganan aktif atas layanan yang sama
- Simulasi pembayaran berhasil/gagal (tanpa penyedia pihak ketiga)
- Riwayat transaksi dengan filter status
- Batas waktu pembayaran 24 jam, otomatis menjadi kedaluwarsa

### Langganan (wajib login)
- Daftar langganan aktif, akan berakhir, dan sudah berakhir
- Progress bar masa aktif dan sisa hari
- Perpanjangan dengan paket yang sama maupun berbeda; masa aktif disambung
- Perpanjangan otomatis dapat dinyalakan/dimatikan
- Perintah terjadwal untuk mengakhiri langganan yang lewat masa aktif

### Panel Pengelola (khusus admin)
- Dashboard metrik nyata: pendapatan bulan ini + pertumbuhan, komposisi status
  transaksi, diagram 14 hari, layanan teratas, langganan yang akan berakhir
- CRUD Penyedia, Kategori, Layanan (dengan unggah logo), dan Paket
- Pengelolaan peran pengguna
- Verifikasi transaksi manual (tandai berhasil/gagal)

---

## Teknologi

| Komponen | Versi |
|---|---|
| PHP | 8.2+ (diuji pada 8.2.12) |
| Laravel | 12.x |
| Livewire | 4.x |
| Flux UI | 2.x |
| Volt | 1.x |
| Tailwind CSS | 4.x |
| Vite | 6.x |
| nwidart/laravel-modules | 12.x (arsitektur HMVC) |
| Pest | 3.x |
| Basis data | SQLite (pengembangan), MySQL/PostgreSQL juga didukung |

---

## Persyaratan Sistem

- PHP 8.2 atau lebih baru dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `curl`
- Composer 2.x
- Node.js 20+ dan npm

---

## Instalasi

```bash
# 1. Pasang dependensi PHP dan JavaScript
composer install
npm install

# 2. Siapkan berkas environment
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# 3. Siapkan basis data SQLite
#    Berkas database/database.sqlite sudah tersedia di repositori.
#    Bila belum ada, buat dengan: type nul > database\database.sqlite  (Windows)
#                                 touch database/database.sqlite       (Linux/macOS)

# 4. Jalankan migrasi sekaligus isi data contoh
php artisan migrate:fresh --seed

# 5. Buat tautan penyimpanan publik (untuk unggah logo)
php artisan storage:link

# 6. Bangun aset frontend
npm run build
```

### Menjalankan aplikasi

```bash
# Opsi 1 — server PHP bawaan
php artisan serve
# Buka http://localhost:8000

# Opsi 2 — semua layanan sekaligus (server + queue + log + vite)
composer dev
```

Untuk pengembangan aktif dengan *hot reload* aset, jalankan `npm run dev`
di terminal terpisah bersama `php artisan serve`.

---

## Akun Demo

Seluruh akun hasil seeder memakai kata sandi **`password`**.

| Peran | Email | Akses |
|---|---|---|
| Administrator | `admin@prim.test` | Seluruh panel `/admin` |
| Pelanggan | `ahmadi@prim.test` | Katalog, transaksi, langganan |
| Pelanggan | `hidayatunnisa@prim.test` | Katalog, transaksi, langganan |
| Pelanggan | `najwa@prim.test` | Katalog, transaksi, langganan |
| Pelanggan | `misliani@prim.test` | Katalog, transaksi, langganan |

### Data contoh yang disediakan

- **3 penyedia fiktif**: Nusantara Stream, Sonata Music, Cendekia Learn
- **3 kategori**: Hiburan & Streaming, Produktivitas & Kreativitas, Edukasi & Pembelajaran
- **6 layanan** dengan total **15 paket** beragam harga dan durasi
- **7 transaksi** (4 berhasil, 2 menunggu pembayaran, 1 gagal) beserta langganannya

> Seluruh nama penyedia dan layanan bersifat **fiktif** dan dibuat hanya untuk
> keperluan demonstrasi akademik.

---

## Peta URL

| URL | Modul | Akses |
|---|---|---|
| `/` | Landing | Publik |
| `/katalog` | Catalog | Publik |
| `/katalog/{slug}` | Catalog | Publik |
| `/bandingkan` | Catalog | Publik |
| `/checkout/{plan}` | Transaction | Login + terverifikasi |
| `/transaksi` | Transaction | Login + terverifikasi |
| `/transaksi/{order_code}` | Transaction | Login (hanya milik sendiri) |
| `/langganan` | Subscription | Login + terverifikasi |
| `/langganan/{id}/perpanjang` | Subscription | Login (hanya milik sendiri) |
| `/admin` | Admin | Peran `admin` |
| `/admin/layanan` | Admin | Peran `admin` |
| `/admin/layanan/{slug}/paket` | Admin | Peran `admin` |
| `/admin/kategori` | Admin | Peran `admin` |
| `/admin/provider` | Admin | Peran `admin` |
| `/admin/pengguna` | Admin | Peran `admin` |
| `/admin/transaksi` | Admin | Peran `admin` |
| `/settings/profile`, `/settings/password`, `/settings/appearance` | Starter kit | Login |

---

## Struktur Proyek

```
prim/
├── app/
│   ├── Casts/                  Cast enum yang tahan nilai null
│   ├── Console/Commands/       prim:expire-transactions
│   ├── Enums/                  Role, TransactionStatus, SubscriptionStatus
│   ├── Http/Middleware/        EnsureUserHasRole (alias 'role')
│   ├── Models/                 Provider, Category, Service, Plan,
│   │                           Transaction, Subscription, Review, User
│   ├── Policies/               TransactionPolicy, SubscriptionPolicy
│   ├── Providers/              Binding PaymentGateway, registrasi Livewire modul
│   ├── Services/
│   │   ├── Payment/            PaymentGateway, MockPaymentGateway, PaymentResult
│   │   └── TransactionService.php
│   └── Support/                Trait StoresUploadedImages
├── database/
│   ├── factories/              Factory seluruh model
│   ├── migrations/             Skema basis data
│   └── seeders/                CatalogSeeder, DemoDataSeeder
├── Modules/                    Arsitektur HMVC
│   ├── Admin/                  Dashboard + CRUD master data
│   ├── Catalog/                Katalog publik, detail, komparasi
│   ├── Subscription/           Daftar & perpanjangan langganan
│   └── Transaction/            Checkout, pembayaran, riwayat
└── tests/Feature/              Test Pest per modul
```

### Konvensi modul HMVC

Setiap modul menaruh komponen Livewire dan view-nya sebagai berikut:

```
Modules/<Nama>/
├── app/Livewire/                     namespace Modules\<Nama>\Livewire
├── resources/views/livewire/         view komponen
├── resources/views/                  view lain (mis. komponen modal admin)
├── routes/web.php                    route web modul
└── Providers/                        service provider modul
```

Komponen dirujuk memakai **nama bernamespace**, misalnya
`Route::livewire('katalog', 'catalog::service-list')`. Pendaftaran namespace
dilakukan di `App\Providers\ModuleLivewireServiceProvider`.

---

## Model Data

```
Provider 1───N Service 1───N Plan 1───N Transaction 1───1 Subscription
                   │                          │
                   └──N Review               User
```

| Tabel | Keterangan |
|---|---|
| `users` | Pengguna dengan kolom `role` (`user`/`admin`/`provider`) |
| `providers` | Penyedia layanan premium |
| `categories` | Kategori layanan |
| `services` | Layanan premium, terhubung ke provider dan kategori |
| `plans` | Paket langganan: harga, durasi, jumlah perangkat, fitur (JSON) |
| `transactions` | Transaksi dengan kode pesanan, status, dan batas waktu |
| `subscriptions` | Masa aktif langganan hasil transaksi yang berhasil |
| `reviews` | Ulasan dan rating pengguna terhadap layanan |

Penyimpanan harga memakai satuan **rupiah penuh** (bilangan bulat), bukan desimal.

---

## Pengujian

```bash
# Seluruh test
php artisan test

# Test per modul
php artisan test --filter=CatalogTest
php artisan test --filter=TransactionTest
php artisan test --filter=SubscriptionTest
php artisan test --filter=AdminTest
php artisan test --filter=SeededRoutesTest

# Periksa dan perbaiki gaya kode
./vendor/bin/pint --test
./vendor/bin/pint
```

Cakupan pengujian saat ini: **147 test** yang mencakup alur katalog, transaksi,
langganan, panel admin, otorisasi antar pengguna, dan verifikasi seluruh rute
dengan data seeder sungguhan.

---

## Catatan Simulasi Pembayaran

PRIM **belum terhubung ke penyedia pembayaran nyata**. Pembayaran disimulasikan
melalui `MockPaymentGateway`, sehingga:

- Pada halaman detail transaksi tersedia tombol **"Simulasikan Pembayaran Berhasil"**
  dan **"Simulasikan Pembayaran Gagal"**.
- Administrator juga dapat memverifikasi transaksi secara manual dari
  `/admin/transaksi`.

Untuk beralih ke penyedia sungguhan (mis. Midtrans), cukup buat implementasi baru
dari antarmuka `App\Services\Payment\PaymentGateway` lalu ubah binding-nya di
`App\Providers\AppServiceProvider`. Kode pemanggil tidak perlu diubah.

---

## Pemecahan Masalah

**Halaman menampilkan "Vite manifest not found"**
Jalankan `npm run build` (produksi) atau `npm run dev` (pengembangan).

**Logo tidak muncul setelah diunggah**
Pastikan `php artisan storage:link` sudah dijalankan.

**Komponen Livewire tidak ditemukan**
Jalankan `composer dump-autoload` lalu `php artisan optimize:clear`.
Pemetaan PSR-4 setiap modul ada di `composer.json` bagian `autoload.psr-4`.

**Perubahan route/view tidak terlihat**
Jalankan `php artisan optimize:clear`.

**Ingin mengulang data dari awal**
Jalankan `php artisan migrate:fresh --seed`.

---

## Lisensi

Proyek akademik untuk mata kuliah Rekayasa Perangkat Lunak.
© 2026 Ilmu Komputer, FMIPA, Universitas Lambung Mangkurat.
