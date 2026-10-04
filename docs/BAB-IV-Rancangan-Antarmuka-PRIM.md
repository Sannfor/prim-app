# CATATAN SEBELUM SALIN-TEMPEL

Berkas ini berisi isi untuk **BAB 4 — RANCANGAN ANTARMUKA** pada dokumen
*SRS Aplikasi PRIM Kelompok 7.docx*.

## Cara saya menyusun ini

Saya membaca berkas `SRS Aplikasi PRIM Kelompok 7.docx` yang ada di komputer Anda
(2.377 KB) dengan mengekstrak isinya langsung dari XML Word — bukan menebak dari
Google Docs. Dari situ saya tahu persis susunan Bab 4 Anda:

| Keadaan di dokumen Anda | Isi |
|---|---|
| Judul bab | `# Rancangan Antarmuka` (bukan "Rancangan Sistem") |
| Kalimat pengantar | `<rancangan antarmuka digambarkan dan diterangkan di bagian ini>` |
| Sub-bab | `## <Nama Modul 1>`, `## <Nama Modul 2>`, `## <Nama Modul 3>` — **masih placeholder** |
| Isi tiap sub-bab | Gambar antarmuka + keterangan `<terangkan gambar diatas, seperti terdapat fungsi apa aja dan lain-lain.>` |
| Gambar | `Gambar 4-1.` sampai `Gambar 4-4.` — 4 gambar |
| Tabel | Belum ada tabel di Bab 4 |

**Dua perbaikan yang saya lakukan pada versi sebelumnya:**

1. **Judul dan cakupan.** Versi saya sebelumnya berjudul "Rancangan Sistem" dan
   memuat arsitektur + basis data + teknologi. Itu **tidak sesuai** dengan dokumen
   Anda, yang Bab 4-nya khusus **Rancangan Antarmuka**. Sekarang saya ikuti dokumen.
2. **Jumlah modul.** Dokumen Anda hanya menyediakan 3 sub-bab modul di Bab 4,
   padahal Bab 3 dokumen Anda sendiri sudah memuat **empat** Use Case Diagram, yaitu
   `<Modul Catalog>`, `<Modul Transaction>`, `<Modul Subscription>`, dan
   `<Modul Admin>`. Versi ini memakai **4 sub-bab** agar jumlahnya konsisten dengan
   Bab 3.

## Langkah menempel ke Google Docs

1. Buka dokumen, gulir ke **Bab 4 Rancangan Antarmuka**.
2. Hapus **seluruh** isi Bab 4 yang lama — termasuk baris `<rancangan antarmuka
   digambarkan dan diterangkan di bagian ini>`, tiga sub-bab `<Nama Modul 1/2/3>`,
   dan empat kalimat `<terangkan gambar diatas, ...>`.
3. Salin isi Bab 4 dari berkas ini (mulai dari baris `BAB 4` di bawah garis
   pemisah) dan tempel.
4. Beri gaya **Heading 1** pada "BAB 4 RANCANGAN ANTARMUKA" dan **Heading 2** pada
   keempat sub-bab modul.
5. Sisipkan gambar pada setiap penanda `[[SISIPKAN GAMBAR: ...]]`. Setelah gambar
   masuk, **hapus baris penanda itu** agar tidak ikut tercetak.

## Daftar gambar yang perlu Anda siapkan

| Gambar | Halaman yang ditangkap | Cara membukanya |
|---|---|---|
| 4-1 | Katalog Layanan | `http://127.0.0.1:8000/katalog` |
| 4-2 | Detail Layanan | `http://127.0.0.1:8000/katalog/netflix` |
| 4-3 | Perbandingan Layanan | `/bandingkan` (pilih 2–4 layanan lebih dahulu) |
| 4-4 | Cara Berlangganan | `http://127.0.0.1:8000/cara-berlangganan` |
| 4-5 | Konfirmasi Pemesanan | Masuk `andi@prim.test`, lalu buka `/checkout/{id paket}` |
| 4-6 | Detail Pesanan | `/transaksi/{kode pesanan}` |
| 4-7 | Langganan Saya | `http://127.0.0.1:8000/langganan` |
| 4-8 | Dashboard Pengelola | Masuk `admin@prim.com`, lalu buka `/admin` |
| 4-9 | Manajemen Pesanan | `http://127.0.0.1:8000/admin/pesanan` |
| 4-10 | Manajemen Produk | `http://127.0.0.1:8000/admin/produk` |
| 4-11 | Manajemen Pengguna | `http://127.0.0.1:8000/admin/pengguna` |

