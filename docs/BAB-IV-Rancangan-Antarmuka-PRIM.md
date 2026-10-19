# CATATAN SEBELUM SALIN-TEMPEL

Berkas ini berisi isi untuk **BAB 4 — RANCANGAN ANTARMUKA** pada dokumen
*SRS Aplikasi PRIM Kelompok 7.docx*.

## Cara saya menyusun ini

Saya membaca berkas `SRS Aplikasi PRIM Kelompok 7.docx` yang ada di komputer Anda
dengan mengekstrak isinya langsung dari XML Word, bukan menebak dari Google Docs,
lalu membandingkannya dengan aplikasi PRIM yang sudah berjalan. Dari situ saya tahu
persis susunan Bab 4 Anda:

| Keadaan di dokumen Anda | Isi |
|---|---|
| Judul bab | `# Rancangan Antarmuka` (bukan "Rancangan Sistem") |
| Kalimat pengantar | `<rancangan antarmuka digambarkan dan diterangkan di bagian ini>` |
| Sub-bab | `## <Nama Modul 1>`, `## <Nama Modul 2>`, `## <Nama Modul 3>` — **masih placeholder** |
| Isi tiap sub-bab | Gambar antarmuka + keterangan `<terangkan gambar diatas, seperti terdapat fungsi apa aja dan lain-lain.>` |
| Gambar | `Gambar 4-1.` sampai `Gambar 4-5.` — 4 gambar |
| Tabel | Belum ada tabel di Bab 4 |

## Apa yang berubah pada revisi ini

Versi sebelumnya memuat **11 gambar** dan teksnya ditulis pada 4 Oktober, sebelum
aplikasi menerima beberapa penambahan fitur. Revisi ini menyelaraskan dokumen
dengan aplikasi yang benar-benar berjalan sekarang:

1. **Seluruh 11 gambar lama diambil ulang** dari aplikasi terkini, sehingga tata
   letak hasil penyempurnaan kontainer (`1280px`, tepi 16/24/48px) dan kartu
   layanan yang lebih rapat sudah terlihat.
2. **19 gambar baru ditambahkan** untuk halaman yang sebelumnya belum terwakili:
   perbandingan layanan, formulir ulasan, penerapan kode voucher, struk digital,
   riwayat transaksi, notifikasi pembeli, perpanjangan langganan, pemantauan
   langganan, pengelolaan paket, pengelolaan voucher, pemantauan pembayaran,
   laporan admin, dua halaman pengaturan pengelola, notifikasi pengelola, serta
   **tiga halaman panel penyedia** (Gambar 4-28 sampai 4-30).
3. **Nomor gambar disusun ulang** agar berurutan mengikuti modul. Karena itu
   sebagian nomor lama bergeser — lihat tabel pemetaan di bawah.
4. **Halaman Pusat Pesanan** yang dulu hanya disimpan sebagai gambar cadangan
   (`tambahan-pusat-pesanan.png`) kini menjadi **Gambar 4-11**.
5. **Halaman Perbandingan Layanan yang dulu belum punya gambar kini punya**
   (Gambar 4-5), karena pengambilan gambar sekarang dapat menekan tombol
   bandingkan lebih dahulu sebelum menangkap layar.
6. **Seluruh keterangan gambar ditulis ulang** berdasarkan isi tangkapan layar
   yang baru, bukan berdasarkan ingatan.
7. **Gambar dipaksa mode terang.** Chrome tanpa antarmuka mewarisi preferensi
   sistem; pada mesin yang memakai mode gelap, varian `dark:` beberapa komponen
   ikut aktif sehingga satu kartu tampil gelap di tengah halaman terang. Media
   `prefers-color-scheme` sekarang dikunci ke `light`.
8. **Peran provider kini punya antarmuka nyata.** Sebelumnya dokumen menyebut tiga
   peran tetapi aplikasi hanya punya panel untuk administrator; akun berperan
   provider tidak memiliki halaman apa pun. Sekarang ada panel penyedia terpisah
   pada `/penyedia` yang hanya dapat dipakai akun penyedia dan hanya memuat
   layanan serta paket milik penyedia tersebut. Bab 4 bagian 4.4 dan Bab 3 Modul 4
   ikut disesuaikan.
9. **Dua use case yang sebelumnya hanya ada di diagram kini punya fiturnya.**
   Use case "Menulis ulasan dan rating" (Modul Catalog) dan "Memonitor seluruh
   langganan" (Modul Subscription) dulu tidak punya implementasi sama sekali.
   Sekarang Pelanggan dapat menulis ulasan pada halaman detail layanan bila sudah
   memiliki transaksi berhasil, dan administrator punya halaman pemantauan
   langganan pada `/admin/langganan`. Keduanya dijelaskan pada Gambar 4-4 dan
   Gambar 4-18.

### Pemetaan nomor gambar lama ke nomor baru

| Nomor lama | Berkas lama | Nomor baru | Berkas baru |
|---|---|---|---|
| 4-1 | `gambar-4-1-beranda.png` | **4-1** | `gambar-4-1-beranda.png` |
| 4-2 | `gambar-4-2-katalog.png` | **4-2** | `gambar-4-2-katalog.png` |
| 4-3 | `gambar-4-3-detail-layanan.png` | **4-3** | `gambar-4-3-detail-layanan.png` |
| 4-4 | `gambar-4-4-cara-berlangganan.png` | **4-6** | `gambar-4-6-cara-berlangganan.png` |
| 4-5 | `gambar-4-5-konfirmasi-pemesanan.png` | **4-7** | `gambar-4-7-konfirmasi-pemesanan.png` |
| 4-6 | `gambar-4-6-detail-pesanan.png` | **4-9** | `gambar-4-9-detail-pesanan.png` |
| 4-7 | `gambar-4-7-langganan-saya.png` | **4-14** | `gambar-4-14-langganan-saya.png` |
| 4-8 | `gambar-4-8-dashboard.png` | **4-16** | `gambar-4-16-dashboard.png` |
| 4-9 | `gambar-4-9-manajemen-pesanan.png` | **4-17** | `gambar-4-17-manajemen-pesanan.png` |
| 4-10 | `gambar-4-10-manajemen-produk.png` | **4-19** | `gambar-4-19-manajemen-produk.png` |
| 4-11 | `gambar-4-11-manajemen-pengguna.png` | **4-21** | `gambar-4-21-manajemen-pengguna.png` |

Berkas gambar lama yang nomornya sudah tidak dipakai sudah dihapus dari
`docs/screens/` agar folder tidak berisi campuran dua penomoran. Nomor gambar di
dalam dokumen Google Docs perlu disesuaikan dengan tabel di atas.

## Dua penyesuaian yang tetap dipertahankan

1. **Judul dan cakupan.** Draf awal berjudul "Rancangan Sistem" dan memuat
   arsitektur, basis data, serta teknologi. Itu tidak sesuai dengan dokumen Anda,
   yang Bab 4-nya khusus **Rancangan Antarmuka**. Berkas ini mengikuti dokumen.
2. **Jumlah modul.** Dokumen Anda hanya menyediakan tiga sub-bab modul di Bab 4,
   padahal Bab 3 dokumen Anda sendiri sudah memuat **empat** Use Case Diagram.
   Berkas ini memakai **empat sub-bab** agar konsisten dengan Bab 3.

## Langkah menempel ke Google Docs

1. Buka dokumen, gulir ke **Bab 4 Rancangan Antarmuka**.
2. Hapus **seluruh** isi Bab 4 yang lama, termasuk baris `<rancangan antarmuka
   digambarkan dan diterangkan di bagian ini>`, tiga sub-bab `<Nama Modul 1/2/3>`,
   dan semua kalimat `<terangkan gambar diatas, ...>` beserta gambar lamanya.
3. Salin isi Bab 4 dari berkas ini, mulai dari baris `BAB 4` di bawah garis pemisah.
4. Beri gaya **Heading 1** pada "BAB 4 RANCANGAN ANTARMUKA" dan **Heading 2** pada
   keempat sub-bab modul.
5. Sisipkan gambar pada setiap baris penanda penyisipan gambar melalui
   *Insert → Image → Upload from computer*. Berkas yang diminta tertulis pada baris
   penanda itu, misalnya berkas `docs/screens/gambar-4-1-beranda.png`. Setelah gambar
   masuk, **hapus baris penanda itu** agar tidak ikut tercetak.
