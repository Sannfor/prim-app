# CATATAN SEBELUM SALIN-TEMPEL

Berkas ini berisi isi untuk **BAB 4 — RANCANGAN ANTARMUKA** pada dokumen
*SRS Aplikasi PRIM Kelompok 7.docx*.

## Cara saya menyusun ini

Saya membaca berkas `SRS Aplikasi PRIM Kelompok 7.docx` yang ada di komputer Anda
(2.377 KB) dengan mengekstrak isinya langsung dari XML Word, bukan menebak dari
Google Docs. Dari situ saya tahu persis susunan Bab 4 Anda:

| Keadaan di dokumen Anda | Isi |
|---|---|
| Judul bab | `# Rancangan Antarmuka` (bukan "Rancangan Sistem") |
| Kalimat pengantar | `<rancangan antarmuka digambarkan dan diterangkan di bagian ini>` |
| Sub-bab | `## <Nama Modul 1>`, `## <Nama Modul 2>`, `## <Nama Modul 3>` — **masih placeholder** |
| Isi tiap sub-bab | Gambar antarmuka + keterangan `<terangkan gambar diatas, seperti terdapat fungsi apa aja dan lain-lain.>` |
| Gambar | `Gambar 4-1.` sampai `Gambar 4-4.` — 4 gambar |
| Tabel | Belum ada tabel di Bab 4 |

**Dua penyesuaian yang saya lakukan:**

1. **Judul dan cakupan.** Draf saya sebelumnya berjudul "Rancangan Sistem" dan
   memuat arsitektur, basis data, serta teknologi. Itu tidak sesuai dengan dokumen
   Anda, yang Bab 4-nya khusus **Rancangan Antarmuka**. Berkas ini sudah mengikuti
   dokumen.
2. **Jumlah modul.** Dokumen Anda hanya menyediakan tiga sub-bab modul di Bab 4,
   padahal Bab 3 dokumen Anda sendiri sudah memuat **empat** Use Case Diagram, yaitu
   `<Modul Catalog>`, `<Modul Transaction>`, `<Modul Subscription>`, dan
   `<Modul Admin>`. Berkas ini memakai **empat sub-bab** agar konsisten dengan Bab 3.

## Langkah menempel ke Google Docs

1. Buka dokumen, gulir ke **Bab 4 Rancangan Antarmuka**.
2. Hapus **seluruh** isi Bab 4 yang lama, termasuk baris `<rancangan antarmuka
   digambarkan dan diterangkan di bagian ini>`, tiga sub-bab `<Nama Modul 1/2/3>`,
   dan empat kalimat `<terangkan gambar diatas, ...>`.
3. Salin isi Bab 4 dari berkas ini, mulai dari baris `BAB 4` di bawah garis pemisah.
4. Beri gaya **Heading 1** pada "BAB 4 RANCANGAN ANTARMUKA" dan **Heading 2** pada
   keempat sub-bab modul.
5. Sisipkan gambar pada setiap penanda `[[SISIPKAN GAMBAR: ...]]` melalui
   *Insert → Image → Upload from computer*. Setelah gambar masuk, **hapus baris
   penanda itu** agar tidak ikut tercetak.

## Daftar gambar

Sebelas gambar berikut sudah tersedia di folder `docs/screens/`. Gambar diambil pada
lebar 1440 piksel dalam mode penuh satu halaman (bukan hanya bagian yang terlihat di
layar) oleh skrip `tools/screenshot.mjs`.

