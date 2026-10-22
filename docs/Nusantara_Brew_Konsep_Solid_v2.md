# Nusantara Brew — Konsep Sistem v2

**Status:** rancangan rekomendasi untuk disepakati tim sebelum membuat UI, migration, atau backend. Bukan laporan implementasi.

**Tim:** 3 orang.  
**Tagline:** “Satu platform untuk semua rasa lokal.”  
**Dasar revisi:** `Nusantara_Brew_AI_Context.md`. File asli tidak diganti.

---

## 1. Identitas dan posisi sistem

**Nusantara Brew adalah aplikasi web pemesanan kopi dari berbagai coffee shop lokal dalam satu platform. Pelanggan dapat memilih toko, melihat menu, membuat pesanan untuk diambil sendiri atau dinikmati di tempat, lalu memantau proses pembuatannya.**

Makna “lintas toko”: satu akun pelanggan dapat berbelanja di toko berbeda pada transaksi berbeda. Bukan mencampur produk beberapa toko dalam satu pembayaran.

Setiap coffee shop merupakan usaha yang datanya terpisah. Bukan diasumsikan sebagai cabang dari satu perusahaan. Platform mengelola akses dan katalog; toko mengelola menu serta operasionalnya sendiri.

**Prinsip inti:** banyak toko dalam satu platform, satu toko dalam satu pesanan.

### 1.1 Masalah yang ditangani

- Pelanggan perlu menemukan beberapa coffee shop tanpa memakai aplikasi berbeda untuk setiap toko.
- Toko perlu katalog, antrean pesanan, dan pencatatan transaksi sederhana.
- Pemilik perlu mengelola menu serta melihat hasil penjualan tokonya sendiri.
- Tim perlu menunjukkan MVC, CRUD, relasi database, autentikasi, otorisasi, validasi, dan transaksi database secara utuh.

### 1.2 Bukan tujuan versi pertama

Tidak membangun marketplace komersial lengkap, layanan antar, sistem akuntansi, atau aplikasi pengelolaan bahan baku. Kelengkapan alur lebih penting daripada banyaknya fitur.

---

## 2. Hasil analisis dokumen awal

| Bagian | Masalah atau keputusan yang belum tegas | Revisi yang ditetapkan dalam rancangan ini |
|---|---|---|
| Pemesanan lintas toko | Mudah dimaknai sebagai checkout campuran. | Banyak toko dapat dijelajahi; setiap checkout hanya untuk satu toko. |
| Keranjang | Aturan menyebut satu keranjang aktif, tetapi relasi dan tabel masih memungkinkan beberapa keranjang aktif. | Satu keranjang persisten per pelanggan melalui `UNIQUE(carts.user_id)`. Keranjang digunakan ulang, bukan diarsipkan setiap checkout. |
| Stok | Aturan menyebut pengurangan saat diproses, sedangkan roadmap menyebut saat checkout. | Stok tersedia dikurangi secara atomik saat checkout berhasil; dilepas kembali jika pesanan belum dibayar lalu dibatalkan atau kedaluwarsa. |
| Status | Tidak ada tahap yang membedakan sudah dibayar tetapi belum mulai dibuat. | Tambahkan `queued` di antara `pending_payment` dan `processing`. |
| Pembayaran | Ada `failed` dan `refunded`, tetapi belum ada aturan percobaan bayar, pembatalan berbayar, atau pengembalian uang. | MVP menggunakan `pending`, `paid`, `voided`; refund dan riwayat percobaan bayar ditunda. |
| POS kasir | Pesanan langsung tanpa akun pelanggan bertentangan dengan `orders.user_id` yang wajib. | POS dan pelanggan tamu ditunda. Semua order MVP berasal dari pelanggan yang login. |
| Staf | Kasir dan barista terpisah menambah matriks akses serta layar. | Satu peran staf operasional pada MVP. Pemisahan kasir/barista menjadi pengembangan berikutnya. |
| Jam buka | Ada JSON jam buka, tetapi belum jelas apa yang menentukan boleh checkout. | `is_accepting_orders` menjadi sakelar operasional; jam buka hanya informasi pada MVP. |
| Konkurensi | Belum ada aturan dua pelanggan memesan stok terakhir atau menekan checkout berulang. | Penguncian baris, transaksi database, versi keranjang, dan constraint unik menjadi persyaratan backend. |
| Isolasi toko | Middleware role belum cukup membatasi objek berdasarkan toko. | Policy dan query berbasis kepemilikan/penugasan wajib diterapkan pada setiap akses. |
| Histori | Snapshot item sudah tepat, tetapi perubahan status belum memiliki jejak. | Pertahankan snapshot dan tambahkan `order_status_histories`. |

Bagian yang dipertahankan: identitas Nusantara Brew, katalog multi-toko, checkout satu toko, Laravel/MySQL, katalog CRUD, pickup/dine-in, snapshot transaksi, serta larangan menghapus riwayat pesanan.

---

## 3. Ruang lingkup versi pertama

### 3.1 Fitur wajib — MVP

| Modul | Fitur yang dikerjakan |
|---|---|
| Akun | Registrasi pelanggan, login/logout, profil dasar, status akun aktif/nonaktif. |
| Penemuan toko | Daftar toko yang disetujui admin, pencarian nama toko, detail alamat dan informasi buka. |
| Katalog | Kategori dan menu per toko, harga, foto, deskripsi, stok porsi, status tersedia. |
| Keranjang | Satu toko, perubahan jumlah, catatan per menu, hapus item, konfirmasi sebelum mengganti toko. |
| Checkout | Pickup atau dine-in, kontak pemesan, pilihan metode bayar, validasi ulang harga dan stok. |
| Pembayaran | `qris_simulation` dan pencatatan `cash`; tidak ada integrasi pembayaran nyata. |
| Pesanan | Antrean, status berurutan, riwayat pelanggan, detail item, struk digital. |
| Operasional toko | Konfirmasi pembayaran tunai, mulai membuat, tandai siap, tandai selesai. |
| Pengelolaan toko | Profil, kategori, menu, pengisian stok, penugasan staf, buka/tutup penerimaan order. |
| Platform | Pembuatan akun berhak khusus, persetujuan/nonaktif toko, nonaktif akun. |
| Ringkasan toko | Jumlah pesanan selesai, omzet pesanan selesai, dan menu terjual pada rentang tanggal. |