6. Perbarui nomor dan judul keterangan gambar mengikuti tabel pemetaan di atas.

## Daftar gambar

Tiga puluh gambar berikut sudah tersedia di folder `docs/screens/`. Semua diambil
pada lebar **1440 piksel** dalam mode penuh satu halaman (bukan hanya bagian yang
terlihat di layar) oleh skrip `tools/screenshot.mjs`. Angka pada kolom ukuran
mengikuti berkas yang benar-benar ada; tinggi halaman dapat berubah bila jumlah data
contoh berubah, jadi perbarui tabel ini setelah menjalankan ulang skrip.

| Gambar | Berkas | Ukuran | Halaman |
|---|---|---|---|
| 4-1 | `docs/screens/gambar-4-1-beranda.png` | 1440x2233 | Halaman Beranda |
| 4-2 | `docs/screens/gambar-4-2-katalog.png` | 1440x2122 | Katalog Layanan |
| 4-3 | `docs/screens/gambar-4-3-detail-layanan.png` | 1440x1494 | Detail Layanan |
| 4-4 | `docs/screens/gambar-4-4-ulasan-layanan.png` | 1440x1927 | Formulir Ulasan pada Detail Layanan |
| 4-5 | `docs/screens/gambar-4-5-perbandingan-layanan.png` | 1440x1730 | Bandingkan Layanan |
| 4-6 | `docs/screens/gambar-4-6-cara-berlangganan.png` | 1440x2044 | Panduan Berlangganan |
| 4-7 | `docs/screens/gambar-4-7-konfirmasi-pemesanan.png` | 1440x2129 | Konfirmasi Pemesanan |
| 4-8 | `docs/screens/gambar-4-8-voucher-pemesanan.png` | 1440x2213 | Konfirmasi Pemesanan dengan Voucher |
| 4-9 | `docs/screens/gambar-4-9-detail-pesanan.png` | 1440x1219 | Detail Pesanan dan Metode Pembayaran |
| 4-10 | `docs/screens/gambar-4-10-struk-digital.png` | 1440x1179 | Struk Digital |
| 4-11 | `docs/screens/gambar-4-11-pusat-pesanan.png` | 1440x1653 | Pusat Pesanan |
| 4-12 | `docs/screens/gambar-4-12-riwayat-transaksi.png` | 1440x1051 | Riwayat Transaksi |
| 4-13 | `docs/screens/gambar-4-13-notifikasi-pembeli.png` | 1440x1653 | Notifikasi Pembeli |
| 4-14 | `docs/screens/gambar-4-14-langganan-saya.png` | 1440x1427 | Langganan Saya |
| 4-15 | `docs/screens/gambar-4-15-perpanjang-langganan.png` | 1440x900 | Perpanjang Langganan |
| 4-16 | `docs/screens/gambar-4-16-dashboard.png` | 1440x2094 | Dashboard Pengelola |
| 4-17 | `docs/screens/gambar-4-17-manajemen-pesanan.png` | 1440x1498 | Manajemen Pesanan |
| 4-18 | `docs/screens/gambar-4-18-manajemen-langganan.png` | 1440x1272 | Manajemen Langganan |
| 4-19 | `docs/screens/gambar-4-19-manajemen-produk.png` | 1440x1920 | Manajemen Produk |
| 4-20 | `docs/screens/gambar-4-20-manajemen-paket.png` | 1440x900 | Manajemen Paket |
| 4-21 | `docs/screens/gambar-4-21-manajemen-pengguna.png` | 1440x1318 | Manajemen Pengguna |
| 4-22 | `docs/screens/gambar-4-22-manajemen-voucher.png` | 1440x900 | Manajemen Voucher |
| 4-23 | `docs/screens/gambar-4-23-manajemen-pembayaran.png` | 1440x1441 | Manajemen Pembayaran |
| 4-24 | `docs/screens/gambar-4-24-laporan.png` | 1440x2221 | Laporan Admin |
| 4-25 | `docs/screens/gambar-4-25-pengaturan-profil.png` | 1440x900 | Pengaturan Profil Pengelola |
| 4-26 | `docs/screens/gambar-4-26-pengaturan-notifikasi.png` | 1440x1102 | Pengaturan Notifikasi Pengelola |
| 4-27 | `docs/screens/gambar-4-27-notifikasi-pengelola.png` | 1440x2094 | Notifikasi Pengelola |
| 4-28 | `docs/screens/gambar-4-28-dashboard-penyedia.png` | 1440x972 | Dashboard Penyedia |
| 4-29 | `docs/screens/gambar-4-29-layanan-penyedia.png` | 1440x900 | Layanan Saya (Penyedia) |
| 4-30 | `docs/screens/gambar-4-30-paket-penyedia.png` | 1440x900 | Paket Layanan (Penyedia) |

## Cara menghasilkan ulang gambar

Bila tampilan aplikasi berubah, seluruh gambar dapat dibuat ulang sekaligus:

```powershell
# dijalankan dari akar proyek RPL
powershell -ExecutionPolicy Bypass -File tools\buat-gambar-bab4.ps1
```

Skrip itu, secara berurutan:

1. menulis token sementara ke `prim/.env` untuk rute bantuan `/_dev/login`,
2. membuat data contoh — satu pesanan yang masih menunggu pembayaran
   (`prim/tools/buat-pesanan-contoh.php`), notifikasi pembeli
   (`buat-notifikasi-contoh.php`), membersihkan catatan pemakaian voucher pembeli
   contoh (`buat-voucher-contoh.php`) agar potongan voucher terlihat, menyiapkan
   ulasan contoh sehingga formulir ulasan tampil (`buat-ulasan-contoh.php`), dan
   memastikan akun penyedia contoh tertaut ke satu penyedia (`ProviderUserSeeder`),
3. menyalakan server pengembangan `prim`,
4. menangkap layar 30 halaman sebagai pengunjung, pelanggan (`andi@prim.test`),
   pengelola (`admin@prim.com`), dan penyedia (`provider@prim.test`),
5. memulihkan `.env`, mematikan server, menghapus gambar bernomor lama, lalu
   menyalin hasilnya ke `prim/docs/screens` agar kedua salinan tetap sama.

Skrip melaporkan setiap berkas beserta ukuran pikselnya, sehingga tabel daftar
gambar di atas mudah diperbarui. Pilihan lain: `-OutDir <path>`, `-NoSample`
(melewati pembuatan data contoh), dan `-NoSync` (tidak menyalin ke `prim/docs`).

Tombol yang perlu ditekan sebelum menangkap layar ditulis pada daftar halaman di
dalam skrip, misalnya `button[wire\:click='toggleCompare(18)']` untuk menandai
layanan pada halaman perbandingan dan `button[aria-label='Notifikasi']` untuk
membuka daftar jatuh notifikasi.

---

# BAB 4
# RANCANGAN ANTARMUKA

Rancangan antarmuka Aplikasi PRIM (Platform Aggregator Fitur Premium) disusun
mengikuti berkas desain kelompok pada Figma. Setiap halaman dirancang dengan satu
sistem warna dan satu skala tipografi yang sama agar tampilan konsisten pada seluruh
modul. Warna utama yang dipakai adalah ungu `#534AB7`, dengan latar permukaan navbar
`#D3CFFF` dan latar halaman `#F8F9FA`. Teks memakai DM Sans untuk judul dan Inter
untuk isi. Seluruh halaman dirancang untuk lebar layar 1440 piksel.

Bab ini memaparkan rancangan antarmuka pada keempat modul sistem, yaitu **Catalog**
(4.1), **Transaction** (4.2), **Subscription** (4.3), dan **Admin** (4.4).


---

## 4.1 Catalog

Berikut ini adalah rancangan antarmuka pada modul Catalog. Modul ini dipakai oleh Pengunjung tanpa perlu masuk ke akun, sehingga seluruh halamannya dirancang agar dapat dipahami dalam sekali lihat. Bilah navigasi dan footer yang sama dipakai pada seluruh halaman modul ini agar pengunjung tidak kehilangan orientasi ketika berpindah halaman.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-1-beranda.png]]