Seluruh akun demo memakai kata sandi `password`.

**Saran penangkapan layar:** pakai lebar jendela 1440 piksel agar tata letak
mendekati desain, dan tangkap **hanya area konten** (tanpa bilah alamat peramban)
supaya gambar rapi di dalam dokumen.

## Kalau dosen meminta lebih dari sekadar antarmuka

Bab 4 versi saya sebelumnya masih tersimpan di
`BAB-IV-Rancangan-Sistem-PRIM.md` — isinya arsitektur, kamus data 9 tabel, ERD,
dan matriks penelusuran kebutuhan. Itu dapat dipakai sebagai **lampiran** atau bila
dosen meminta Bab 4 yang lebih lengkap. Jangan ditempel bersamaan dengan berkas ini
karena cakupannya berbeda.

---

# BAB 4
# RANCANGAN ANTARMUKA

Rancangan antarmuka Aplikasi PRIM (Platform Aggregator Fitur Premium) disusun
mengikuti berkas desain kelompok pada Figma. Setiap halaman dirancang dengan satu
sistem warna dan satu skala tipografi yang sama agar tampilan konsisten pada seluruh
modul. Warna utama yang dipakai adalah ungu `#534AB7` dengan latar permukaan
`#D3CFFF`, sedangkan teks memakai DM Sans untuk judul dan Inter untuk isi.

Bab ini memaparkan rancangan antarmuka pada keempat modul sistem, yaitu **Catalog**
(4.1), **Transaction** (4.2), **Subscription** (4.3), dan **Admin** (4.4).

---

## 4.1 Catalog

Berikut ini adalah rancangan antarmuka pada modul Catalog. Modul ini dipakai oleh
Pengunjung tanpa perlu masuk ke akun, sehingga seluruh halamannya dirancang agar
dapat dipahami dalam sekali lihat.

[[SISIPKAN GAMBAR: Gambar 4-1 — Katalog Layanan]]

**Gambar 4-1. Antarmuka untuk fitur Menampilkan Katalog Layanan (F-03).**

Gambar 4-1 menunjukkan halaman katalog layanan. Pada bagian atas terdapat judul
halaman, kolom pencarian "Cari Produk", dan dua pilihan penyaring, yaitu daftar
kategori serta pilihan pengurutan hasil. Di bawahnya, layanan ditampilkan dalam
susunan kartu empat kolom. Setiap kartu memuat lambang merek layanan, nama layanan,
daftar varian paket beserta harga per bulan dan tag periode tagihan yang tersedia,
tautan "Lihat Skema Harga" untuk membuka detail, serta tombol "Pesan" yang memenuhi
lebar kartu. Apabila sebuah varian sedang berdiskon, sebuah pita bertuliskan
"Diskon n%" tampil pada sudut kiri atas kartu, dan apabila paket bersifat preorder,
pita bertuliskan "Preorder" yang tampil. Tombol bundar bertanda tambah di kanan
bawah setiap kartu berfungsi menambahkan layanan ke daftar perbandingan. Di bagian
bawah halaman tersedia kontrol paginasi beserta keterangan jumlah layanan yang
sedang ditampilkan.

[[SISIPKAN GAMBAR: Gambar 4-2 — Detail Layanan]]

**Gambar 4-2. Antarmuka untuk fitur Melihat Detail Layanan (F-07).**