### 3.2 Fitur lanjutan — bukan kriteria selesai MVP

- Pemisahan hak akses kasir dan barista.
- POS untuk pembeli langsung tanpa akun.
- `transfer_simulation`, upload bukti bayar, payment gateway, refund, pembayaran sebagian, serta settlement antar-toko.
- Delivery, kurir, peta/rute, jadwal pengambilan, dan reservasi meja.
- Varian berbayar, topping, ukuran, resep/bahan baku, voucher, poin, ulasan, dan rekomendasi AI.
- WebSocket, aplikasi mobile terpisah, backend microservices, dan ekspor laporan kompleks.

**Jangan memasukkan fitur lanjutan diam-diam ke migration atau UI MVP.** Jika diperlukan dosen, revisi ruang lingkup dan aturan datanya terlebih dahulu.

---

## 4. Aktor dan batas hak akses

### 4.1 Model peran

Setiap akun memiliki satu `users.role`: `customer`, `owner`, `staff`, atau `admin`.

- **Pengunjung:** tanpa login; hanya melihat toko aktif dan katalog.
- **Customer:** memiliki keranjang dan pesanan sendiri. Tidak mengubah harga, status pembayaran umum, atau status produksi.
- **Owner:** memiliki satu atau beberapa toko. Setiap halaman pengelolaan bekerja pada satu toko yang dipilih dan benar-benar dimilikinya. Owner juga dapat menjalankan tindakan staf di tokonya sendiri.
- **Staff:** ditugaskan ke maksimal satu toko aktif pada MVP. Tugas kasir dan barista digabung. Akun tanpa penugasan tidak memperoleh akses operasional.
- **Admin:** mengelola akun berhak khusus serta status platform toko. Tidak menjadi kasir atau barista universal dan tidak mengubah transaksi toko sesuka hati.

Akun operasional tidak sekaligus menjadi akun pelanggan pada MVP. Pengujian masing-masing alur menggunakan akun sesuai perannya. Ini penyederhanaan sengaja, bukan model multi-role.

### 4.2 Matriks akses

| Kemampuan | Pengunjung | Customer | Owner | Staff | Admin |
|---|---|---|---|---|---|
| Melihat toko aktif/katalog publik | Ya | Ya | Ya | Ya | Ya |
| Mengisi keranjang dan checkout | Tidak | Milik sendiri | Tidak | Tidak | Tidak |
| Melihat riwayat pesanan pelanggan | Tidak | Milik sendiri | Tidak | Tidak | Tidak |
| Mengelola profil/kategori/menu/stok toko | Tidak | Tidak | Toko sendiri | Tidak | Tidak |
| Mengatur penerimaan order | Tidak | Tidak | Toko sendiri | Tidak | Tidak |
| Menugaskan/melepas staf | Tidak | Tidak | Toko sendiri | Tidak | Tidak |
| Melihat antrean dan detail order operasional | Tidak | Tidak | Toko sendiri | Toko penugasan | Tidak |
| Konfirmasi tunai/perubahan status produksi | Tidak | Tidak | Toko sendiri | Toko penugasan | Tidak |
| Ringkasan penjualan | Tidak | Tidak | Toko sendiri | Tidak | Tidak |
| Membuat akun owner/staff dan mengaktifkan toko | Tidak | Tidak | Tidak | Tidak | Ya |
| Mengubah `coffee_shops.status` | Tidak | Tidak | Tidak | Tidak | Ya |

Customer tetap dapat melihat detail dan status order miliknya melalui halaman pelanggan. Itu berbeda dari hak membaca antrean operasional seluruh toko.

### 4.3 Onboarding yang dipakai

1. Registrasi publik selalu menghasilkan akun `customer`; nilai role dari request diabaikan.
2. Admin awal tersedia melalui proses setup/seed. Akun owner dan staff dibuat admin; tidak ada registrasi admin publik.
3. Owner mengajukan toko dengan status `pending`, lalu mengisi profil serta katalog.
4. Admin memeriksa dan mengaktifkan toko. Aktivasi tidak otomatis membuka penerimaan pesanan.
5. Owner menugaskan akun staff yang aktif dan belum terikat toko lain, lalu mengaktifkan penerimaan order.
6. Owner hanya mencari calon staff melalui email persis; sistem tidak membuka daftar semua pengguna atau staf toko lain. Penugasan yang tidak memenuhi syarat ditolak tanpa membocorkan data toko lain.

Penugasan owner terhadap staff milik toko lain ditolak. Perubahan role bukan fitur edit profil biasa. Penonaktifan akun diperiksa pada setiap request terautentikasi, bukan hanya saat login.

---

## 5. Pengalaman pelanggan dan operasional toko

### 5.1 Alur pelanggan

1. Pelanggan membuka daftar coffee shop aktif dan memilih satu toko.
2. Pelanggan melihat kategori, menu, harga, stok, dan status penerimaan order.
3. Saat menambah menu, sistem meminta login jika belum login.
4. Keranjang menampilkan nama toko, jumlah, catatan, harga, serta total.
5. Jika memilih menu dari toko lain, sistem meminta konfirmasi: pertahankan keranjang lama atau kosongkan lalu mulai dari toko baru. Membatalkan dialog tidak menghapus apa pun.
6. Pelanggan memilih pickup atau dine-in. Dine-in mewajibkan label meja; pickup tidak menyimpan label meja. Keduanya untuk pemesanan saat ini, bukan penjadwalan.
7. Pelanggan mengonfirmasi nama, nomor kontak, metode bayar, dan ringkasan terakhir.
8. Checkout berhasil membuat order `pending_payment` dan mencadangkan stok.
9. Pembayaran berhasil membuat order masuk `queued`; toko kemudian memproses sampai `completed`.
10. Pelanggan melihat detail, timeline, struk digital, serta riwayat miliknya.

Keranjang yang sudah checkout dikosongkan, tetapi order tetap tersimpan. Pelanggan boleh membuat order baru dari toko lain meskipun order lama masih diproses.

### 5.2 Alur toko