| Gambar | Berkas | Ukuran | Halaman |
|---|---|---|---|
| 4-1 | `docs/screens/gambar-4-1-beranda.png` | 1440x2360 | Halaman Beranda |
| 4-2 | `docs/screens/gambar-4-2-katalog.png` | 1440x2221 | Menampilkan Katalog Layanan |
| 4-3 | `docs/screens/gambar-4-3-detail-layanan.png` | 1440x1216 | Melihat Detail Layanan |
| 4-4 | `docs/screens/gambar-4-4-cara-berlangganan.png` | 1440x2021 | Panduan Berlangganan |
| 4-5 | `docs/screens/gambar-4-5-konfirmasi-pemesanan.png` | 1440x1395 | Pembelian Layanan Premium |
| 4-6 | `docs/screens/gambar-4-6-detail-pesanan.png` | 1440x1090 | Menampilkan Status Transaksi |
| 4-7 | `docs/screens/gambar-4-7-langganan-saya.png` | 1440x1081 | Riwayat Langganan |
| 4-8 | `docs/screens/gambar-4-8-dashboard.png` | 1440x1934 | Dashboard Pengelola |
| 4-9 | `docs/screens/gambar-4-9-manajemen-pesanan.png` | 1440x1498 | Pengelolaan dan Pemantauan Transaksi |
| 4-10 | `docs/screens/gambar-4-10-manajemen-produk.png` | 1440x1920 | Pengelolaan Data Layanan dan Paket |
| 4-11 | `docs/screens/gambar-4-11-manajemen-pengguna.png` | 1440x1318 | Pengelolaan Data Pengguna |

Berkas `docs/screens/tambahan-pusat-pesanan.png` berisi tangkapan halaman Pusat
Pesanan yang dapat dipakai sebagai gambar cadangan.

**Halaman Perbandingan Layanan belum memiliki gambar** karena halaman itu hanya
menampilkan isi setelah pengunjung memilih dua sampai empat layanan. Untuk
menambahkannya, buka aplikasi, tandai beberapa layanan dengan tombol tambah pada
kartu katalog, baru buka `/bandingkan` dan tangkap layarnya.

## Cara menghasilkan ulang gambar

Bila tampilan aplikasi berubah, seluruh gambar dapat dibuat ulang sekaligus:

```powershell
# dijalankan dari akar proyek RPL
powershell -ExecutionPolicy Bypass -File tools\buat-gambar-bab4.ps1
```

Skrip itu menyalakan server, masuk sebagai pelanggan dan administrator, menangkap
layar seluruh halaman, lalu mematikan server kembali. Hasilnya masuk ke
`docs/screens/`.

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

Berikut ini adalah rancangan antarmuka pada modul Catalog. Modul ini dipakai oleh Pengunjung tanpa perlu masuk ke akun, sehingga seluruh halamannya dirancang agar dapat dipahami dalam sekali lihat.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-1-beranda.png]]

**Gambar 4-1. Antarmuka untuk fitur Halaman Beranda.**

Gambar 4-1 menunjukkan halaman beranda yang menjadi titik masuk aplikasi. Bagian
atas memuat bilah navigasi dengan menu Beranda, Layanan, Cara Berlangganan, Laporan
Kendala, dan tombol Login. Bagian hero memuat judul utama, kalimat pengantar, tombol
"Lihat Layanan", dan ilustrasi pendukung di sisi kanan, dengan transisi awan pada
kaki bagian. Di bawahnya ditampilkan bagian layanan populer berisi delapan kartu
layanan beserta harga termurahnya, bagian keunggulan berisi empat kartu alasan
memakai PRIM, dan bagian ajakan mendaftar. Halaman ditutup dengan footer yang memuat
tautan navigasi, kanal bantuan, dan informasi kontak.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-2-katalog.png]]

**Gambar 4-2. Antarmuka untuk fitur Menampilkan Katalog Layanan (F-03).**

