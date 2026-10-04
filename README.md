# PRIM — Platform Digital Aggregator Layanan Premium

Aplikasi web untuk **mencari, membandingkan, dan berlangganan** layanan digital premium
dalam satu wadah. Proyek ini merupakan implementasi dari dokumen *Software Requirements
Specification (SRS)* Kelompok 7, Ilmu Komputer FMIPA Universitas Lambung Mangkurat.

---

## Menjalankan dengan cepat

```bash
cd prim
php setup.php     # memasang dependensi, basis data, dan aset sekaligus
php artisan serve # lalu buka http://127.0.0.1:8000
```

Panduan lengkap langkah demi langkah beserta pemecahan masalah tersedia di
**[TUTORIAL-MENJALANKAN.txt](TUTORIAL-MENJALANKAN.txt)**.

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
- Katalog 27 layanan dengan **varian paket** (mis. "1 Perangkat", "Bulanan") dan tag periode
- Pencarian layanan berdasarkan nama, tagline, dan deskripsi
- Filter kategori dan penyedia, pengurutan harga/nama
- Pita **diskon** dan penanda **preorder** pada kartu layanan
- Halaman detail layanan: daftar paket, harga, fitur, dan ulasan pengguna
- Panduan **Cara Berlangganan** beserta 11 kanal pembayaran
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
  pesanan, diagram 14 hari, produk terlaris, langganan yang akan berakhir
- Manajemen pesanan dengan verifikasi pembayaran manual (tandai selesai/gagal)
- Manajemen pengguna: cari, ubah peran, dan ubah **status** (aktif/non-aktif/suspend)
- Manajemen produk: CRUD penyedia, kategori, layanan (dengan unggah logo), dan paket
- Manajemen pembayaran per kanal
- **Laporan** dengan diagram pendapatan bulanan, komposisi status, dan insight
- **Pengaturan** profil dan 8 saklar notifikasi

### Akun pengguna (wajib login)
- **Pusat pesanan** dengan 9 tab status dan pencarian
- **Kode Login**: kredensial akun layanan per pesanan berhasil, tersembunyi sampai diminta
- **Masuk dengan kode OTP** (verifikasi perangkat)
- Pengaturan profil, kata sandi, dan tampilan

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

- PHP 8.2 atau lebih baru dengan ekstensi `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `tokenizer`, `xml`, `curl`, `fileinfo`, `zip`
- Composer 2.x
- Node.js 18+ (disarankan 20+) dan npm

---

## Instalasi

### Cara tercepat — satu perintah

```bash
cd prim
php setup.php
```

Skrip `setup.php` mengerjakan seluruh langkah di bawah sekaligus: menyiapkan
`.env`, memasang dependensi, membuat basis data, mengisi data contoh, dan
membangun aset antarmuka. Setelah selesai, lanjut ke **Menjalankan aplikasi**.

### Cara manual

```bash
# 1. Pasang dependensi PHP dan JavaScript
composer install
npm install

# 2. Siapkan berkas environment
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# 3. Siapkan basis data SQLite
#    database/database.sqlite tidak disertakan di repositori (diabaikan Git),
#    jadi berkasnya perlu dibuat:
#      Windows (Command Prompt) : type nul > database\database.sqlite
#      Windows (PowerShell)     : New-Item database\database.sqlite -ItemType File
#      Linux / macOS            : touch database/database.sqlite

# 4. Jalankan migrasi sekaligus isi data contoh
php artisan migrate:fresh --seed

# 5. Buat tautan penyimpanan publik (untuk unggah logo)
php artisan storage:link

# 6. Bangun aset frontend (WAJIB — aset tidak disertakan di repositori)
npm run build
```

> **Penting:** langkah `npm run build` tidak boleh dilewati. Berkas
> `public/build/` diabaikan Git, sehingga tanpa langkah ini tampilan akan polos
> tanpa warna dan tanpa logo.

### Menjalankan aplikasi

```bash
# Opsi 1 — server PHP bawaan
php artisan serve
# Buka http://127.0.0.1:8000