1. Owner memastikan toko menerima order dan menu tersedia.
2. Order belum dibayar terlihat dalam daftar menunggu pembayaran, bukan antrean produksi.
3. Staff/owner mengonfirmasi tunai setelah pembayaran diterima; simulasi QRIS diproses melalui tindakan simulasi pelanggan.
4. Order yang sudah dibayar masuk antrean `queued`.
5. Staff/owner menekan “Mulai dibuat”, lalu “Siap”.
6. Setelah pickup diserahkan atau dine-in disajikan, staff/owner menekan “Selesai”.
7. Ringkasan penjualan memakai order selesai dan sudah dibayar.

### 5.3 Toko tutup atau dinonaktifkan

- `coffee_shops.status = active` berarti boleh tampil sebagai toko yang disetujui platform.
- `is_accepting_orders = true` berarti toko sedang menerima pesanan baru.
- Add-to-cart dan checkout mensyaratkan kedua kondisi tersebut. Keranjang lama masih boleh dilihat, dikurangi, atau dikosongkan ketika toko tutup.
- Jam buka berupa informasi teks, bukan mesin penjadwalan otomatis. UI tidak boleh mengklaim toko buka hanya berdasarkan jam tersebut.
- Penutupan penerimaan atau nonaktif toko menghentikan order baru, tetapi tidak menghapus order lama. Pembayaran order yang sudah terbentuk tetap boleh diselesaikan sebelum batas waktu; order berbayar tetap dapat diproses oleh akun aktif yang berwenang.
- Riwayat order pelanggan tetap dapat diakses meskipun toko tidak lagi tampil di katalog publik.

---

## 6. Aturan bisnis yang menjadi kontrak bersama

### 6.1 Keranjang dan item

**BR-01 — Satu keranjang per customer.** `carts.user_id` unik. Keranjang kosong memiliki `coffee_shop_id = NULL`; keranjang berisi item wajib memiliki satu toko. Tidak memakai status `active/converted/abandoned` pada rancangan ini.

**BR-02 — Satu toko per cart dan order.** Semua menu dalam cart maupun order harus berasal dari toko yang sama dengan induknya. Pengecekan mencakup tambah item, ubah item, dan checkout.

**BR-03 — Satu baris per menu.** `UNIQUE(cart_id, menu_id)`. Menambah menu yang sama meningkatkan jumlah. Satu catatan berlaku untuk seluruh jumlah menu pada baris tersebut. Kustomisasi berbayar atau beberapa varian dari menu yang sama bukan fitur MVP.

**BR-04 — Catatan bukan perubahan harga.** Catatan seperti “gula sedikit” bersifat permintaan kepada toko. Catatan tidak menghasilkan topping, varian, atau harga baru.

**BR-05 — Batas input.** Jumlah item berupa bilangan bulat 1–99 per menu, tetap tidak boleh melampaui stok saat validasi. Catatan item maksimal 500 karakter. Harga menu lebih besar dari nol; stok tidak boleh negatif.

**BR-06 — Ganti toko harus eksplisit.** Pengosongan cart serta penambahan item toko baru dilakukan atomik setelah pelanggan menyetujui. Menghapus item terakhir mengembalikan `coffee_shop_id` ke `NULL`.

### 6.2 Harga dan histori

**BR-07 — Backend menentukan total.** Frontend hanya mengirim identitas item, jumlah, catatan, versi cart, dan pilihan checkout. Harga, total, role, serta status dari browser tidak dipercaya.

**BR-08 — Harga berubah harus ditinjau ulang.** Cart menyimpan `unit_price_seen` sebagai harga yang terakhir disetujui pelanggan. Jika harga menu kini berbeda saat checkout, tidak ada order atau pengurangan stok. Sistem memperbarui penawaran cart beserta versinya dan meminta pelanggan mengonfirmasi lagi.

**BR-09 — Nilai uang berupa rupiah utuh.** Kolom harga dan jumlah uang menggunakan `BIGINT UNSIGNED`; perhitungan memakai integer, bukan floating point. Tidak ada pecahan rupiah, diskon, pajak, atau service fee pada MVP. `line_total = unit_price × quantity`; `total_amount = jumlah seluruh line_total`.

**BR-10 — Snapshot order tidak berubah.** Item order menyimpan nama menu, harga satuan, jumlah, total baris, dan catatan saat checkout. Order menyimpan nama/alamat toko serta nama/nomor kontak pemesan saat checkout. Perubahan katalog/profil berikutnya tidak menulis ulang snapshot.

### 6.3 Stok dan pesanan paralel

**BR-11 — Stok berarti porsi siap dijual.** Bukan biji kopi, susu, resep, atau stok bahan baku. `stock` menunjukkan jumlah yang masih bisa dipesan, setelah dikurangi pesanan yang sudah terbentuk.

**BR-12 — Cart tidak mencadangkan stok.** Pengecekan ketika menambah ke cart hanya pemeriksaan awal. Checkout selalu memvalidasi ulang toko, kategori aktif, menu aktif/tersedia, harga, dan jumlah.

**BR-13 — Checkout atomik.** Dalam satu transaksi database: kunci cart dan data stok yang relevan, validasi ulang, buat order beserta snapshot dan histori awal, kurangi stok, lalu kosongkan cart. Kesalahan pada salah satu tahap membatalkan seluruh perubahan tersebut. Perubahan harga mengikuti BR-08 sebagai hasil konflik tanpa membuat order.

**BR-14 — Tidak boleh overselling.** Menu terkait dikunci dengan `lockForUpdate()` dalam urutan ID yang konsisten. Dua checkout terhadap stok terakhir tidak boleh sama-sama berhasil. Penguncian cart juga dipakai untuk semua mutasi cart, bukan hanya checkout.

**BR-15 — Pembatalan melepaskan stok tepat sekali.** Hanya order belum dibayar yang boleh dibatalkan pada MVP. Pengubahan status, pelepasan stok, dan histori berada dalam satu transaksi; permintaan ulang tidak menambah stok lagi.

**BR-16 — Pengisian stok menggunakan selisih.** Owner menambah stok lewat tindakan restock dengan jumlah positif dan penguncian baris. Form edit deskripsi/harga tidak boleh menimpa saldo stok menggunakan angka lama. Pengurangan stok karena kerusakan atau koreksi gudang ditunda; owner dapat menonaktifkan menu untuk menghentikan penjualan.