Gambar 4-2 menunjukkan halaman katalog layanan. Pada bagian atas terdapat judul
halaman, kolom pencarian "Cari Produk", serta dua pilihan penyaring, yaitu daftar
kategori dan pilihan pengurutan hasil. Di bawahnya, layanan ditampilkan dalam
susunan kartu empat kolom. Setiap kartu memuat lambang merek layanan, nama layanan,
daftar varian paket beserta harga per bulan dan tag periode tagihan yang tersedia,
tautan "Lihat Skema Harga" untuk membuka detail, serta tombol "Pesan" yang memenuhi
lebar kartu. Apabila sebuah varian sedang berdiskon, sebuah pita bertuliskan
"Diskon n%" tampil pada sudut kiri atas kartu, dan apabila paket bersifat preorder,
pita bertuliskan "Preorder" yang tampil. Tombol bundar bertanda tambah di kanan
bawah setiap kartu berfungsi menambahkan layanan ke daftar perbandingan. Di bagian
bawah halaman tersedia kontrol paginasi beserta keterangan jumlah layanan yang
sedang ditampilkan.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-3-detail-layanan.png]]

**Gambar 4-3. Antarmuka untuk fitur Melihat Detail Layanan (F-07).**

Gambar 4-3 menunjukkan halaman detail layanan. Bagian atas memuat identitas
layanan berupa lambang merek, nama layanan, nama penyedia, kategori, dan nilai
rating beserta jumlah ulasan. Di bawahnya terdapat deskripsi layanan dan situs web
resmi penyedia. Bagian utama halaman menampilkan seluruh varian paket yang aktif
dalam bentuk kartu yang memuat nama kelompok varian, harga, durasi, jumlah perangkat
yang dapat dipakai bersamaan, daftar fitur, serta tombol "Berlangganan" pada setiap
kartu. Bagian bawah halaman memuat ulasan dari pengguna lain. Halaman ini menjadi
tempat membandingkan paket sebelum pelanggan menentukan pilihan.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-4-cara-berlangganan.png]]

**Gambar 4-4. Antarmuka untuk fitur Panduan Berlangganan.**

Gambar 4-4 menunjukkan halaman panduan "Cara Berlangganan". Bagian atas halaman
memuat lima kartu langkah berlangganan dengan bentuk setengah lingkaran, yaitu Pesan
Layanan, Pembayaran, Menunggu Proses, Pesanan Diterima, dan Selesai. Setiap kartu
memuat nomor langkah, judul langkah, dan ilustrasi pendukung. Bagian bawah halaman
menampilkan sebelas logo kanal pembayaran yang didukung, meliputi PermataBank, BSI,
BCA, BNI, Mandiri, OVO, DANA, ShopeePay, Alfamart, Bank BRI, dan LinkAja, yang
disusun lima logo per baris pada kartu putih. Halaman ditutup dengan uraian rincian
tiap langkah agar pengunjung memahami alur berlangganan sebelum melakukan
pemesanan.


---

## 4.2 Transaction

Berikut ini adalah rancangan antarmuka pada modul Transaction. Modul ini hanya dapat diakses oleh Pelanggan yang telah masuk, sehingga identitas pemesan selalu dikenali oleh sistem pada setiap halaman.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-5-konfirmasi-pemesanan.png]]

**Gambar 4-5. Antarmuka untuk fitur Pembelian Layanan Premium (F-08).**

Gambar 4-5 menunjukkan halaman konfirmasi pemesanan. Halaman ini memuat tiga
bagian utama. Bagian pertama adalah ringkasan paket yang berisi nama layanan, nama
varian paket, durasi, jumlah perangkat, dan daftar fitur. Bagian kedua adalah
pemilihan kanal pembayaran dalam bentuk daftar pilihan yang memuat metode virtual
account, dompet digital, QRIS, dan gerai retail. Bagian ketiga adalah rincian biaya
yang memuat harga paket dan total yang harus dibayar. Pada bagian bawah terdapat
tombol "Buat Pesanan". Apabila pelanggan telah memiliki langganan aktif pada layanan
yang sama, sistem menampilkan pemberitahuan bahwa pembelian ini akan memperpanjang
masa aktif mulai tanggal berakhir sebelumnya.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-6-detail-pesanan.png]]

**Gambar 4-6. Antarmuka untuk fitur Menampilkan Status Transaksi (F-10).**