**Gambar 4-1. Antarmuka untuk fitur Halaman Beranda.**

Gambar 4-1 menunjukkan halaman beranda yang menjadi titik masuk aplikasi. Bagian
atas memuat bilah navigasi dengan menu Beranda, Layanan, Cara Berlangganan, Laporan
Kendala, dan tombol Login. Bagian hero memuat judul utama "Satu Platform, Semua
Layanan Premium Favoritmu", kalimat pengantar, tombol "Lihat Layanan" beserta tautan
"Cara berlangganan", dan ilustrasi pendukung di sisi kanan, dengan transisi awan
pada kaki bagian. Di bawahnya ditampilkan bagian Layanan Populer berisi delapan kartu
layanan yang memuat lambang merek, nama layanan, kategori, dan harga termurah per
bulan, disertai tautan menuju katalog lengkap. Bagian "Kenapa memakai PRIM?" memuat
empat kartu alasan, yaitu Pencarian Terpusat, Skema Harga Transparan, Pembayaran
Fleksibel, dan Riwayat Lengkap. Halaman dilanjutkan dengan bagian "Siap
berlangganan?" yang memuat tombol "Mulai Jelajahi" dan "Daftar Sekarang", lalu
ditutup dengan footer yang memuat tautan navigasi, kanal bantuan, dan informasi
kontak.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-2-katalog.png]]

**Gambar 4-2. Antarmuka untuk fitur Menampilkan Katalog Layanan (F-03), Pencarian Layanan (F-04), serta Filter dan Kategori Layanan (F-05).**

Gambar 4-2 menunjukkan halaman katalog layanan. Pada bagian atas terdapat judul
halaman, kolom pencarian "Cari Produk", serta dua pilihan penyaring, yaitu daftar
kategori dan pilihan pengurutan hasil. Di bawahnya terdapat keterangan jumlah layanan
yang sedang ditampilkan. Layanan ditampilkan dalam susunan kartu empat kolom. Setiap
kartu memuat lambang merek layanan, nama layanan, daftar varian paket beserta harga
per bulan dan penanda periode tagihan yang tersedia (misalnya "1, 2, 3 bln" atau
"12 bln"), tautan "Lihat Skema Harga" untuk membuka detail, serta tombol "Pesan"
yang memenuhi lebar kartu. Apabila sebuah varian sedang berdiskon, sebuah pita
bertuliskan "Diskon n%" tampil pada sudut kiri atas kartu, dan apabila paket bersifat
preorder, pita bertuliskan "Preorder" yang tampil. Tombol bundar di kanan bawah
setiap kartu berfungsi menambahkan layanan ke daftar perbandingan; tombol berubah
menjadi tanda centang bila layanan sudah masuk daftar, dan tombol "Bandingkan (n)"
muncul di atas grid beserta jumlah layanan terpilih. Di bagian bawah halaman
tersedia kontrol paginasi beserta keterangan jumlah layanan yang sedang ditampilkan.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-3-detail-layanan.png]]

**Gambar 4-3. Antarmuka untuk fitur Melihat Detail Layanan (F-07).**

Gambar 4-3 menunjukkan halaman detail layanan. Bagian atas dimulai dengan jejak
navigasi yang memuat nama katalog, kategori, dan nama layanan. Di bawahnya terdapat
kartu identitas layanan yang memuat kotak inisial nama layanan, nama layanan, nama
penyedia beserta tautan "Kunjungi situs penyedia", lencana kategori, lencana jumlah
paket aktif, dan lencana nilai rating yang hanya muncul apabila layanan sudah
memiliki ulasan. Pada sudut kanan kartu tersedia tombol "Bandingkan" untuk
memasukkan layanan ke daftar perbandingan dan tombol "Lihat Perbandingan" menuju
halaman perbandingan. Kartu itu juga memuat deskripsi layanan. Bagian utama halaman
menampilkan seluruh varian paket yang aktif dalam bentuk kartu yang memuat nama
kelompok varian, harga, harga setara per bulan, durasi, jumlah perangkat yang dapat
dipakai bersamaan, daftar fitur, serta tombol "Berlangganan" pada setiap kartu.
Apabila layanan sudah memiliki ulasan, bagian bawah halaman memuat ulasan dari
pengguna lain. Halaman ini menjadi tempat membandingkan paket sebelum pelanggan
menentukan pilihan.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-4-ulasan-layanan.png]]

**Gambar 4-4. Antarmuka untuk fitur Menulis Ulasan dan Rating.**

Gambar 4-4 menunjukkan halaman detail layanan yang sama dengan Gambar 4-3, tetapi
dibuka oleh Pelanggan yang sudah pernah membeli layanan tersebut. Pada kartu
identitas layanan, lencana rating kini menampilkan nilai rata-rata, yaitu
"★ 4,5 / 5", karena layanan sudah memiliki ulasan. Bagian "Tulis Ulasan" muncul di
antara daftar paket dan daftar ulasan. Bagian itu memuat keterangan bahwa Pelanggan
sudah berlangganan layanan ini sehingga berhak memberi nilai, pilihan Rating satu
sampai lima bintang, kolom Komentar yang bersifat opsional, dan tombol "Kirim
Ulasan". Di bawahnya, bagian "Ulasan pengguna" menampilkan ulasan yang sudah masuk
beserta nama penulis, jumlah bintang, dan isi komentarnya. Formulir ini hanya muncul
untuk Pelanggan yang memiliki transaksi berhasil atas layanan tersebut dan belum
pernah mengulasnya; apabila Pelanggan sudah pernah mengulas, halaman menampilkan
keterangan bahwa ulasan sudah dikirim. Setelah ulasan tersimpan, nilai rata-rata
pada lencana rating ikut berubah.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-5-perbandingan-layanan.png]]

**Gambar 4-5. Antarmuka untuk fitur Perbandingan Layanan (F-06).**

Gambar 4-5 menunjukkan halaman "Bandingkan Layanan". Halaman ini menyajikan tabel
perbandingan mendatar untuk dua sampai empat layanan sekaligus. Setiap layanan
menempati satu kolom yang diawali lambang merek, nama layanan, nama penyedia, dan
tombol silang untuk mengeluarkan layanan dari perbandingan. Baris tabel memuat harga
termurah, jumlah paket, perangkat maksimum, durasi terpanjang, penyedia, kategori,
rating pengguna, serta daftar fitur yang dimiliki layanan dengan tanda centang.
Nilai yang paling menguntungkan pada setiap baris ditandai dengan latar hijau tua,
sehingga pengunjung dapat menilai pilihan terbaik tanpa membaca seluruh angka.
Perbandingan hanya mempertimbangkan paket yang berstatus aktif. Di bawah tabel
terdapat bagian "Tambahkan layanan lain" berisi tombol seluruh layanan yang belum
dibandingkan, serta dua tombol untuk mengosongkan daftar dan kembali ke katalog.
Halaman ini bersifat publik seperti halaman katalog lainnya.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-6-cara-berlangganan.png]]

**Gambar 4-6. Antarmuka untuk fitur Panduan Berlangganan.**

Gambar 4-6 menunjukkan halaman panduan "Cara Berlangganan". Bagian atas halaman
memuat lima kartu langkah berlangganan dengan bentuk setengah lingkaran, yaitu Pesan
Layanan, Pembayaran, Menunggu Proses, Pesanan Diterima, dan Selesai. Setiap kartu
memuat nomor langkah, judul langkah, dan ilustrasi pendukung. Bagian kedua bertajuk
"Metode Pembayaran" menampilkan sebelas kanal pembayaran yang didukung, meliputi
PermataBank, BSI, BCA, BNI, Mandiri, OVO, DANA, ShopeePay, Alfamart, Bank BRI, dan
LinkAja, yang disusun lima kanal per baris pada kartu putih, disertai keterangan
bahwa transaksi dapat memakai virtual account, dompet digital, QRIS, maupun gerai
retail. Halaman ditutup dengan bagian "Rincian tiap langkah" yang menguraikan kelima
langkah secara terperinci agar pengunjung memahami alur berlangganan sebelum
melakukan pemesanan.


---

## 4.2 Transaction