Gambar 4-2 menunjukkan halaman detail layanan. Bagian atas memuat identitas layanan
berupa lambang merek, nama layanan, nama penyedia, kategori, dan nilai rating
beserta jumlah ulasan. Di bawahnya terdapat deskripsi layanan dan situs web resmi
penyedia. Bagian utama halaman menampilkan seluruh varian paket yang aktif dalam
bentuk kartu yang memuat nama kelompok varian, harga, durasi, jumlah perangkat yang
dapat dipakai bersamaan, daftar fitur, serta tombol "Berlangganan" pada setiap
kartu. Bagian bawah halaman memuat ulasan dari pengguna lain. Halaman ini menjadi
tempat membandingkan paket sebelum pelanggan menentukan pilihan.

[[SISIPKAN GAMBAR: Gambar 4-3 — Perbandingan Layanan]]

**Gambar 4-3. Antarmuka untuk fitur Perbandingan Layanan (F-06).**

Gambar 4-3 menunjukkan halaman perbandingan layanan. Halaman ini menampilkan dua
sampai empat layanan yang dipilih pengunjung secara berdampingan dalam bentuk tabel,
dengan setiap layanan menempati satu kolom. Baris yang dibandingkan meliputi harga
paket termurah, jumlah varian paket, jumlah perangkat maksimum, durasi terpanjang,
nama penyedia, kategori, nilai rating, dan daftar fitur. Sel yang memuat nilai paling
menguntungkan diberi penanda warna agar pengunjung dapat langsung melihat
keunggulannya. Setiap kolom dilengkapi tombol untuk melepas layanan dari daftar
perbandingan. Bila belum ada layanan yang dipilih, halaman menampilkan keadaan kosong
beserta arahan untuk memilih layanan terlebih dahulu.

[[SISIPKAN GAMBAR: Gambar 4-4 — Cara Berlangganan]]

**Gambar 4-4. Antarmuka untuk fitur Panduan Berlangganan.**

Gambar 4-4 menunjukkan halaman panduan "Cara Berlangganan". Bagian atas halaman
memuat lima kartu langkah berlangganan dengan bentuk setengah lingkaran, yaitu Pesan
Layanan, Pembayaran, Menunggu Proses, Pesanan Diterima, dan Selesai. Setiap kartu
memuat nomor langkah, judul langkah, dan ilustrasi pendukung. Bagian bawah halaman
menampilkan sebelas logo kanal pembayaran yang didukung, meliputi PermataBank, BSI,
BCA, BNI, Mandiri, OVO, DANA, ShopeePay, Alfamart, Bank BRI, dan LinkAja, disusun
lima logo per baris pada kartu putih. Halaman ditutup dengan uraian rincian tiap
langkah agar pengunjung memahami alur berlangganan sebelum melakukan pemesanan.

---

## 4.2 Transaction

Berikut ini adalah rancangan antarmuka pada modul Transaction. Modul ini hanya dapat
diakses oleh Pelanggan yang telah masuk, sehingga identitas pemesan selalu dikenali
oleh sistem.

[[SISIPKAN GAMBAR: Gambar 4-5 — Konfirmasi Pemesanan]]

**Gambar 4-5. Antarmuka untuk fitur Pembelian Layanan Premium (F-08).**

Gambar 4-5 menunjukkan halaman konfirmasi pemesanan. Halaman ini memuat tiga bagian
utama. Bagian pertama adalah ringkasan paket yang berisi nama layanan, nama varian
paket, durasi, jumlah perangkat, dan daftar fitur. Bagian kedua adalah pemilihan kanal
pembayaran dalam bentuk daftar pilihan yang memuat metode virtual account, dompet
digital, QRIS, dan gerai retail. Bagian ketiga adalah rincian biaya yang memuat harga
paket dan total yang harus dibayar. Pada bagian bawah terdapat tombol "Buat Pesanan".
Apabila pelanggan telah memiliki langganan aktif pada layanan yang sama, sistem
menampilkan pemberitahuan bahwa pembelian ini akan memperpanjang masa aktif mulai
tanggal berakhir sebelumnya.

