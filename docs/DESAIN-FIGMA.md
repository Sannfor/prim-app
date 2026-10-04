# PRIM — Sinkronisasi Tampilan dengan Desain Figma

Catatan pelaksanaan penyesuaian tampilan aplikasi PRIM terhadap berkas Figma
**PRIM_KELOMPOK7** (`UwwRCAWc14xrGYLtCOb5yl`).

Rujukan pendukung:

- `docs/DESIGN-TOKENS.md` — palet, tipografi, radius, dan bayangan hasil ekstraksi.
- `docs/figma/DESIGN-MAP.md` — peta frame Figma ke halaman aplikasi.
- `docs/figma/ASSET-NODES.txt` — daftar node Figma yang diekspor.

---

## 1. Fondasi Desain

| Berkas | Perubahan |
|---|---|
| `resources/css/app.css` | Blok `@theme` diisi token desain (ungu `#534AB7`, `#6C63D5`, `#D3CFFF`, `#8B5CF6`, netral, warna status). Ditambah kelas komponen `.prim-*` (tombol, kolom masukan, kartu, lencana, pita diskon, kartu langkah, tabel, sidebar). Ditambah `@source "../../Modules/*/resources/views"` agar kelas Tailwind di dalam modul ikut dipindai. |
| `resources/views/partials/head.blade.php` | Memuat **DM Sans** (judul), **Inter** (isi), dan **Poppins** (autentikasi) menggantikan Instrument Sans. |
| `app/Support/PrimAsset.php` | Penunjuk lokasi aset: logo merek per slug dari `public/images/brands`, aset halaman dari `resources/images/figma` lewat Vite. |
| `vite.config.js` | Seluruh PNG pada `resources/images/figma` didaftarkan sebagai entry agar dibundel ber-hash; pemantauan berkas mencakup view modul. |

Komponen Blade baru:

`brand-logo`, `prim-clouds`, `service-logo`, `payment-tile`, `status-badge`,
`layouts/auth`, `layouts/admin`.

---

## 2. Halaman Publik

| Halaman | Rute | Berkas | Perubahan |
|---|---|---|---|
| Beranda | `/` | `views/welcome.blade.php` | Hero ungu penuh, judul DM Sans, tombol pil, transisi awan, layanan populer, keunggulan |
| Katalog | `/katalog` | `Modules/Catalog/.../service-list.blade.php` | Grid **4 kolom**, kartu dengan logo merek, varian paket, tag periode, pita diskon/preorder, tombol Pesan penuh lebar |
| Detail Layanan | `/katalog/{slug}` | `.../service-detail.blade.php` | Disesuaikan ke token baru |
| Bandingkan | `/bandingkan` | `.../comparison.blade.php` | Disesuaikan ke token baru |
| **Cara Berlangganan** | `/cara-berlangganan` | `Modules/Catalog/.../how-to-subscribe.blade.php` | **Halaman baru**: 5 kartu langkah radius 117px + 11 kanal pembayaran |
| **Laporan Kendala** | `/laporan-kendala` | `views/support/report.blade.php` | **Halaman baru**: kanal WhatsApp dengan pesan siap kirim |

## 3. Halaman Autentikasi (Poppins, latar gradien)

| Halaman | Rute | Catatan |
|---|---|---|
| Login | `/login` | Kartu putih radius 30, latar `linear-gradient(197.5deg, …)` |
| Daftar | `/register` | Ditambah kolom **Nomor WhatsApp** dan persetujuan syarat |
| Lupa Kata Sandi | `/forgot-password` | Kolom email dengan tombol kirim di dalam kolom |
| Reset Kata Sandi | `/reset-password/{token}` | — |
| Konfirmasi Password | `/confirm-password` | — |
| Verifikasi Email | `/verify-email` | — |
| **Kode Login (OTP)** | `/kode-login` | **Alur baru**: minta kode 6 angka lalu tukarkan untuk masuk |

## 4. Halaman Akun

| Halaman | Rute | Catatan |
|---|---|---|
| **Pesanan Saya** | `/profil/pesanan` | **Halaman baru**: panel akun, 9 tab status, pencarian, daftar pesanan. `/dashboard` dialihkan ke sini |
| **Kode Login** | `/profil/kode-login` | **Halaman baru**: kredensial akun per pesanan berhasil, tersembunyi sampai diminta |
| Checkout / Riwayat / Detail Transaksi | `/checkout/{plan}`, `/transaksi`, `/transaksi/{kode}` | Disesuaikan ke token baru |
| Langganan / Perpanjang | `/langganan`, `/langganan/{id}/perpanjang` | Disesuaikan ke token baru |
| Pengaturan (Profil/Password/Tampilan) | `/settings/*` | Navigasi tab, kartu putih, label Bahasa Indonesia |

## 5. Panel Pengelola

Tata letak baru `layouts/admin.blade.php`: **sidebar ungu 280 px** dengan
menu Dashboard, Pesanan, Pengguna, Produk, Pembayaran, Laporan, lalu
Pengaturan dan Keluar di bawah; header konten memuat tanggal, lonceng
notifikasi berhitung, avatar, nama, dan peran.

| Halaman | Rute |
|---|---|
| Dashboard | `/admin` |
| Pesanan | `/admin/pesanan` |
| Pembayaran | `/admin/pembayaran` |
| Pengguna | `/admin/pengguna` |
| Produk | `/admin/produk` |
| Paket per layanan | `/admin/produk/{service}/paket` |
| Kategori & Penyedia | `/admin/kategori`, `/admin/penyedia` |
| **Laporan** | `/admin/laporan` |
| **Pengaturan profil** | `/admin/pengaturan/profil` |
| **Pengaturan notifikasi** | `/admin/pengaturan/notifikasi` |