Berikut ini adalah rancangan antarmuka pada modul Transaction. Modul ini hanya dapat diakses oleh Pelanggan yang telah masuk, sehingga identitas pemesan selalu dikenali oleh sistem pada setiap halaman. Halaman-halaman pada modul ini memakai satu kerangka tata letak akun yang sama, yaitu bilah samping berisi menu Pesanan, Kode Login, Langganan, Transaksi, dan Pengaturan, ditambah menu Laporan Kendala, Keluar, dan lonceng notifikasi pada bagian atas.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-7-konfirmasi-pemesanan.png]]

**Gambar 4-7. Antarmuka untuk fitur Pembelian Layanan Premium (F-08).**

Gambar 4-7 menunjukkan halaman konfirmasi pemesanan. Halaman ini memuat empat
bagian utama. Bagian pertama adalah pemberitahuan apabila pelanggan sudah memiliki
langganan aktif pada layanan yang sama; sistem menjelaskan bahwa pembelian ini akan
menyambung masa aktif mulai tanggal berakhir sebelumnya sehingga sisa masa aktif
tidak hilang. Bagian kedua adalah pemilihan paket, yang menampilkan seluruh paket
aktif layanan tersebut beserta harga, durasi, dan jumlah perangkat. Bagian ketiga
adalah ringkasan paket terpilih yang memuat lambang merek, nama layanan, penyedia,
kategori, dan daftar fitur paket. Bagian keempat adalah pemilihan kanal pembayaran
dalam bentuk daftar pilihan yang memuat QRIS, dompet digital (Dana, GoPay, OVO,
ShopeePay), transfer bank atau virtual account, dan gerai retail. Pada bagian bawah
terdapat ringkasan biaya serta tombol "Buat Pesanan", disertai keterangan bahwa
pesanan berlaku 24 jam sebelum kedaluwarsa dan kode login dikirim setelah pembayaran
diterima.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-8-voucher-pemesanan.png]]

**Gambar 4-8. Antarmuka untuk penerapan kode voucher pada pemesanan (F-08).**

Gambar 4-8 menunjukkan keadaan yang sama dengan Gambar 4-7, tetapi setelah pelanggan
memasukkan kode voucher dan menekan tombol "Pakai Voucher". Bagian "Kode Voucher"
menampilkan kode yang berhasil dipakai beserta keterangan potongan yang diperoleh,
tombol "Hapus" untuk membatalkan pemakaian, dan pesan keberhasilan. Di bawahnya
terdapat daftar "Voucher yang berlaku" beserta besar potongan dan tombol "Isi" untuk
mengisi kolom kode secara otomatis. Bagian Ringkasan Biaya memuat baris potongan
voucher sehingga total pembayaran berkurang, pada contoh ini dari Rp95.000 menjadi
Rp76.000. Apabila kode ditolak, sistem menampilkan pesan kesalahan berwarna merah
tepat di bawah kolom kode, misalnya ketika voucher sudah pernah dipakai pelanggan
yang sama atau kuotanya habis.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-9-detail-pesanan.png]]

**Gambar 4-9. Antarmuka untuk fitur Proses Pembayaran (F-09) dan Menampilkan Status Transaksi (F-10).**

Gambar 4-9 menunjukkan halaman detail pesanan untuk pesanan yang masih menunggu
pembayaran. Bagian atas memuat kode pesanan, lencana status, dan tombol kembali.
Panel "Selesaikan Pembayaran" memuat nominal yang harus dibayar, batas waktu
pembayaran, dan penanda sisa waktu dalam jam dan menit yang berubah menjadi warna
peringatan bila waktunya tinggal sedikit. Di bawahnya terdapat pemilih Metode
Pembayaran yang memuat QRIS, empat dompet digital, transfer bank atau virtual
account, dan gerai retail. Pada versi simulasi ini, halaman juga menyediakan kotak
"MODE SIMULASI" dengan tombol "Simulasikan Berhasil" dan "Simulasikan Gagal" sebagai
sarana demonstrasi tanpa penyedia pembayaran nyata. Bagian bawah memuat Rincian
Pesanan berupa nama layanan, varian paket, penyedia, durasi, jumlah perangkat,
metode pembayaran, waktu pesanan dibuat, dan total. Warna lencana status mengikuti
jenis status, yaitu hijau untuk pesanan selesai, biru untuk diproses, kuning untuk
menunggu, dan merah untuk dibatalkan. Apabila pesanan sudah lunas, halaman
menampilkan tombol "Cetak Struk" dan langganan aktif yang dihasilkan pesanan itu.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-10-struk-digital.png]]

**Gambar 4-10. Antarmuka untuk struk digital pesanan (F-10, F-11).**

Gambar 4-10 menunjukkan halaman struk digital yang dibuka dari halaman detail
pesanan. Bagian atas, yang tidak ikut tercetak, memuat tombol kembali dan tombol
"Cetak Struk" beserta keterangan bahwa pengguna dapat memilih "Simpan sebagai PDF"
pada dialog cetak. Badan struk memuat lambang dan identitas resmi PRIM, nomor struk,
tanggal penerbitan, status pembayaran beserta waktu pembayaran, data pelanggan yang
ditagih, kode pesanan, metode pembayaran, dan nomor referensi. Bagian rincian memuat
deskripsi layanan yang dibeli beserta penyedia, subtotal, biaya layanan, potongan
voucher, dan total yang dibayar. Struk juga menampilkan masa aktif langganan yang
dihasilkan, catatan transaksi, dan pernyataan bahwa struk diterbitkan secara
elektronik sehingga sah tanpa tanda tangan basah.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-11-pusat-pesanan.png]]

**Gambar 4-11. Antarmuka untuk fitur Menampilkan Status Transaksi (F-10).**

Gambar 4-11 menunjukkan halaman Pusat Pesanan, yaitu daftar seluruh pesanan milik
pelanggan yang sedang masuk. Bagian atas memuat kolom pencarian pesanan atau layanan
serta deretan tab penyaring menurut status, yaitu Semua, Aktif, Menunggu Pembayaran,
Diproses, Pembaharuan Bukti, Revisi Bukti, dan Masa Tenggang, masing-masing dengan
jumlah pesanannya. Setiap pesanan ditampilkan dalam kartu tersendiri yang memuat
lambang merek, nama layanan, nama varian paket, kode pesanan, lencana status, nominal,
dan waktu pesanan dibuat. Kartu juga memuat tiga keterangan tambahan, yaitu metode
pembayaran, masa aktif, dan jumlah perangkat. Pada kaki kartu tersedia tombol yang
menyesuaikan status pesanan, seperti "Bayar Sekarang" beserta hitung mundur batas
pembayaran, "Lihat Rincian", "Batalkan", "Cetak Struk", dan penanda ketersediaan
kode login.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-12-riwayat-transaksi.png]]

**Gambar 4-12. Antarmuka untuk fitur Riwayat Transaksi (F-11).**

Gambar 4-12 menunjukkan halaman Riwayat Transaksi, yaitu daftar kronologis seluruh
pesanan layanan premium yang pernah dibuat pelanggan. Bagian atas memuat judul
halaman dan satu pilihan penyaring status yang memuat seluruh status pesanan.
Setiap transaksi ditampilkan dalam satu baris kartu yang memuat nama layanan, lencana
status, nama varian paket, durasi, kode pesanan, nominal, dan waktu transaksi dibuat.
Pada kaki kartu tersedia tombol "Lihat Detail" menuju rincian pesanan; pesanan yang
belum dibayar juga dilengkapi tombol "Bayar Sekarang", sedangkan transaksi yang sudah
menghasilkan langganan menampilkan keterangan masa aktif langganan tersebut. Halaman
ini memakai penyajian yang lebih ringkas daripada Pusat Pesanan agar riwayat panjang
mudah ditelusuri.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-13-notifikasi-pembeli.png]]

**Gambar 4-13. Antarmuka untuk notifikasi pembeli (F-10).**