### 6.4 Pembayaran, kedaluwarsa, dan duplikasi request

**BR-17 — Batas bayar 15 menit.** Default ini berlaku bagi kedua metode bayar, dihitung dari checkout oleh waktu server dan disimpan di `expires_at`. Tunai harus dikonfirmasi petugas dalam jangka tersebut; persiapan dimulai setelah lunas. Nilai batas waktu menjadi konfigurasi, bukan angka tersebar di controller.

**BR-18 — Pembayaran dan kedaluwarsa diserialkan.** Tindakan bayar, pembatalan, dan tugas kedaluwarsa mengunci order yang sama. Jika waktu server sudah sama dengan atau melewati `expires_at`, pembayaran ditolak dan order belum dibayar dibatalkan. Order berbayar tidak boleh dibatalkan oleh tugas kedaluwarsa.

**BR-19 — Kedaluwarsa butuh proses berjalan.** Scheduler menjalankan pembersihan setidaknya setiap menit dan melepaskan stok order lewat BR-15. Endpoint pembayaran juga memeriksa tenggat; tidak bergantung pada scheduler untuk menolak pembayaran terlambat. Stok yang tertahan sampai proses pembersihan berjalan tidak dihitung sebagai stok tersedia.

**BR-20 — Checkout berulang tidak membuat order ganda.** Cart mempunyai `version`, bertambah pada setiap perubahan isi/penawaran dan setelah checkout. Order merekam pasangan `cart_id` dan `cart_version` yang digunakan. Constraint unik pada pasangan tersebut menjadi pengaman. Retry versi yang sudah sukses mengembalikan order yang sama setelah verifikasi pemilik; versi yang sudah usang tetapi belum menjadi order ditolak agar cart ditinjau ulang.

**BR-21 — Status bukan field CRUD bebas.** Hanya tindakan domain tertentu yang boleh mengubah status. Mengirim `status=completed` atau `payment_status=paid` ke endpoint edit umum harus ditolak/diabaikan sesuai kontrak request; endpoint transaksi tidak menerima mass assignment status.

### 6.5 Isolasi, arsip, dan operasional

**BR-22 — Role ditambah kepemilikan.** Owner diperiksa terhadap `owner_user_id`; staff terhadap penugasan aktif; customer terhadap pemilik cart/order. `coffee_shop_id` dari URL/body bukan bukti kewenangan.

**BR-23 — Relasi lintas toko ditolak.** Kategori menu harus satu toko dengan menu; menu cart/order harus satu toko dengan induknya. Tidak cukup hanya memeriksa bahwa ID ada di database.

**BR-24 — Arsip, bukan penghapusan histori.** Toko, kategori, menu, serta pengguna dinonaktifkan/diarsipkan pada UI MVP. Tidak ada hard delete order, item order, atau histori. Foreign key melindungi referensi transaksi. Data cart boleh dihapus sebagai data sementara.

**BR-25 — Pisahkan arsip dan ketersediaan.** `menus.is_active = false` berarti diarsipkan dari katalog; `is_available = false` berarti sementara tidak dijual. Keduanya, kategori aktif, stok cukup, dan toko menerima order wajib diperiksa sebelum checkout. Menu yang diarsipkan tetap dapat ditampilkan melalui snapshot pesanan lama.

**BR-26 — Order berbayar menjadi kewajiban toko.** Tidak ada pembatalan/refund setelah bayar pada MVP. Kehabisan operasional ditangani dengan menutup penerimaan baru atau menonaktifkan menu, bukan menghapus order berbayar. Order tidak diambil tetap `ready`; tidak otomatis dianggap selesai.

Kebijakan BR-26 merupakan penyederhanaan proyek akademik. Sebelum penggunaan uang sungguhan, perlu alur penolakan setelah bayar, refund, sengketa, dan rekonsiliasi yang teruji.

---

## 7. Status order dan pembayaran

### 7.1 Status order

```text
pending_payment
  setelah pembayaran sah: queued
  sebelum bayar dibatalkan/kedaluwarsa: cancelled

queued
  setelah petugas mulai membuat: processing

processing
  setelah petugas menandai siap: ready

ready
  setelah diserahkan/disajikan: completed
```

`cancelled` dan `completed` merupakan status akhir. Tidak ada lompatan atau gerak mundur status.

### 7.2 Status pembayaran

- `pending`: belum dicatat lunas.
- `paid`: simulasi QRIS berhasil atau tunai telah dikonfirmasi petugas.
- `voided`: permintaan pembayaran ditutup karena order belum dibayar dibatalkan/kedaluwarsa. Bukan refund dan bukan bukti kegagalan bank.

### 7.3 Matriks transisi yang sah

| Kondisi awal | Tindakan | Pelaku | Kondisi akhir | Dampak stok |
|---|---|---|---|---|
| Belum ada order | Checkout | Customer pemilik cart | `pending_payment` + `pending` | Kurangi saat order terbentuk |
| `pending_payment` + `pending`, sebelum tenggat | Simulasi QRIS berhasil | Customer pemilik order; metode harus QRIS simulasi | `queued` + `paid` | Tidak berubah |
| `pending_payment` + `pending`, sebelum tenggat | Konfirmasi tunai | Owner/staff toko; metode harus cash | `queued` + `paid` | Tidak berubah |
| `pending_payment` + `pending` | Batalkan | Customer pemilik atau owner/staff toko | `cancelled` + `voided` | Kembalikan sekali |
| `pending_payment` + `pending`, tenggat lewat | Kedaluwarsa | Sistem atau pemeriksaan tindakan terhadap order | `cancelled` + `voided` | Kembalikan sekali |
| `queued` + `paid` | Mulai dibuat | Owner/staff toko | `processing` + `paid` | Tidak berubah |
| `processing` + `paid` | Tandai siap | Owner/staff toko | `ready` + `paid` | Tidak berubah |
| `ready` + `paid` | Serahkan/sajikan | Owner/staff toko | `completed` + `paid` | Tidak berubah |

Kombinasi lain, misalnya `processing + pending` atau `cancelled + paid`, tidak sah pada MVP. Pembayaran dan perubahan menjadi `queued` harus terjadi dalam transaksi yang sama.