Rute lama (`/admin/transaksi`, `/admin/layanan`) tetap tersedia sebagai alias
agar tautan yang sudah dipakai tidak putus.

---

## 6. Perubahan Basis Data

| Migrasi | Isi |
|---|---|
| `..._add_design_fields_to_plans_table` | `variant_group`, `periods_label`, `discount_percent`, `compare_at_price`, `is_preorder`, `stock` |
| `..._add_status_fields_to_users_table` | `status`, `last_login_at`, `address` |
| `..._add_payment_label_to_transactions_table` | `payment_method_label` |
| `..._create_service_credentials_table` | Kredensial akun per transaksi (kode login, kata sandi, profil, PIN) |
| `..._add_settings_to_users_table` | `settings` (JSON) untuk preferensi notifikasi |

Enum:

- `App\Enums\TransactionStatus` — ditambah `processed`, `accepted`,
  `waiting_process`, `follow_up`, `proof_renewal`, `proof_revision`, `grace`;
  label dan kelompok warna mengikuti desain.
- `App\Enums\UserStatus` — **baru**: `aktif`, `non_aktif`, `suspend`.

## 7. Data Contoh (Seeder)

`CatalogSeeder` diisi ulang memakai **27 layanan** dari frame "Layanan" beserta
kelompok varian, harga, tag periode, diskon, dan penanda preorder yang persis
seperti desain. `DemoDataSeeder` diisi ulang memakai 16 nama pelanggan, kanal
pembayaran, status pesanan, dan status akun dari frame admin, plus contoh
kredensial kode login.

Akun demo (kata sandi `password`):

- `admin@prim.com` (Super Admin)
- `admin@prim.test` (Administrator)
- `andi@prim.test`, `rina@prim.test`, … (pelanggan)

## 8. Aset dari Figma

| Aset | Berkas sumber | Ukuran asli | Lokasi |
|---|---|---|---|
| Logo PRIM | node `37:3` | 633×348 px (3×) | `resources/images/figma/logo-prim.png` |
| Ilustrasi hero Beranda | node `48:2` ("Prim") | 2154×1290 px (3×) | `resources/images/figma/hero-beranda.png` |
| Transisi awan | node `19:9` ("Awan") | 3456×624 px (2×) | `resources/images/figma/awan.png` |
| Ilustrasi 5 langkah | node Step 1–5 | 176–196 px lebar | `resources/images/figma/step-1.png` … `step-5.png` |
| Logo 27 merek | frame `Akunmu *` | 3× | `public/images/brands/<slug>.png` |
| Logo 11 kanal pembayaran | node `90:500` ("payments") | sprite 2048×407 px (4×) | `public/images/payments/<slug>.png` |

Logo merek dan logo kanal pembayaran disajikan dari `public/` karena namanya
disusun dari slug (dinamis). Seluruh aset halaman pada `resources/images/figma`
didaftarkan sebagai entry Vite sehingga dirujuk lewat manifest dengan nama
ber-hash.

Kesebelas logo kanal pembayaran pada Figma disimpan sebagai **satu berkas
sprite**. Pemotongannya dikerjakan `tools/slice-payment-sprite.mjs`: skrip itu
membaca PNG sprite, mendeteksi kotak tiap logo dari piksel yang tidak
transparan, **memangkas sisa margin transparan** agar gambar hanya berisi logo,
lalu menuliskannya sebagai berkas terpisah memakai encoder PNG sendiri (tanpa
dependensi tambahan). Jalankan ulang bila sprite di Figma berubah:

```
node tools/slice-payment-sprite.mjs docs/figma/assets/payments-sprite.png prim/public/images/payments
```

Kartu kanal pembayaran memakai lebar fleksibel dari grid induknya (lima kartu per
baris pada layar lebar) dan logo dibatasi dua arah, sehingga proporsi merek yang
berbeda tetap rapi di dalam kartu dan tidak saling menempel.

Ukuran tampilan mengikuti proporsi desain:

| Pemakaian | Figma | Kelas | Tinggi nyata |
|---|---|---|---|
| Navbar publik | 211×116 px pada kanvas 1728 | `size="lg"` | 64 px |
| Sidebar admin | 167×50 px | `size="md"` | 48 px |
| Kartu autentikasi | 263×145 px pada kartu 661 | `size="xl"` | 96 px |
| Ilustrasi hero | 718×430 px pada kanvas 1728 | `max-w-[820px]` | proporsional |

**Belum tersedia dari Figma:** ilustrasi khusus langkah 2 dan langkah 4. Langkah 2
memakai ilustrasi langkah 1 dan langkah 4 memakai ilustrasi langkah 3 sampai
berkas khususnya tersedia.

## 9. Verifikasi

- 32 rute utama diuji merender tanpa galat (tamu, pelanggan, admin).
- Seluruh aset desain terkonfirmasi muncul di HTML: logo PRIM, ilustrasi hero,
  awan, 27 logo merek, dan 11 logo kanal pembayaran.
- `npm run build` menghasilkan CSS 246 kB (gzip 34 kB), 6 aset gambar ber-hash,
  dan seluruh kelas Tailwind dari dalam modul ikut terbundel.
- `php artisan test` — 157 pengujian lulus.