Gambar 4-13 menunjukkan daftar jatuh notifikasi yang terbuka dari lonceng pada
bilah atas halaman akun. Setiap butir notifikasi memuat lambang jenis pemberitahuan,
judul, keterangan singkat, dan waktu relatif, misalnya "1 menit yang lalu".
Notifikasi yang belum dibaca ditandai dengan judul tebal dan titik ungu, sedangkan
notifikasi yang sudah dibaca tampil lebih redup. Kepala daftar memuat jumlah
notifikasi yang belum dibaca beserta tombol "Tandai semua". Menekan sebuah butir
menandainya sebagai dibaca sekaligus mengarahkan pelanggan ke halaman yang
bersangkutan, misalnya halaman Kode Login atau rincian pesanan. Bila belum ada
notifikasi, daftar menampilkan keadaan kosong beserta keterangannya. Lencana angka
pada lonceng menunjukkan jumlah notifikasi yang belum dibaca dan bertambah pada
setiap perubahan status pesanan.


---

## 4.3 Subscription

Berikut ini adalah rancangan antarmuka pada modul Subscription. Modul ini dipakai Pelanggan untuk memantau masa aktif dan memperpanjang langganannya.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-14-langganan-saya.png]]

**Gambar 4-14. Antarmuka untuk fitur Riwayat Langganan (F-12).**

Gambar 4-14 menunjukkan halaman "Langganan Saya". Bagian atas memuat ringkasan
jumlah langganan yang dipisahkan menurut kondisinya, yaitu langganan aktif, langganan
yang akan berakhir dalam tujuh hari ke depan, dan total langganan, disertai tombol
"Tambah Langganan" menuju katalog. Di bawahnya tersedia empat tab penyaring, yaitu
Sedang Aktif, Akan Berakhir, Sudah Berakhir, dan Semua. Setiap langganan ditampilkan
dalam kartu tersendiri yang memuat lambang merek, nama layanan, nama varian paket,
penyedia, lencana "Aktif", dan jumlah hari yang tersisa. Masa aktif digambarkan
dengan bilah kemajuan beserta persentase masa aktif yang sudah terpakai, sehingga
pelanggan dapat menilai sisa waktunya secara cepat; langganan yang tinggal sedikit
hari diberi penanda warna peringatan. Setiap kartu dilengkapi tombol "Perpanjang",
tombol "Lihat Layanan", dan sebuah tombol untuk menyalakan atau mematikan
perpanjangan otomatis, serta kode pesanan asal langganan tersebut.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-15-perpanjang-langganan.png]]

**Gambar 4-15. Antarmuka untuk fitur Perpanjangan Langganan (F-12).**

Gambar 4-15 menunjukkan halaman perpanjangan langganan. Bagian atas memuat jejak
navigasi dan kartu keterangan yang menjelaskan nama layanan yang diperpanjang, paket
yang sedang dipakai, tanggal berakhir, dan jumlah hari yang tersisa. Kartu itu juga
menegaskan bahwa masa aktif baru akan disambung mulai tanggal berakhir sebelumnya,
sehingga sisa masa aktif tidak hilang. Di bawahnya terdapat bagian "Pilih paket
perpanjangan" yang menampilkan seluruh paket aktif layanan tersebut sebagai kartu
pilihan. Kartu yang sedang dipakai pelanggan diberi lencana "Paket Anda saat ini"
dan garis tepi berwarna, sedangkan setiap kartu memuat nama paket, harga, durasi,
jumlah perangkat, keterangan singkat, dan tombol "Pilih Paket Ini" yang mengarah ke
halaman konfirmasi pemesanan.


---

## 4.4 Admin

Berikut ini adalah rancangan antarmuka pada modul Admin. Modul ini melayani **dua peran** yang berbagi satu kerangka tata letak, yaitu **Administrator** pada bagian 4.4.1 dan **Penyedia Layanan** pada bagian 4.4.2. Keduanya memakai kerangka yang sama, yaitu bilah samping berwarna ungu di sebelah kiri dan area konten berwarna putih di sebelah kanan, tetapi isi menunya berbeda menurut peran agar tidak ada menu yang berujung pada penolakan akses.

### 4.4.1 Panel Pengelola

Seluruh halaman pada bagian ini memakai satu kerangka tata letak, yaitu bilah samping berwarna ungu di sebelah kiri dan area konten berwarna putih di sebelah kanan. Bilah samping memuat menu Dashboard, Pesanan, Langganan, Pengguna, Produk, Pembayaran, Voucher, dan Laporan, serta menu Pengaturan dan Keluar pada bagian bawah. Kepala area konten memuat tanggal hari ini, lonceng notifikasi beserta penghitung pesanan yang perlu ditangani, dan identitas pengelola yang sedang masuk. Kerangka tetap ini dipakai agar pengelola selalu menemukan menu pada posisi yang sama di seluruh halaman.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-16-dashboard.png]]

**Gambar 4-16. Antarmuka untuk fitur Dashboard Pengelola (F-16).**

Gambar 4-16 menunjukkan halaman dashboard pengelola. Bagian atas memuat kartu
metrik yang menampilkan jumlah layanan terdaftar, paket langganan, penyedia layanan,
pengguna terdaftar, langganan aktif, dan transaksi menunggu. Setiap kartu memuat
nilai utama beserta keterangan pendukungnya. Bagian tengah memuat nilai pendapatan
bulan berjalan beserta pertumbuhannya dibanding bulan lalu dan diagram batang jumlah
pesanan selama empat belas hari terakhir. Bagian bawah memuat komposisi status
pesanan dalam bentuk bilah persentase, daftar produk terlaris beserta jumlah
transaksinya, dan tabel pesanan terbaru yang memuat kode pesanan, nama pelanggan,
produk, nominal, dan status, disertai tautan menuju daftar pesanan lengkap.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-17-manajemen-pesanan.png]]

**Gambar 4-17. Antarmuka untuk fitur Pengelolaan dan Pemantauan Transaksi (F-16).**

Gambar 4-17 menunjukkan halaman manajemen pesanan. Bagian atas memuat empat kartu
ringkasan, yaitu Total Pesanan, Menunggu, Selesai Hari Ini beserta selisihnya
terhadap hari kemarin, dan Dibatalkan. Di bawahnya terdapat tab penyaring status,
yaitu Semua, Selesai, Diproses, Menunggu, dan Dibatalkan, serta kolom pencarian yang
dapat dipakai untuk mencari kode pesanan, nama pelanggan, atau surel. Tabel pesanan
memuat kolom ID Pesanan, Tanggal, Pelanggan, Produk, Metode Pembayaran, Total,
Status, dan Aksi. Kolom Status memuat lencana status dan, pada pesanan yang sudah
dikembalikan dananya, keterangan nominal dana yang dikembalikan. Pada kolom Aksi
tersedia tombol yang menyesuaikan kondisi pesanan: untuk pesanan yang belum lunas
tersedia tombol penandaan berhasil atau gagal secara manual, sedangkan untuk pesanan
yang sudah lunas tersedia tombol "Bagikan kode login" untuk mengirimkan kredensial
akun layanan kepada pembeli dan tombol "Kembalikan dana". Penandaan berhasil akan
otomatis membentuk langganan bagi pelanggan terkait.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-18-manajemen-langganan.png]]

**Gambar 4-18. Antarmuka untuk fitur Memonitor Seluruh Langganan.**

Gambar 4-18 menunjukkan halaman manajemen langganan. Bagian atas memuat empat kartu
ringkasan, yaitu jumlah Langganan Aktif yang masa aktifnya masih berjalan, jumlah
langganan yang Akan Berakhir dalam tujuh hari ke depan, jumlah langganan yang Sudah
Berakhir termasuk yang dibatalkan, dan jumlah langganan dengan Perpanjangan Otomatis
yang menyala. Di bawahnya terdapat kolom pencarian yang dapat dipakai untuk mencari
nama atau surel pelanggan serta nama layanan, dan satu penyaring kondisi yang memuat
pilihan Semua kondisi, Sedang aktif, Akan berakhir, dan Sudah berakhir. Tabel
langganan memuat kolom Pelanggan beserta surelnya, Layanan beserta penyedianya,
Paket, tanggal Mulai, tanggal Berakhir, Sisa hari yang ditandai warna peringatan bila
tinggal tujuh hari atau kurang, status Perpanjangan otomatis, dan Status langganan.
Halaman ini melengkapi dashboard, yang hanya menampilkan jumlah langganan aktif,
dengan kemampuan menelusuri langganan satu per satu dan menemukan langganan yang
perlu ditindaklanjuti sebelum berakhir.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-19-manajemen-produk.png]]