[[SISIPKAN GAMBAR: Gambar 4-6 — Detail Pesanan]]

**Gambar 4-6. Antarmuka untuk fitur Menampilkan Status Transaksi (F-10).**

Gambar 4-6 menunjukkan halaman detail pesanan. Bagian atas memuat kode pesanan,
status pesanan dalam bentuk lencana berwarna, dan batas waktu pembayaran. Warna
lencana mengikuti jenis status, yaitu hijau untuk pesanan selesai, biru untuk
diproses, kuning untuk menunggu, dan merah untuk dibatalkan. Bagian tengah memuat
instruksi pembayaran beserta nominal yang harus dibayar dan kanal pembayaran yang
dipilih. Pada versi simulasi ini, halaman juga menyediakan tombol untuk menandai
pembayaran berhasil atau gagal sebagai sarana demonstrasi. Bagian bawah memuat
rincian pesanan berupa nama layanan, varian paket, durasi, dan waktu pesanan dibuat.
Apabila pesanan telah menghasilkan langganan aktif, halaman menampilkan tautan menuju
halaman Langganan Saya.

---

## 4.3 Subscription

Berikut ini adalah rancangan antarmuka pada modul Subscription. Modul ini dipakai
Pelanggan untuk memantau masa aktif dan memperpanjang langganannya.

[[SISIPKAN GAMBAR: Gambar 4-7 — Langganan Saya]]

**Gambar 4-7. Antarmuka untuk fitur Riwayat Langganan (F-12).**

Gambar 4-7 menunjukkan halaman "Langganan Saya". Bagian atas memuat ringkasan jumlah
langganan yang dipisahkan menurut kondisinya, yaitu langganan aktif, langganan yang
akan berakhir, dan langganan yang sudah berakhir. Di bawahnya, setiap langganan
ditampilkan dalam kartu tersendiri yang memuat nama layanan, nama varian paket,
tanggal mulai, tanggal berakhir, dan jumlah hari yang tersisa. Masa aktif
digambarkan dengan bilah kemajuan sehingga pelanggan dapat menilai sisa waktunya
secara cepat; langganan yang tinggal sedikit hari diberi penanda warna peringatan.
Setiap kartu dilengkapi tombol "Perpanjang" dan sebuah saklar untuk menyalakan atau
mematikan perpanjangan otomatis. Halaman juga menyediakan penyaring untuk menampilkan
langganan menurut kondisinya.

---

## 4.4 Admin

Berikut ini adalah rancangan antarmuka pada modul Admin. Seluruh halaman pada modul
ini memakai satu kerangka tata letak yang sama, yaitu bilah samping berwarna ungu di
sebelah kiri dan area konten berwarna putih di sebelah kanan. Bilah samping memuat
menu Dashboard, Pesanan, Pengguna, Produk, Pembayaran, dan Laporan, serta menu
Pengaturan dan Keluar pada bagian bawah. Kepala area konten memuat tanggal hari ini,
lonceng notifikasi beserta penghitung pesanan yang perlu ditangani, dan identitas
pengelola yang sedang masuk. Kerangka tetap ini dipakai agar pengelola selalu
menemukan menu pada posisi yang sama di seluruh halaman.

[[SISIPKAN GAMBAR: Gambar 4-8 — Dashboard Pengelola]]

**Gambar 4-8. Antarmuka untuk fitur Dashboard Pengelola (F-16).**

Gambar 4-8 menunjukkan halaman dashboard pengelola. Bagian atas memuat empat kartu
metrik, yaitu Total Pesanan, Pendapatan bulan berjalan, Total Pengguna, dan Laporan
yang perlu ditangani. Setiap kartu memuat nilai utama beserta keterangan
perbandingannya terhadap periode sebelumnya. Bagian tengah memuat diagram batang
jumlah pesanan selama empat belas hari terakhir dan diagram pendapatan enam bulan
terakhir. Bagian bawah memuat komposisi status pesanan dalam bentuk bilah persentase,
daftar produk terlaris beserta jumlah transaksinya, dan tabel pesanan terbaru yang
memuat kode pesanan, nama pelanggan, produk, nominal, dan status.