### 7.4 Simulasi pembayaran yang jujur

- Halaman menampilkan label jelas **“SIMULASI PEMBAYARAN — bukan QRIS nyata.”**
- Tidak menghasilkan QR untuk menerima dana asli dan tidak mengklaim melakukan verifikasi bank.
- Tindakan “Simulasikan pembayaran berhasil” hanya bekerja pada order pelanggan sendiri, metode yang cocok, belum dibayar, dan belum kedaluwarsa.
- Customer tidak bisa menjalankan tindakan konfirmasi tunai. Staff tidak menggunakan tombol simulasi pelanggan untuk melunasi order.
- Setiap sukses menyimpan waktu, pelaku, dan histori. Retry sukses mengembalikan keadaan yang sudah tercatat tanpa menggandakan efek.
- Tabel `payments` terpisah belum diperlukan karena MVP memiliki satu metode dan satu hasil pembayaran per order, tanpa pembayaran sebagian atau ledger percobaan bayar.

---

## 8. Fondasi data untuk CDM/PDM berikutnya

Ini daftar entitas logis dan keputusan struktur, bukan migration final atau diagram PowerDesigner. Model menggunakan sembilan tabel inti dari dokumen lama ditambah satu tabel histori.

| Tabel | Tanggung jawab | Atribut inti/constraint yang perlu dibawa ke desain fisik |
|---|---|---|
| `users` | Identitas dan satu role global | `id`, `name`, `email UNIQUE`, `phone`, password hash, `role`, `is_active`. |
| `coffee_shops` | Profil serta batas tenant | `id`, `owner_user_id`, `name`, `slug UNIQUE`, `description`, `address`, `phone`, `opening_hours_text`, `logo_path`, `status`, `is_accepting_orders DEFAULT false`. |
| `coffee_shop_staff` | Penugasan staff | PK gabungan `(coffee_shop_id, user_id)` dan tambahan `UNIQUE(user_id)` untuk maksimal satu toko pada MVP. Penugasan diputus dengan menghapus baris ini; histori order tetap mereferensikan user, bukan penugasan. Tidak memakai `staff_role` dulu. |
| `categories` | Kelompok menu milik toko | `id`, `coffee_shop_id`, `name`, `description`, `sort_order`, `is_active`; unik `(coffee_shop_id, name)`. |
| `menus` | Menu dan stok porsi tersedia | `id`, `coffee_shop_id`, `category_id`, `name`, `description`, `price`, `stock`, `image_path`, `is_active`, `is_available`. |
| `carts` | Satu cart yang digunakan ulang | `id`, `user_id UNIQUE`, `coffee_shop_id NULL`, `version` bilangan bulat positif. Tidak ada status arsip cart. |
| `cart_items` | Isi cart sementara | `id`, `cart_id`, `menu_id`, `quantity`, `unit_price_seen`, `note`; unik `(cart_id, menu_id)`. |
| `orders` | Dokumen transaksi dan ringkasan pembayaran | `id`, `order_code UNIQUE`, `user_id`, `coffee_shop_id`, `cart_id`, `cart_version`, snapshot toko/kontak, `fulfillment_type`, `table_label NULL`, `status`, `payment_method`, `payment_status`, `total_amount`, `customer_note`, `expires_at`, `paid_at NULL`, `paid_by_user_id NULL`, `completed_at NULL`, `cancelled_at NULL`, `cancellation_reason NULL`, `stock_released_at NULL`; unik `(cart_id, cart_version)`. |
| `order_items` | Snapshot baris transaksi | `id`, `order_id`, `menu_id`, `menu_name_snapshot`, `unit_price`, `quantity`, `line_total`, `note`. |
| `order_status_histories` | Jejak checkout, bayar, batal, dan produksi | `id`, `order_id`, `actor_user_id NULL` untuk sistem, `event`, status order dan pembayaran sebelum/sesudah yang boleh kosong pada penciptaan awal, `reason NULL`, `created_at`. Bersifat append-only. |

Tabel yang dapat berubah memakai `created_at` dan `updated_at`; item order boleh memiliki timestamp, tetapi snapshot tidak diedit setelah checkout. Histori hanya membutuhkan waktu penciptaan.

### 8.1 Kardinalitas yang dipakai

- User owner memiliki nol atau banyak coffee shop; setiap shop memiliki tepat satu owner.
- User staff memiliki nol atau satu penugasan; shop memiliki nol atau banyak staff. Ini menggantikan M:N staff pada dokumen awal untuk MVP.
- Shop memiliki nol atau banyak kategori, menu, dan order.
- Category memiliki nol atau banyak menu; setiap menu wajib berada pada satu kategori dari toko yang sama.
- User customer memiliki nol atau satu cart; cart dibuat saat pertama diperlukan. Setiap cart punya satu customer.
- Cart terikat nol atau satu shop: nol ketika kosong, satu ketika berisi. Cart dapat memiliki nol atau banyak item.
- Customer memiliki nol atau banyak order; setiap order punya tepat satu customer dan satu shop.
- Cart yang digunakan ulang dapat menghasilkan banyak order pada versi berbeda; setiap order MVP berasal dari tepat satu cart/versi.
- Order wajib memiliki satu atau banyak item dan satu atau banyak catatan histori setelah transaksi checkout selesai.
- Menu dapat direferensikan banyak cart item dan order item. Histori order tetap membaca snapshot, bukan harga menu terbaru.
- Setiap order dapat memiliki nol atau satu user pencatat pembayaran; sesudah `paid`, `paid_by_user_id` wajib terisi untuk kedua metode MVP.
- Setiap baris histori punya nol atau satu actor user; kosong hanya untuk tindakan sistem.

### 8.2 Constraint dan indeks penting