**Gambar 4-19. Antarmuka untuk fitur Pengelolaan Data Layanan (F-13).**

Gambar 4-19 menunjukkan halaman "Kelola Layanan" yang menjadi tempat pengelolaan
seluruh layanan premium pada katalog. Bagian atas memuat judul halaman beserta
keterangannya dan tombol "Tambah Layanan". Di bawahnya, seluruh layanan ditampilkan
dalam satu tabel yang memuat kolom Layanan berupa nama layanan beserta slug-nya,
Kategori, Penyedia, jumlah Paket aktif, Status berupa lencana "Aktif" atau
"Nonaktif", dan Aksi. Melalui kolom Aksi, pengelola dapat membuka halaman
pengelolaan paket layanan tersebut, menyunting layanan, atau menghapusnya. Formulir
layanan yang muncul ketika menambah atau menyunting memuat pilihan penyedia dan
kategori, nama layanan, tagline, deskripsi, situs web, saklar "Tampilkan di katalog
publik", dan unggahan logo. Sistem menolak penghapusan layanan yang sudah pernah
ditransaksikan dan menyarankan menonaktifkannya saja.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-20-manajemen-paket.png]]

**Gambar 4-20. Antarmuka untuk fitur Pengelolaan Data Paket (F-14).**

Gambar 4-20 menunjukkan halaman pengelolaan paket untuk satu layanan tertentu.
Bagian atas memuat jejak navigasi kembali ke daftar layanan, nama layanan yang
sedang dikelola beserta nama penyedianya, dan tombol "Tambah Paket". Setiap paket
ditampilkan sebagai kartu yang memuat nama paket, harga, lencana status aktif,
keterangan berupa durasi, jumlah perangkat, jumlah transaksi yang memakai paket itu,
dan jumlah langganan yang dihasilkan. Kartu juga menampilkan daftar fitur paket dan
tombol untuk menyunting atau menghapus paket. Formulir paket memuat nama, harga,
durasi, jumlah perangkat, deskripsi, daftar fitur, status aktif, persentase diskon,
penanda preorder, dan jumlah stok.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-21-manajemen-pengguna.png]]

**Gambar 4-21. Antarmuka untuk fitur Pengelolaan Data Pengguna (F-15, F-18).**

Gambar 4-21 menunjukkan halaman manajemen pengguna. Bagian atas memuat empat
kartu ringkasan, yaitu Total Pengguna, jumlah akun Aktif, jumlah akun Non-aktif, dan
jumlah akun yang Disuspend, masing-masing disertai persentasenya terhadap total. Di
bawahnya terdapat kolom pencarian yang dapat dipakai untuk mencari nama, surel, atau
nomor telepon, serta dua penyaring untuk peran dan status akun. Tabel pengguna
memuat kolom Pengguna berupa inisial, nama, dan surel, lalu No. Telepon, Role,
Status, Tgl. Daftar, jumlah Pesanan, dan Terakhir Login. Kolom Role dan Status
berupa pilihan yang dapat diubah langsung dari tabel, sehingga pengelola dapat
mengubah peran pengguna atau menonaktifkan akun tanpa berpindah halaman. Sistem
menolak perubahan peran dan status terhadap akun pengelola yang sedang masuk agar
akses ke panel tidak hilang. Halaman ditutup dengan keterangan peran tiap jenis akun
dan akibatnya terhadap hak akses.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-22-manajemen-voucher.png]]

**Gambar 4-22. Antarmuka untuk fitur Pengelolaan Kode Voucher (F-08).**

Gambar 4-22 menunjukkan halaman manajemen voucher. Bagian atas memuat empat kartu
ringkasan, yaitu Total Voucher, Voucher Aktif, Total Pemakaian, dan Total Potongan
yang Diberikan. Di bawahnya terdapat kolom pencarian serta tab penyaring Semua,
Aktif, Nonaktif, dan Kuota Habis. Tabel voucher memuat kolom Kode beserta
keterangannya, Potongan, Syarat, Kuota, Masa Berlaku, Status, dan Aksi. Kolom
Potongan menampilkan besar potongan dalam persen atau rupiah beserta batas
maksimalnya, sedangkan kolom Kuota menampilkan perbandingan jumlah pemakaian
terhadap batas kuota dan batas pemakaian per pengguna. Melalui kolom Aksi, pengelola
dapat mengubah voucher, mengaktifkan atau menonaktifkannya, dan menghapusnya.
Formulir voucher memuat kode, keterangan, jenis potongan, besar potongan, batas
maksimal, minimum belanja, kuota, batas pemakaian per pengguna, dan masa berlaku.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-23-manajemen-pembayaran.png]]

**Gambar 4-23. Antarmuka untuk fitur Pemantauan Pembayaran (F-09, F-16).**

Gambar 4-23 menunjukkan halaman manajemen pembayaran. Bagian atas memuat empat
kartu ringkasan, yaitu Total Pendapatan dari transaksi berhasil, jumlah Transaksi
Berhasil termasuk pesanan yang sudah diterima, jumlah transaksi yang Menunggu
Pembayaran, dan nilai pesanan yang Dibatalkan. Di bawahnya terdapat dua penyaring,
yaitu pilihan metode pembayaran dan pilihan status. Tabel pembayaran memuat kolom
ID Transaksi, Pelanggan, Metode, Tanggal & Waktu, Jumlah, dan Status. Halaman ini
dipakai pengelola untuk memeriksa kanal pembayaran yang paling sering dipakai,
mencocokkan nominal yang masuk, dan menemukan transaksi yang belum terselesaikan.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-24-laporan.png]]

**Gambar 4-24. Antarmuka untuk fitur Laporan dan Analitik (F-16).**

Gambar 4-24 menunjukkan halaman laporan admin. Bagian atas memuat empat kartu
ringkasan, yaitu Total Pesanan, Pendapatan Bulan Ini, Pengguna Baru, dan Pembatalan
Bulan Ini, masing-masing dengan pembanding bulan sebelumnya. Bagian "Insight Bulan
Ini" menampilkan tiga nilai pertumbuhan, yaitu pertumbuhan pendapatan, pertumbuhan
pengguna baru, dan jumlah pembatalan bulan berjalan. Bagian berikutnya memuat
diagram lingkaran komposisi status pesanan beserta keterangannya dan kartu statistik
bulan ini yang memuat rata-rata nilai pesanan, tingkat keberhasilan, total
pembatalan, dan hari tersibuk. Di bawahnya terdapat diagram batang pendapatan enam
bulan terakhir, rincian status pesanan dalam bentuk bilah persentase, daftar produk
terlaris, dan tabel pesanan terbaru. Seluruh laporan dihitung dari data transaksi
yang benar-benar tersimpan, bukan angka contoh.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-25-pengaturan-profil.png]]

**Gambar 4-25. Antarmuka untuk fitur Pengaturan Profil Pengelola (F-02, F-18).**

Gambar 4-25 menunjukkan halaman pengaturan pengelola pada tab Profile Admin. Bagian
atas memuat judul halaman, tombol kembali ke dashboard, dan dua tab pengaturan, yaitu
Profile Admin dan Notifikasi. Sisi kiri memuat kartu identitas berisi inisial, nama,
surel, lencana peran, nomor telepon, status akun, waktu login terakhir, dan tanggal
akun dibuat, disertai peringatan bahwa perubahan pada halaman ini memengaruhi akun
yang sedang dipakai untuk masuk. Sisi kanan memuat formulir Informasi Pribadi yang
memuat nama lengkap, surel, nomor telepon, alamat, dan peran. Kolom peran ditampilkan
tetapi tidak dapat diubah dari halaman ini agar akses ke panel tidak hilang. Di
bagian bawah tersedia tombol "Simpan Perubahan" dan "Batalkan".


[[SISIPKAN GAMBAR: docs/screens/gambar-4-26-pengaturan-notifikasi.png]]

**Gambar 4-26. Antarmuka untuk fitur Pengaturan Notifikasi Pengelola (F-18).**