[[SISIPKAN GAMBAR: Gambar 4-9 — Manajemen Pesanan]]

**Gambar 4-9. Antarmuka untuk fitur Pengelolaan dan Pemantauan Transaksi (F-16).**

Gambar 4-9 menunjukkan halaman manajemen pesanan. Bagian atas memuat empat kartu
ringkasan, yaitu Total Pesanan, jumlah pesanan yang Menunggu, jumlah pesanan yang
Selesai Hari Ini, dan jumlah pesanan yang Dibatalkan. Di bawahnya terdapat tab
penyaring status, yaitu Semua, Selesai, Diproses, Menunggu, dan Dibatalkan, serta
kolom pencarian yang dapat dipakai untuk mencari kode pesanan, nama pelanggan, atau
surel. Tabel pesanan memuat kolom ID Pesanan, Tanggal, Pelanggan, Produk, Metode
Pembayaran, Total, Status, dan Aksi. Pada kolom Aksi tersedia tombol untuk menandai
pesanan berhasil atau gagal secara manual serta tombol untuk membuka rincian
pesanan. Penandaan berhasil akan otomatis membentuk langganan bagi pelanggan terkait.

[[SISIPKAN GAMBAR: Gambar 4-10 — Manajemen Produk]]

**Gambar 4-10. Antarmuka untuk fitur Pengelolaan Data Layanan dan Paket (F-13, F-14).**

Gambar 4-10 menunjukkan halaman manajemen produk. Bagian atas memuat empat kartu
ringkasan, yaitu Total Produk, Produk Aktif, Stok Habis, dan jumlah Kategori. Di
bawahnya terdapat baris penyaring yang memuat kolom pencarian nama produk, pilihan
kategori, pilihan status, pilihan tipe, serta tombol Reset dan Export. Tabel produk
memuat kolom ID Produk, Produk beserta keterangan singkatnya, Kategori, Tipe, Harga,
Stok, Status, Tanggal Dibuat, dan Aksi. Melalui kolom Aksi, pengelola dapat membuka
halaman pengelolaan paket untuk menambah, menyunting, atau menghapus varian paket
pada layanan tersebut, termasuk menetapkan harga, masa aktif, jumlah perangkat,
persentase diskon, penanda preorder, dan jumlah stok.

[[SISIPKAN GAMBAR: Gambar 4-11 — Manajemen Pengguna]]

**Gambar 4-11. Antarmuka untuk fitur Pengelolaan Data Pengguna (F-15).**

Gambar 4-11 menunjukkan halaman manajemen pengguna. Bagian atas memuat empat kartu
ringkasan, yaitu Total Pengguna, jumlah akun Aktif, jumlah akun Non-aktif, dan jumlah
akun yang Disuspend, masing-masing disertai persentasenya terhadap total. Di bawahnya
terdapat kolom pencarian yang dapat dipakai untuk mencari nama, surel, atau nomor
telepon, serta dua penyaring untuk peran dan status akun. Tabel pengguna memuat kolom
Pengguna berupa inisial, nama, dan surel, lalu No. Telepon, Role, Status, Tanggal
Daftar, jumlah Pesanan, dan Terakhir Login. Kolom Role dan Status berupa pilihan yang
dapat diubah langsung dari tabel, sehingga pengelola dapat mengubah peran pengguna
atau menonaktifkan akun tanpa berpindah halaman. Sistem menolak perubahan peran dan
status terhadap akun pengelola yang sedang masuk agar akses ke panel tidak hilang.

---

# CATATAN TAMBAHAN (jangan ikut di-paste)

## 1. Temuan lain pada dokumen SRS Anda

Saat membaca berkas `.docx`, saya menemukan beberapa hal yang sebaiknya diperiksa.
Ini di luar Bab 4, jadi tidak saya ubah — hanya saya laporkan.