Gambar 4-6 menunjukkan halaman detail pesanan. Bagian atas memuat kode pesanan,
status pesanan dalam bentuk lencana berwarna, dan batas waktu pembayaran. Warna
lencana mengikuti jenis status, yaitu hijau untuk pesanan selesai, biru untuk
diproses, kuning untuk menunggu, dan merah untuk dibatalkan. Bagian tengah memuat
instruksi pembayaran beserta nominal yang harus dibayar dan kanal pembayaran yang
dipilih. Pada versi simulasi ini, halaman juga menyediakan tombol untuk menandai
pembayaran berhasil atau gagal sebagai sarana demonstrasi. Bagian bawah memuat
rincian pesanan berupa nama layanan, varian paket, durasi, dan waktu pesanan
dibuat. Apabila pesanan telah menghasilkan langganan aktif, halaman menampilkan
tautan menuju halaman Langganan Saya.


---

## 4.3 Subscription

Berikut ini adalah rancangan antarmuka pada modul Subscription. Modul ini dipakai Pelanggan untuk memantau masa aktif dan memperpanjang langganannya.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-7-langganan-saya.png]]

**Gambar 4-7. Antarmuka untuk fitur Riwayat Langganan (F-12).**

Gambar 4-7 menunjukkan halaman "Langganan Saya". Bagian atas memuat ringkasan
jumlah langganan yang dipisahkan menurut kondisinya, yaitu langganan aktif, langganan
yang akan berakhir, dan langganan yang sudah berakhir. Di bawahnya, setiap langganan
ditampilkan dalam kartu tersendiri yang memuat nama layanan, nama varian paket,
tanggal mulai, tanggal berakhir, dan jumlah hari yang tersisa. Masa aktif digambarkan
dengan bilah kemajuan sehingga pelanggan dapat menilai sisa waktunya secara cepat,
dan langganan yang tinggal sedikit hari diberi penanda warna peringatan. Setiap
kartu dilengkapi tombol "Perpanjang" dan sebuah saklar untuk menyalakan atau
mematikan perpanjangan otomatis. Halaman juga menyediakan penyaring untuk
menampilkan langganan menurut kondisinya.


---

## 4.4 Admin

Berikut ini adalah rancangan antarmuka pada modul Admin. Seluruh halaman pada modul ini memakai satu kerangka tata letak yang sama, yaitu bilah samping berwarna ungu di sebelah kiri dan area konten berwarna putih di sebelah kanan. Bilah samping memuat menu Dashboard, Pesanan, Pengguna, Produk, Pembayaran, dan Laporan, serta menu Pengaturan dan Keluar pada bagian bawah. Kepala area konten memuat tanggal hari ini, lonceng notifikasi beserta penghitung pesanan yang perlu ditangani, dan identitas pengelola yang sedang masuk. Kerangka tetap ini dipakai agar pengelola selalu menemukan menu pada posisi yang sama di seluruh halaman.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-8-dashboard.png]]

**Gambar 4-8. Antarmuka untuk fitur Dashboard Pengelola (F-16).**

Gambar 4-8 menunjukkan halaman dashboard pengelola. Bagian atas memuat kartu
metrik yang menampilkan jumlah layanan terdaftar, paket langganan, penyedia layanan,
pengguna terdaftar, langganan aktif, dan transaksi menunggu. Setiap kartu memuat
nilai utama beserta keterangan pendukungnya. Bagian tengah memuat nilai pendapatan
bulan berjalan beserta pertumbuhannya dibanding bulan lalu dan diagram batang jumlah
pesanan selama empat belas hari terakhir. Bagian bawah memuat komposisi status
pesanan dalam bentuk bilah persentase, daftar produk terlaris beserta jumlah
transaksinya, dan tabel pesanan terbaru yang memuat kode pesanan, nama pelanggan,
produk, nominal, dan status.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-9-manajemen-pesanan.png]]

**Gambar 4-9. Antarmuka untuk fitur Pengelolaan dan Pemantauan Transaksi (F-16).**