Gambar 4-26 menunjukkan halaman pengaturan pengelola pada tab Notifikasi. Halaman
dibagi menjadi dua kelompok, yaitu Notifikasi Email dan Notifikasi Sistem. Kelompok
Notifikasi Email memuat pilihan Laporan Harian, Pesanan Baru, Pembaruan Sistem, dan
Perubahan Kata Sandi, sedangkan kelompok Notifikasi Sistem memuat pilihan Pop-up
Pesanan, Login Perangkat Baru, Pesan Pengguna, dan Peringatan Stok Rendah. Setiap
pilihan memuat judul, keterangan akibat pilihan tersebut, dan sebuah saklar untuk
menyalakan atau mematikannya. Di bagian bawah tersedia tombol "Simpan Perubahan"
dan tautan "Kembalikan" untuk memuat ulang nilai tersimpan.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-27-notifikasi-pengelola.png]]

**Gambar 4-27. Antarmuka untuk notifikasi pengelola (F-16).**

Gambar 4-27 menunjukkan daftar jatuh notifikasi pengelola yang terbuka dari lonceng
pada kepala area konten. Daftar ini memuat pemberitahuan operasional, misalnya
pesanan baru yang perlu ditangani, pembayaran yang berhasil, dan pengajuan
pengembalian dana. Setiap butir memuat lambang jenis pemberitahuan, judul,
keterangan singkat, dan waktu relatif. Notifikasi yang belum dibaca ditandai dengan
judul tebal dan titik penanda, sedangkan kepala daftar memuat jumlah notifikasi yang
belum dibaca beserta tombol untuk menandai semuanya sudah dibaca. Lencana angka pada
lonceng di kepala area konten menunjukkan jumlah pesanan yang perlu ditangani,
sehingga pengelola dapat mengetahui adanya pekerjaan baru dari halaman mana pun
tanpa membuka menu Pesanan.


### 4.4.2 Panel Penyedia

Bagian ini memaparkan antarmuka untuk peran **Penyedia Layanan**, yaitu pihak yang memasok layanan premium yang ditampilkan pada katalog. Panel penyedia memakai kerangka tata letak yang sama dengan panel pengelola, tetapi cakupannya sengaja dibatasi: penyedia hanya dapat melihat dan menyunting layanan serta paket miliknya sendiri, sesuai kebutuhan fungsional F-17. Batas kepemilikan ditentukan oleh tautan antara akun penyedia dan satu baris data penyedia pada basis data, bukan oleh masukan formulir, sehingga penyedia tidak dapat memindahkan layanan ke penyedia lain atau membuka layanan pihak lain walaupun alamat halamannya diubah. Karena cakupannya berbeda, bilah samping panel penyedia hanya memuat menu Dashboard dan Layanan Saya, dan lonceng notifikasi panel pengelola tidak ditampilkan agar pemberitahuan lintas penyedia tidak terbaca oleh penyedia.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-28-dashboard-penyedia.png]]

**Gambar 4-28. Antarmuka untuk fitur Dashboard Penyedia (F-17).**

Gambar 4-28 menunjukkan halaman dashboard panel penyedia. Bilah samping memuat menu
Dashboard dan Layanan Saya, sedangkan kepala area konten memuat tanggal hari ini dan
identitas penyedia yang sedang masuk beserta label perannya, yaitu "Penyedia
Layanan". Bagian atas memuat empat kartu metrik, yaitu jumlah Layanan Saya beserta
nama penyedia yang diwakili, jumlah Paket Langganan pada layanan tersebut, jumlah
Pesanan Layanan, dan nilai Pendapatan dari pesanan yang berhasil. Bagian tengah
memuat kartu "Layanan Anda" yang menampilkan setiap layanan milik penyedia beserta
lencana status, jumlah paket, jumlah ulasan, dan tombol "Kelola Paket" yang menuju
halaman paket layanan tersebut. Bagian bawah memuat tabel "Pesanan Terbaru atas
Layanan Anda" yang memuat ID Pesanan, Tanggal, Pelanggan, Produk, Total, dan Status,
disertai keterangan bahwa pesanan hanya untuk dipantau karena verifikasi pembayaran
tetap dilakukan administrator. Seluruh angka pada halaman ini dihitung hanya dari
layanan milik penyedia yang sedang masuk.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-29-layanan-penyedia.png]]

**Gambar 4-29. Antarmuka untuk fitur Pengelolaan Layanan oleh Penyedia (F-17).**

Gambar 4-29 menunjukkan halaman "Layanan Saya" pada panel penyedia. Bagian atas
memuat judul halaman beserta keterangan bahwa daftar ini hanya berisi layanan milik
penyedia tersebut, dan tombol "Tambah Layanan". Di bawahnya terdapat pita keterangan
bahwa penyedia pada setiap layanan dikunci ke akun yang sedang masuk, sehingga
penyedia hanya dapat menyunting layanan milik sendiri. Tabel layanan memuat kolom
Layanan berupa nama layanan beserta slug-nya, Kategori, jumlah Paket, Status berupa
lencana "Aktif" atau "Nonaktif", dan Aksi. Melalui kolom Aksi, penyedia dapat membuka
halaman pengelolaan paket layanan tersebut, menyunting layanan, atau menghapusnya.
Formulir layanan pada panel ini sama dengan formulir pada panel pengelola, dengan
satu perbedaan penting: kolom penyedia ditampilkan terkunci berisi nama penyedia
milik akun tersebut dan tidak dapat diubah. Sistem menolak penghapusan layanan yang
sudah pernah ditransaksikan dan menyarankan menonaktifkannya saja.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-30-paket-penyedia.png]]

**Gambar 4-30. Antarmuka untuk fitur Pengelolaan Paket oleh Penyedia (F-17).**

Gambar 4-30 menunjukkan halaman pengelolaan paket pada panel penyedia untuk satu
layanan miliknya. Bagian atas memuat jejak navigasi kembali ke daftar Layanan Saya,
nama layanan yang sedang dikelola beserta kategorinya, dan tombol "Tambah Paket".
Pita keterangan menegaskan bahwa paket pada halaman ini hanya berlaku untuk layanan
tersebut. Setiap paket ditampilkan sebagai kartu yang memuat nama paket, harga,
lencana status aktif, keterangan berupa durasi, jumlah perangkat, jumlah transaksi
yang memakai paket, dan jumlah langganan yang dihasilkan, serta daftar fiturnya.
Melalui tombol pada setiap kartu, penyedia dapat menyunting atau menghapus paket.
Formulir paket memuat nama, harga, durasi, jumlah perangkat, deskripsi, daftar
fitur, dan saklar status aktif. Sistem menolak penghapusan paket yang sudah pernah
ditransaksikan, dan menolak penghapusan paket terakhir pada sebuah layanan karena
layanan wajib memiliki minimal satu paket.

---

# CATATAN TAMBAHAN (jangan ikut di-paste)

## 1. Temuan lain pada dokumen SRS Anda

Saat membaca berkas `.docx`, saya menemukan hal berikut. Semuanya di luar Bab 4,
jadi tidak saya ubah, hanya saya laporkan.

**a. Judul sub-bab Bab 3 masih memakai placeholder.** Baris `### Use Case Diagram :
<Modul Catalog>` dan tiga baris sejenisnya masih bertanda kurung siku, begitu pula
keterangan gambar seperti `Gambar 3-1. Use Case Diagram <Modul Catalog>`. Sebaiknya
tanda kurungnya dihapus menjadi `### Use Case Diagram Modul Catalog`.

**b. "Tabel 3-3" dipakai dua kali.** Sekali untuk *Memfilter layanan* pada Bab 3.1
dan sekali lagi untuk *Kamus Data* pada bagian Entity Relationship Diagram. Saya
sudah memastikan nomor **Tabel 3-33 sampai 3-36 belum dipakai**, sehingga Kamus Data
sebaiknya memakai nomor **Tabel 3-33**.

**c. Penomoran kebutuhan fungsional dokumen sudah pasti F-01 sampai F-18.** Pada
revisi sebelumnya saya menulis bahwa nomor itu perlu diselaraskan dengan
`prim/docs/SRS-SINKRONISASI.md`, yang memakai F-01 sampai F-23. Setelah memeriksa
kembali isi dokumen, seluruh keterangan gambar pada berkas ini saya rujuk ke
**penomoran dokumen Anda (F-01–F-18)** agar aman dipakai di depan penguji. Perlu
diketahui bahwa penomoran repositori berbeda, sehingga daftar kebutuhan fungsional
pada dokumen dan pada repositori sebaiknya tetap diselaraskan.