- Gunakan foreign key konsisten, tipe PK/FK yang sama, dan `RESTRICT` untuk referensi historis penting.
- `menus(coffee_shop_id, category_id)` harus cocok dengan kategori. Target desain fisik: composite foreign key ke `categories(coffee_shop_id, id)` yang diberi indeks unik pendukung, selain validasi aplikasi.
- Kesamaan toko cart/order dengan setiap menu wajib dipastikan melalui service transaksi dan pengujian integritas; foreign key `menu_id` tunggal tidak membuktikan kesamaan tenant.
- Indeks antrean: `orders(coffee_shop_id, status, created_at)`.
- Indeks riwayat: `orders(user_id, created_at)`.
- Indeks expiry: `orders(status, expires_at)`.
- Indeks laporan: `orders(coffee_shop_id, completed_at)`; filter hanya paid/completed.
- Indeks katalog: `menus(coffee_shop_id, is_active, category_id)`.
- Nilai uang menggunakan `BIGINT UNSIGNED`; jumlah dan stok menggunakan integer nonnegatif. Validasi bisnis tetap diperlukan walaupun tipe database membatasi tanda.
- `order_code` dibuat backend dengan jaminan unik; jangan memakai `MAX(id) + 1`. Mengetahui kode pesanan bukan izin membacanya.

### 8.3 Data yang sengaja tidak dibuat

Tidak ada tabel bahan baku, resep, varian, topping, kurir, pembayaran terpisah, transfer dana, role-permission generik, atau meja. `table_label` hanya identifikasi meja manual, bukan reservasi maupun bukti meja kosong.

---

## 9. Arah arsitektur web dan backend

**Rekomendasi: modular monolith Laravel + MySQL + Blade/Tailwind, satu repository, satu aplikasi.** Backend tetap terstruktur; tidak harus dipisah menjadi REST API dan frontend SPA untuk memenuhi tujuan proyek ini.

### 9.1 Pembagian tanggung jawab kode

- **Routes dan middleware:** autentikasi, akun aktif, role dasar.
- **Policies:** izin terhadap toko, menu, cart, order, dan penugasan tertentu.
- **Form Requests:** format input, batas jumlah, metode bayar, jenis pemenuhan, dan field yang boleh diubah.
- **Controllers:** menerima request dan memanggil service; tidak menampung seluruh logika transaksi.
- **Services:** `CartService`, `CheckoutService`, `PaymentService`, `OrderWorkflowService`, serta `StockService`.
- **Models/Eloquent:** relasi dan query yang tetap memperhatikan tenant.
- **Scheduled command:** kedaluwarsa order belum dibayar melalui service yang sama dengan pembatalan biasa.
- **Blade views:** tampilan publik, customer, owner, staff, dan admin dengan layout bersama.

### 9.2 Tindakan backend utama

- Tambah/ubah/hapus item cart dan ganti toko.
- Checkout berdasarkan versi cart.
- Simulasi bayar QRIS untuk pemilik order.
- Konfirmasi tunai oleh petugas berwenang.
- Batalkan order belum dibayar.
- Mulai produksi, tandai siap, dan tandai selesai.
- Restock menu melalui penambahan saldo atomik.
- Kedaluwarsa order melalui scheduler.

Semua perubahan memakai request non-GET dengan proteksi CSRF. Tidak ada endpoint “edit order” yang membolehkan semua kolom diubah sekaligus.

### 9.3 Perilaku konflik dan keamanan

- Belum login diarahkan ke login; endpoint JSON dapat menggunakan 401.
- Salah role, salah pemilik, atau salah tenant ditolak. Kontrak proyek memakai 403 untuk objek yang ada tetapi bukan hak akun; respons tidak menyertakan detail objek tersebut.
- Input tidak valid menghasilkan validasi 422 pada endpoint JSON atau pesan form yang setara.
- Stok berubah, harga berubah, versi cart usang, atau status sudah berubah menghasilkan konflik 409 pada endpoint JSON atau halaman konflik yang jelas pada alur Blade.
- Klik berulang terhadap tindakan yang sudah sukses mengembalikan hasil semula/tanpa efek ulang. Tindakan berbeda yang bertentangan dengan status saat ini ditolak.
- Upload foto dibatasi tipe JPEG/PNG/WebP dan ukuran maksimal 2 MB, nama file dibuat server, bukan mengikuti path dari pengguna. File non-gambar dan SVG tidak diterima pada MVP.
- Password di-hash; rahasia konfigurasi tidak dimasukkan ke repository. Tampilan catatan memakai escaping standar, bukan HTML mentah.
- Transaksi yang menyentuh beberapa menu mengunci berdasarkan urutan ID yang sama. Penanganan deadlock harus melakukan retry terbatas, bukan mengulang efek di luar transaksi.

### 9.4 Pembaruan layar dan waktu

Antrean staff dan halaman status customer dapat memakai polling setiap 10 detik saat halaman terlihat, dengan tombol refresh sebagai cadangan. WebSocket tidak diperlukan.

Waktu disimpan dalam UTC; tampilan demo memakai `Asia/Jakarta` dan diberi label WIB. Perhitungan expiry selalu memakai waktu server, bukan jam perangkat pelanggan. Dukungan zona waktu per toko merupakan pengembangan berikutnya.

Scheduler merupakan komponen wajib saat demo. Aplikasi belum memenuhi konsep ini jika order kedaluwarsa hanya bisa dilepas melalui perubahan database manual.

---

## 10. Peta halaman dan arahan UI

### 10.1 Publik dan customer

- Beranda: pencarian toko dan kartu coffee shop aktif.
- Detail toko: identitas, alamat, informasi jam, status menerima order, kategori/menu.
- Detail menu: foto, harga, stok, jumlah, catatan.
- Login/register/profil pelanggan.
- Keranjang: toko terkunci, item, harga, total, dialog ganti toko.
- Checkout: pickup/dine-in, label meja bila relevan, kontak, metode bayar, ringkasan.
- Detail order: kode, batas bayar, tindakan bayar/batal jika tersedia, timeline, struk digital.
- Riwayat order: filter sederhana berdasarkan status/tanggal.

### 10.2 Owner

- Pemilihan toko milik sendiri dan ringkasan toko.
- Profil toko dan sakelar penerimaan order.
- Kategori, menu, upload foto, serta restock.
- Penugasan staff.
- Antrean/detail order dengan tindakan operasional.
- Ringkasan penjualan menurut rentang tanggal.

### 10.3 Staff

- Identitas toko penugasan yang selalu terlihat.
- Daftar menunggu pembayaran dan tindakan konfirmasi tunai.
- Antrean `queued`, `processing`, `ready`; detail catatan selalu terlihat.
- Detail order, struk digital, dan riwayat operasional toko.