Gambar 4-9 menunjukkan halaman manajemen pesanan. Bagian atas memuat empat kartu
ringkasan, yaitu Total Pesanan, jumlah pesanan yang Menunggu, jumlah pesanan yang
Selesai Hari Ini, dan jumlah pesanan yang Dibatalkan. Di bawahnya terdapat tab
penyaring status, yaitu Semua, Selesai, Diproses, Menunggu, dan Dibatalkan, serta
kolom pencarian yang dapat dipakai untuk mencari kode pesanan, nama pelanggan, atau
surel. Tabel pesanan memuat kolom ID Pesanan, Tanggal, Pelanggan, Produk, Metode
Pembayaran, Total, Status, dan Aksi. Pada kolom Aksi tersedia tombol untuk menandai
pesanan berhasil atau gagal secara manual serta tombol untuk membuka rincian
pesanan. Penandaan berhasil akan otomatis membentuk langganan bagi pelanggan
terkait.


[[SISIPKAN GAMBAR: docs/screens/gambar-4-10-manajemen-produk.png]]

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


[[SISIPKAN GAMBAR: docs/screens/gambar-4-11-manajemen-pengguna.png]]

**Gambar 4-11. Antarmuka untuk fitur Pengelolaan Data Pengguna (F-15).**

Gambar 4-11 menunjukkan halaman manajemen pengguna. Bagian atas memuat empat
kartu ringkasan, yaitu Total Pengguna, jumlah akun Aktif, jumlah akun Non-aktif, dan
jumlah akun yang Disuspend, masing-masing disertai persentasenya terhadap total. Di
bawahnya terdapat kolom pencarian yang dapat dipakai untuk mencari nama, surel, atau
nomor telepon, serta dua penyaring untuk peran dan status akun. Tabel pengguna
memuat kolom Pengguna berupa inisial, nama, dan surel, lalu No. Telepon, Role,
Status, Tanggal Daftar, jumlah Pesanan, dan Terakhir Login. Kolom Role dan Status
berupa pilihan yang dapat diubah langsung dari tabel, sehingga pengelola dapat
mengubah peran pengguna atau menonaktifkan akun tanpa berpindah halaman. Sistem
menolak perubahan peran dan status terhadap akun pengelola yang sedang masuk agar
akses ke panel tidak hilang.

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

**c. Kebutuhan fungsional sudah lengkap sampai F-18** dan non-fungsional sampai
**NF-18**. Ini berbeda dengan catatan pada `prim/docs/SRS-SINKRONISASI.md` di
repositori, yang memakai penomoran F-01 sampai F-23 dan NF-01 sampai NF-14. Karena
itu pada keterangan gambar di atas saya hanya merujuk nomor yang benar-benar ada di
dokumen Anda. Sebaiknya dokumen SRS dan dokumen sinkronisasi diselaraskan.

**d. Bab 4 hanya membahas antarmuka.** Arsitektur sistem, rancangan basis data
(kamus data sudah ada di Bab 3), dan rancangan teknologi tidak dibahas di dalamnya.
Bila dosen menuntut bagian tersebut, tambahkan sebagai sub-bab tersendiri atau
sebagai lampiran.

## 2. Yang sudah benar dan tidak perlu diubah

- Penomoran **Tabel 3-1 sampai 3-32 lengkap** tanpa nomor yang melompat.
- Judul *Tabel 3-3. Kamus Data* sudah berada tepat di atas tabelnya, setelah
  keterangan Gambar 3-5 (ERD).
- Urutan gambar Bab 3 sudah berurutan: Gambar 3-1 (use case), 3-2 (aktivitas
  registrasi), 3-3 (aktivitas login), 3-4 (aktivitas pembelian dan pembayaran),
  3-5 (ERD).
- Bab 2 sudah memuat Tabel 2-1 (kebutuhan fungsional) dan Tabel 2-2 (kebutuhan
  non-fungsional) secara lengkap.