**d. Beberapa fitur belum punya nomor kebutuhan fungsional.** Kode voucher, struk
digital, notifikasi pembeli, dan halaman laporan tidak tercantum sebagai butir
tersendiri pada daftar F-01–F-18. Untuk sementara ketiga gambar terkait dirujuk ke
kebutuhan yang paling dekat, yaitu F-08 untuk voucher dan F-10 atau F-16 untuk
notifikasi dan laporan. Bila dosen menuntut ketelitian, tambahkan butir kebutuhan
baru, misalnya "Pengelolaan kode promo" dan "Notifikasi perubahan status pesanan",
lalu perbarui rujukan pada keterangan gambar di atas.

**e. Bab 4 hanya membahas antarmuka.** Arsitektur sistem, rancangan basis data
(kamus data sudah ada di Bab 3), dan rancangan teknologi tidak dibahas di dalamnya.
Bila dosen menuntut bagian tersebut, tambahkan sebagai sub-bab tersendiri atau
sebagai lampiran.

**f. Bab 2.1 Perspektif Sistem menyebut aktor ketiga secara tidak konsisten.** Satu paragraf pada Bab 2
berbunyi:

> Sistem PRIM berpusat sebagai platform digital yang menerima dan memproses data dari
> tiga aktor utama, yaitu User, Admin, dan Payment Gateway.

Padahal daftar aktor beberapa paragraf setelahnya menyebut **Provider/Penyedia
Layanan** sebagai pihak ketiga, dan F-17, F-18, serta NF-10 juga memakai tiga peran
(User, Admin, Provider). Payment Gateway sendiri bukan peran pengguna, melainkan
aktor pendukung berupa sistem lain di luar batas sistem. Sebaiknya kalimat itu
diganti menjadi:

> Sistem PRIM berpusat sebagai platform digital yang menerima dan memproses data dari
> tiga aktor utama, yaitu User/Pelanggan, Admin, dan Provider/Penyedia Layanan, serta
> satu aktor pendukung berupa Payment Gateway.

Dengan begitu kalimat tersebut sejalan dengan daftar aktor, kebutuhan fungsional, dan
diagram use case Modul 2 yang memang memuat aktor Gateway Pembayaran sebagai aktor
pendukung.

Perbaikan Bab 2 selengkapnya — Gambar 2-1 yang kini memuat Provider, paragraf Provider
pada 2.1, tiga butir Fungsi Produk, dua kalimat Batasan Sistem, NF-10 dan NF-17, serta
delapan kebutuhan fungsional baru F-19 sampai F-26 — ada pada berkas terpisah
`BAB-II-Deskripsi-Umum-Revisi.md` di akar proyek.

## 2. Hasil audit diagram use case terhadap aplikasi

Saya membandingkan keempat diagram use case beserta deskripsinya dengan rute dan
komponen yang benar-benar ada pada aplikasi. Hasilnya sebagai berikut.

| # | Modul | Temuan | Bukti pada aplikasi | Saran |
|---|---|---|---|---|
| 1 | Modul 4 Admin | Aktor **Penyedia Layanan** tidak muncul pada diagram mana pun, padahal dokumen mendefinisikan tiga peran | sebelum revisi: 0 dari 4 diagram dan 0 dari seluruh deskripsi use case memuat aktor provider | **Selesai**: Provider ditambahkan sebagai aktor, ditambah UC-11 sampai UC-14, dan panel penyedia dibangun (Gambar 4-28 s.d. 4-30) |
| 2 | Modul 1 Catalog | UC-07 "Menulis ulasan dan rating" belum ada implementasinya | sebelum revisi: `ServiceDetail` hanya menampilkan ulasan; tidak ada aksi simpan ulasan | **Selesai**: formulir ulasan dibangun dengan syarat transaksi berhasil dan satu ulasan per layanan per pelanggan (Gambar 4-4) |
| 3 | Modul 3 Subscription | UC-06 "Memonitor seluruh langganan" (Administrator) belum ada halamannya | sebelum revisi: daftar rute panel admin tidak memuat menu langganan | **Selesai**: halaman pemantauan langganan dibangun pada `/admin/langganan` beserta menu, penyaring, dan kartu ringkasan (Gambar 4-18) |
| 4 | Modul 4 Admin | Aplikasi memiliki fitur yang belum menjadi use case: kelola voucher, pantau pembayaran, laporan, pengaturan akun pengelola, bagikan kode login, dan kembalikan dana | rute `/admin/voucher`, `/admin/pembayaran`, `/admin/laporan`, `/admin/pengaturan/*`, serta aksi pada tabel pesanan (Gambar 4-17, 4-22, 4-23, 4-24, 4-25) | **Selesai**: enam use case ditambahkan, yaitu UC-15 sampai UC-20, beserta deskripsinya |
| 5 | Modul 2 Transaction | UC-08 "Memperbarui status transaksi" dan UC-09 "Menandai transaksi kedaluwarsa" oleh Penjadwal Sistem **sudah sesuai** | `App\Console\Commands\ExpireTransactions` terjadwal tiap jam pada `routes/console.php` | tidak perlu diubah |
| 6 | Bab 2.1 | Aktor ketiga disebut dua nama berbeda (Payment Gateway dan Provider) | lihat temuan 1.f di atas | **Selesai**: kalimat pada `SRS Aplikasi PRIM Kelompok 7.docx` sudah diperbaiki oleh `tools/perbaiki-aktor-bab1.py`, dengan cadangan di `docs/backup/` |

Seluruh temuan sudah ditutup. Temuan 1, 4, dan 6 diselesaikan dengan menambah atau
memperbaiki dokumen dan diagram, sedangkan temuan 2 dan 3 diselesaikan dengan
membangun fiturnya di aplikasi agar dokumen dan implementasi benar-benar sejalan.

Modul 4 pada diagram use case kini memuat 20 use case, yaitu 16 untuk Administrator
dan 4 untuk Penyedia Layanan. Karena dua use case baru disisipkan di tengah urutan,
nomor Gambar 4 bergeser lagi; tabel pemetaan di awal berkas ini sudah memuat
penomoran terbaru, dan **seluruh gambar Bab 4 perlu disisipkan ulang** ke dokumen
Google Docs.

## 3. Yang sudah benar dan tidak perlu diubah

- Penomoran **Tabel 3-1 sampai 3-32 lengkap** tanpa nomor yang melompat.
- Judul *Tabel 3-3. Kamus Data* sudah berada tepat di atas tabelnya, setelah
  keterangan Gambar 3-5 (ERD).
- Urutan gambar Bab 3 sudah berurutan: Gambar 3-1 (use case), 3-2 (aktivitas
  registrasi), 3-3 (aktivitas login), 3-4 (aktivitas pembelian dan pembayaran),
  3-5 (ERD).
- Bab 2 sudah memuat Tabel 2-1 (kebutuhan fungsional) dan Tabel 2-2 (kebutuhan
  non-fungsional) secara lengkap.

## 4. Halaman yang belum dibuat gambarnya

Beberapa halaman berikut sengaja belum dibuatkan gambar karena di luar cakupan
keempat modul Bab 4, yaitu halaman masuk, registrasi, lupa kata sandi, kode login,
pengaturan akun pelanggan (profil, kata sandi, tampilan), laporan kendala, serta
halaman master data pendukung pada panel pengelola (kategori dan penyedia). Semuanya
sudah berjalan pada aplikasi dan dapat ditambahkan kemudian dengan menambah satu
baris pada daftar halaman di dalam `tools/buat-gambar-bab4.ps1`, lalu menjalankan
ulang skrip itu.

Halaman **perbandingan layanan** yang pada revisi sebelumnya belum punya gambar kini
sudah terwakili sebagai Gambar 4-5. Berkas gambar cadangan
`tambahan-pusat-pesanan.png` tidak lagi diperlukan karena halaman Pusat Pesanan sudah
menjadi Gambar 4-11, sehingga berkas itu dihapus agar tidak ada gambar yang tidak
dirujuk di dalam folder.