# Opsi 2 — semua layanan sekaligus (server + queue + log + vite)
composer dev
```

Untuk pengembangan aktif dengan *hot reload* aset, jalankan `npm run dev`
di terminal terpisah bersama `php artisan serve`.

---

## Akun Demo

Seluruh akun hasil seeder memakai kata sandi **`password`**.

### Administrator

| Email | Peran | Akses |
|---|---|---|
| `admin@prim.com` | Administrator (Super Admin) | Seluruh panel `/admin` |
| `admin@prim.test` | Administrator | Seluruh panel `/admin` |

Cara masuk sebagai admin: buka `/login` → masukkan kredensial di atas → setelah
masuk, klik avatar di kanan atas lalu pilih **Panel Pengelola**, atau langsung
buka `/admin`.

### Pelanggan

| Email | Catatan |
|---|---|
| `andi@prim.test` | Paling lengkap — 2 transaksi, 2 kode login |
| `rina@prim.test` | 2 transaksi |
| `budi@prim.test` | 2 transaksi, 1 kode login |
| `dewi@prim.test` | 2 transaksi, 1 kode login |

Pelanggan lain (semuanya `@prim.test`, sandi `password`): `citra`, `doni`, `eko`,
`faisal`, `fitri`, `fitriani`, `guntur`, `hendra`, `mega`, `maya`, `rizky`, `siti`.

Dua akun berikut sengaja tidak aktif untuk menguji kolom Status di panel
pengelola:

| Email | Status |
|---|---|
| `maya@prim.test` | Suspend |
| `mega@prim.test` | Non-aktif |

### Data contoh yang disediakan

- **27 layanan** dengan **43 varian paket** (varian, harga, tag periode, diskon, preorder)
- **5 kategori**: Streaming, Musik, AI, Produktivitas, Penyimpanan, Edukasi
- **18 akun**: 2 administrator dan 16 pelanggan dengan beragam status
- **20 transaksi** pada berbagai status pesanan, beserta langganan dan kredensial kode login

> Nama merek layanan mengikuti desain Figma dan dipakai hanya untuk keperluan
> demonstrasi akademik.

---

## Peta URL

### Publik (tanpa login)

| URL | Keterangan |
|---|---|
| `/` | Beranda |
| `/katalog` | Katalog layanan (grid 4 kolom, pencarian, filter, urutan) |
| `/katalog/{slug}` | Detail layanan, mis. `/katalog/netflix` |
| `/bandingkan` | Perbandingan layanan |
| `/cara-berlangganan` | Lima langkah berlangganan + metode pembayaran |
| `/laporan-kendala` | Bantuan melalui WhatsApp |
| `/login`, `/register` | Masuk dan daftar |
| `/kode-login` | Masuk dengan kode OTP (verifikasi perangkat) |
| `/forgot-password`, `/reset-password/{token}` | Pemulihan kata sandi |

### Pelanggan (perlu login)

| URL | Keterangan |
|---|---|
| `/profil/pesanan` | Pusat pesanan (9 tab status) |
| `/profil/kode-login` | Kredensial akun layanan |
| `/langganan` | Daftar langganan |
| `/langganan/{id}/perpanjang` | Perpanjangan langganan |
| `/transaksi` | Riwayat transaksi |
| `/transaksi/{order_code}` | Detail pesanan |
| `/checkout/{plan}` | Konfirmasi pemesanan |
| `/settings/profile`, `/settings/password`, `/settings/appearance` | Pengaturan akun |

### Pengelola (peran `admin`)

| URL | Keterangan |
|---|---|
| `/admin` | Dashboard |
| `/admin/pesanan` | Manajemen pesanan |
| `/admin/pembayaran` | Manajemen pembayaran |
| `/admin/pengguna` | Manajemen pengguna |
| `/admin/produk` | Manajemen produk |
| `/admin/produk/{service}/paket` | Pengelolaan paket per layanan |
| `/admin/kategori`, `/admin/penyedia` | Master data pendukung |
| `/admin/laporan` | Laporan |
| `/admin/pengaturan/profil`, `/admin/pengaturan/notifikasi` | Pengaturan pengelola |


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