### 10.4 Admin

- Daftar pengajuan toko dan aktivasi/nonaktif.
- Pengelolaan akun owner/staff/customer dan status aktif akun.
- Ringkasan jumlah toko serta akun; tidak perlu dashboard keuangan seluruh platform.

### 10.5 Prinsip tampilan

Pertahankan warna dasar dokumen awal: `#6F4E37`, `#C68B59`, `#F8F3EF`, `#FFFDFB`. Gunakan satu font, misalnya Plus Jakarta Sans.

Status tidak boleh hanya dibedakan lewat warna; sertakan teks. Nama toko harus tetap terlihat di cart, checkout, dan area operasional. Tombol yang sedang diproses dinonaktifkan untuk mengurangi klik ganda, tetapi backend tetap wajib melindunginya.

Halaman customer ditargetkan dapat digunakan pada lebar 360 px tanpa scroll horizontal. Tabel pengelolaan dapat berubah menjadi kartu atau menyediakan area scroll tersendiri. Form menampilkan error dekat field; kondisi stok habis, toko tutup, belum ada item, dan belum ada pesanan mempunyai tampilan khusus.

---

## 11. Ringkasan penjualan yang tidak menyesatkan

**Omzet pesanan selesai** adalah jumlah `total_amount` dari order dengan `status = completed` dan `payment_status = paid`, menggunakan `completed_at` sebagai dasar rentang tanggal.

- Order belum dibayar, dibatalkan, dan masih antre/diproses/siap tidak masuk omzet selesai.
- Menu terjual dihitung dari `order_items` pada order yang memenuhi syarat tersebut.
- Periode tanggal pada UI menggunakan WIB dan dikonversi ke UTC untuk query.
- Tampilan menyebut “omzet pesanan selesai”, bukan laba. Sistem tidak memiliki biaya bahan baku sehingga tidak dapat menghitung laba.
- Pembayaran QRIS tetap diberi konteks simulasi. Laporan demo bukan bukti penerimaan dana sungguhan.

---

## 12. Pembagian kerja untuk tiga anggota

Pembagian berdasarkan modul, bukan satu orang mengerjakan seluruh frontend dan satu orang seluruh backend. Setiap anggota menangani tampilan, controller, validasi, serta pengujian modulnya.

| Anggota | Tanggung jawab utama | Hasil yang harus bisa didemokan |
|---|---|---|
| 1 — Akun dan customer | Login/register/profil, layout bersama, katalog publik, cart dan versi cart, checkout UI, detail/riwayat order customer. | Pelanggan dapat memilih toko dan mengirim checkout melalui service anggota 3; akses cart/order orang lain ditolak. |
| 2 — Tenant dan katalog | Onboarding/admin, model kepemilikan, Policies bersama, profil toko, kategori/menu/foto, staff assignment, ringkasan penjualan. | Dua toko punya data terpisah; owner hanya dapat mengelola tokonya; laporan membaca kontrak order anggota 3. |
| 3 — Mesin transaksi dan operasional | CheckoutService, pembayaran, stok/restock service, workflow order, expiry scheduler, histori, antrean staff dan struk. | Alur order berjalan ujung ke ujung; stok paralel, retry, pembayaran terlambat, dan transisi terlarang teruji. |

Anggota 3 memiliki bagian paling berisiko. Anggota 1 membantu layout/tampilan staff setelah alur customer tersambung; anggota 2 membantu laporan dan pengujian izin. Pengujian integrasi dilakukan bersama, bukan diserahkan kepada anggota 3 di akhir.

### 12.1 Kontrak yang harus disepakati sebelum coding paralel

- Nama tabel, foreign key, nama relasi Eloquent, status, role, satuan uang, dan arti `stock`.
- Bentuk input/output CartService dan CheckoutService, termasuk `cart_version` dan konflik harga.
- Satu pintu perubahan status dan stok: service domain, bukan query update di halaman berbeda.
- Konvensi route, layout, pesan validasi, branch, dan review perubahan.
- Satu penanggung jawab integrasi migration; perubahan schema lintas modul dibicarakan sebelum merge.

---

## 13. Urutan pengerjaan dengan titik selesai yang jelas

### Tahap A — Kontrak dan fondasi

Sepakati dokumen ini, buat ERD/PDM, layout/wireframe sederhana, autentikasi, role, Policies dasar, migration, dan seed dua toko yang berbeda kepemilikan.

**Selesai jika:** semua role bisa login dan percobaan akses toko orang lain ditolak.

### Tahap B — Katalog dan keranjang

Bangun admin/owner master data, katalog publik, penugasan staff, cart satu toko, serta penanganan toko tutup dan menu habis.

**Selesai jika:** customer bisa menyusun cart satu toko dan pergantian toko tidak pernah diam-diam menghapus cart.

### Tahap C — Satu alur transaksi penuh

Bangun checkout atomik, snapshot, pembayaran simulasi/tunai, antrean, dan status sampai selesai. Kerjakan satu alur pickup dahulu, kemudian dine-in dengan label meja.

**Selesai jika:** order customer terlihat di toko yang benar dan bisa diselesaikan tanpa edit database manual.

### Tahap D — Ketahanan dan laporan

Bangun expiry, cancellation, pengaman retry, pengujian stok bersamaan, histori status, laporan, dan penyempurnaan UI.

**Selesai jika:** semua skenario penerimaan pada bagian 14 lolos dan hasil laporan cocok dengan order uji yang memenuhi syarat.

Tidak menetapkan durasi kalender sebelum tenggat, kemampuan anggota, dan jam kerja tim diketahui.

---

## 14. Skenario penerimaan dan keterlacakan

Skenario berikut merupakan tes yang harus dibuat saat implementasi. Dokumen ini tidak mengklaim tes aplikasi sudah dijalankan.