**a. Judul sub-bab Bab 3 masih memakai placeholder.** Baris berikut masih bertanda
kurung siku:

- `### Use Case Diagram : <Modul Catalog>`
- `### Use Case Diagram : <Modul Transaction>`
- `### Use Case Diagram : <Modul Subscription>`
- `### Use Case Diagram : <Modul Admin>`

Ganti menjadi tanpa tanda kurung, misalnya `### Use Case Diagram Modul Catalog`.
Hal yang sama juga muncul pada keterangan gambar, misalnya
`Gambar 3-1. Use Case Diagram <Modul Catalog>`.

**b. "Tabel 3-3" dipakai dua kali.** Sekali untuk *Memfilter layanan* (Bab 3.1) dan
sekali lagi untuk *Kamus Data* pada bagian Entity Relationship Diagram. Saya sudah
memastikan nomor **Tabel 3-33 sampai 3-36 belum dipakai**, sehingga Kamus Data
sebaiknya memakai nomor **Tabel 3-33** agar tidak ada dua tabel bernomor sama.

**c. Kebutuhan fungsional sudah lengkap sampai F-18** dan non-fungsional sampai
**NF-18**. Ini berbeda dengan catatan pada `prim/docs/SRS-SINKRONISASI.md` di
repositori, yang memakai penomoran F-01 sampai F-23 dan NF-01 sampai NF-14. Karena
itu pada keterangan gambar di atas saya hanya merujuk nomor yang benar-benar ada di
dokumen Anda. Sebaiknya dokumen SRS dan dokumen sinkronisasi diselaraskan agar tidak
ada dua daftar kebutuhan yang berbeda.

**d. Judul Bab 4 masih "Rancangan Antarmuka".** Karena Bab 4 hanya membahas
antarmuka, arsitektur sistem, rancangan basis data (kamus data sudah ada di Bab 3),
dan rancangan teknologi **tidak** dibahas di dalamnya. Bila dosen menuntut bagian
tersebut, tambahkan sebagai sub-bab tersendiri atau sebagai lampiran, bukan
diselipkan ke dalam Bab 4.

## 2. Yang sudah benar dan tidak perlu diubah

Agar tidak menimbulkan keraguan, beberapa hal yang saya periksa dan sudah sesuai:

- Penomoran **Tabel 3-1 sampai 3-32 lengkap** tanpa nomor yang melompat.
- Judul *Tabel 3-3. Kamus Data* sudah berada **tepat di atas** tabelnya, setelah
  keterangan Gambar 3-5 (ERD). Susunan ini sudah benar.
- Urutan gambar Bab 3 sudah berurutan: Gambar 3-1 (use case), 3-2 (aktivitas
  registrasi), 3-3 (aktivitas login), 3-4 (aktivitas pembelian dan pembayaran),
  3-5 (ERD).
- Bab 2 sudah memuat Tabel 2-1 (kebutuhan fungsional) dan Tabel 2-2 (kebutuhan
  non-fungsional) secara lengkap.


## 2. Isi setiap modul yang belum tertangkap layar

Bab 4 hanya memuat 11 gambar, sedangkan sistem memiliki lebih dari 20 halaman. Bila
dosen meminta seluruh halaman terdokumentasi, halaman berikut dapat ditambahkan
sebagai gambar lanjutan dengan pola keterangan yang sama:

- **Bersama:** Beranda, Masuk, Daftar, Lupa Kata Sandi, Verifikasi Perangkat, Laporan
  Kendala, Pusat Pesanan, Kode Login, Pengaturan Akun
- **Transaction:** Riwayat Transaksi
- **Subscription:** Perpanjangan Langganan
- **Admin:** Manajemen Pembayaran, Laporan, Pengaturan Profil, Pengaturan Notifikasi,
  Manajemen Kategori, Manajemen Penyedia

Bila ditambahkan, penomoran gambar cukup dilanjutkan menjadi Gambar 4-12 dan
seterusnya.