| ID | Skenario dan hasil yang wajib | Aturan terkait |
|---|---|---|
| AT-01 | Registrasi dengan input `role=admin` tetap membuat customer atau menolak field tersebut; akun nonaktif tidak bisa menggunakan sesi lama. | Bagian 4, BR-22 |
| AT-02 | Owner toko A tidak dapat membaca/mengubah menu toko B; staff tidak mendapat akses hanya dengan mengganti ID toko di URL. | BR-22, BR-23 |
| AT-03 | Customer tidak dapat membaca cart atau order customer lain, termasuk dengan kode order yang diketahui. | BR-20, BR-22 |
| AT-04 | Menambah menu toko B ketika cart toko A terisi meminta konfirmasi; membatalkan dialog mempertahankan isi A. | BR-01–BR-06 |
| AT-05 | Setelah item terakhir dihapus, cart kosong dan `coffee_shop_id` kembali `NULL`; customer tidak memiliki cart persisten kedua. | BR-01, BR-06 |
| AT-06 | Kategori toko B tidak dapat dipakai menu toko A; menu nonaktif/tidak tersedia atau kategori nonaktif ditolak saat checkout. | BR-12, BR-23, BR-25 |
| AT-07 | Harga berubah setelah add-to-cart menghasilkan penawaran baru tanpa order dan tanpa stok berkurang; pelanggan perlu konfirmasi ulang. | BR-07–BR-09 |
| AT-08 | Total dari browser dimanipulasi, tetapi order tetap memakai jumlah harga backend. Perubahan nama/harga menu setelahnya tidak mengubah histori. | BR-07, BR-09, BR-10 |
| AT-09 | Dua checkout paralel memperebutkan satu porsi terakhir; hanya satu order terbentuk dan stok akhir tidak negatif. | BR-11–BR-14 |
| AT-10 | Dua request checkout paralel dengan cart/versi sama mengembalikan order yang sama; hanya satu order dan satu pengurangan stok. | BR-13, BR-20 |
| AT-11 | Checkout bersamaan dengan perubahan cart tidak memakai isi campuran; versi usang ditolak atau retry sukses lama dikembalikan. | BR-14, BR-20 |
| AT-12 | Checkout gagal di tengah penyimpanan item menyebabkan seluruh order, histori, stok, dan pengosongan cart di-rollback. | BR-13 |
| AT-13 | Order tidak masuk produksi sebelum dibayar; customer tidak bisa konfirmasi cash dan staff tidak bisa bayar lewat tombol QRIS milik customer. | BR-17, BR-21, Bagian 7 |
| AT-14 | Bayar dan expiry bersamaan hanya menghasilkan satu hasil sah; pembayaran setelah atau tepat tenggat ditolak. | BR-18, BR-19 |
| AT-15 | Cancel atau expiry yang dipanggil dua kali hanya melepaskan stok sekali; order paid tidak terpengaruh proses expiry berikutnya. | BR-15, BR-18, BR-19 |
| AT-16 | Lompatan `queued` langsung ke `completed`, pembatalan order paid, serta edit status melalui endpoint umum ditolak. | BR-21, BR-26, Bagian 7 |
| AT-17 | Toko berhenti menerima order: checkout baru ditolak, order lama tetap terbaca dan order berbayar tetap bisa diselesaikan. | Bagian 5.3, BR-26 |
| AT-18 | Order dine-in tanpa label meja ditolak; order pickup tidak menyimpan label meja yang tersisa dari form sebelumnya. | Bagian 5.1 |
| AT-19 | Restock bersamaan dengan checkout tidak menghilangkan salah satu perubahan saldo. | BR-14, BR-16 |
| AT-20 | Omzet selesai hanya menjumlahkan paid/completed berdasarkan `completed_at`; pending, cancelled, dan ready tidak ikut. | Bagian 11 |
| AT-21 | Akun staff yang sudah ditugaskan tidak bisa ditarik owner toko lain; pelepasan assignment tidak menghapus histori pelakunya. | Bagian 4.3, Bagian 8 |
| AT-22 | Upload file non-gambar ditolak; catatan dengan markup tampil sebagai teks, bukan script yang berjalan. | Bagian 9.3 |
| AT-23 | Setiap checkout, pembayaran, pembatalan, expiry, dan perubahan produksi menambah histori dengan actor/waktu yang benar dalam transaksi yang sama. | BR-13, BR-15, Bagian 7–8 |
| AT-24 | Scheduler yang benar-benar berjalan membatalkan order belum dibayar yang melewati tenggat dan mengembalikan stok tanpa perubahan database manual. | BR-17–BR-19 |

### 14.1 Data demo minimum

Gunakan admin, dua owner dengan toko berbeda, staff pada setiap toko, dua customer, kategori/menu yang berbeda antartoko, menu stok satu, menu habis, serta toko yang belum/tidak menerima order. Semua kredensial demo disiapkan melalui setup lokal, bukan akun produksi.

Demo wajib menunjukkan alur normal sekaligus penolakan akses lintas toko, konflik stok, retry checkout, dan expiry. Jangan hanya mendemokan CRUD.

---

## 15. Keputusan akhir yang menjadi dasar implementasi

1. Nusantara Brew adalah platform multi-toko dengan transaksi satu toko, bukan satu checkout multi-vendor.
2. MVP mempertahankan customer, owner, admin, serta satu staf operasional; POS dan pemisahan kasir/barista ditunda.
3. Pickup dan dine-in didukung tanpa delivery, jadwal pickup, atau reservasi meja.
4. Pembayaran terbatas pada QRIS simulasi dan pencatatan tunai; tidak ada transaksi gateway nyata atau refund.
5. Stok dicadangkan saat checkout, bukan saat masuk cart atau ketika barista mulai bekerja.
6. Pembayaran harus selesai dalam batas waktu; hanya order unpaid yang bisa dibatalkan dan melepaskan stok.
7. Order berbayar melewati antrean, diproses, siap, lalu selesai tanpa lompatan status.
8. Harga dan histori berasal dari backend serta snapshot, bukan input harga browser.
9. Laravel monolith dengan Blade/Tailwind dan MySQL cukup untuk seluruh MVP.
10. Pekerjaan berikutnya setelah tim menyepakati konsep: use case ringkas, ERD/CDM-PDM, wireframe, lalu kontrak route/service dan implementasi.

**Ukuran keberhasilan:** satu pelanggan dapat memesan dari toko pilihannya sampai selesai, toko lain tidak bisa melihat/mengubah transaksi tersebut, dan stok serta total tetap benar ketika request bersamaan atau diulang.
