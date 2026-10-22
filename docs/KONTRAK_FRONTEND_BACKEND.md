# Kontrak Frontend–Backend: Nusantara Brew

Dokumen ini mengikat pekerjaan frontend (Blade) dan backend (Laravel + MySQL).
Frontend sudah selesai dan memakai URL serta nama route final. Backend tidak
mengubah nama route, nama variabel view, atau nama field form — cukup mengganti
sumber data dan menambahkan middleware.

Status dokumen: acuan kerja tim, bukan dokumentasi pengguna.

---

## 1. Prinsip Pembagian

| Bagian | Pemilik | Isi |
|---|---|---|
| `resources/views/**`, `public/assets/**` | Frontend | Markup, CSS, JS tampilan, state UI |
| `routes/web.php` (nama route) | Disepakati bersama | Frontend menetapkan nama; backend menambah middleware |
| `app/Http/Controllers/PageController.php` | Backend (menggantikan isi) | Sekarang berisi data contoh |
| `database/**`, `app/Models/**`, `app/Policies/**` | Backend | Skema, relasi, aturan akses |
| `app/Http/Controllers/AuthController.php` | Backend | Login, register, logout |

Aturan keras:

1. Backend tidak mengganti nama route yang sudah ada di §2.
2. Backend tidak mengganti nama variabel yang dikirim ke view (§3).
3. Frontend tidak menambah query database atau logika transaksi di Blade.
4. Semua nominal uang dikirim sebagai integer rupiah, bukan string berformat.

---

## 2. Daftar Route

### 2.1 Sudah ada (dibuat frontend)

| Method | URI | Nama route | Controller saat ini |
|---|---|---|---|
| GET | `/` | `shops.index` | `ShopController@index` |
| GET | `/toko/{slug}` | `shops.show` | `ShopController@show` |
| GET | `/masuk` | `login` | closure `view('auth.login')` |
| GET | `/daftar` | `register` | closure `view('auth.register')` |
| GET | `/keranjang` | `cart.index` | `Route::view` |
| GET | `/pesanan` | `orders.show` | `PageController@order` |
| GET | `/pesanan/struk` | `orders.receipt` | `PageController@receipt` |
| GET | `/riwayat` | `orders.history` | `PageController@customerOrders` |
| GET | `/owner` | `owner.dashboard` | `PageController@ownerDashboard` |
| GET | `/owner/pesanan` | `owner.orders` | `PageController@ownerOrders` |
| GET | `/owner/menu` | `owner.menu` | `PageController@ownerMenu` |
| GET | `/owner/kategori` | `owner.categories` | `PageController@ownerCategories` |
| GET | `/owner/toko` | `owner.shop` | `PageController@ownerShop` |
| GET | `/owner/staf` | `owner.staff` | `PageController@ownerStaff` |
| GET | `/owner/laporan` | `owner.reports` | `PageController@ownerReports` |
| GET | `/staf` | `staff.queue` | `PageController@staffDashboard` |
| GET | `/staf/riwayat` | `staff.history` | `PageController@staffHistory` |
| GET | `/kasir` | `staff.pos` | `PageController@staffPos` |
| GET | `/admin` | `admin.dashboard` | `PageController@adminDashboard` |
| GET | `/admin/toko` | `admin.shops` | `PageController@adminShops` |
| GET | `/admin/pengguna` | `admin.users` | `PageController@adminUsers` |

Dua route data contoh, hanya untuk pemeriksaan tampilan, harus 404 di produksi:

| Method | URI | Nama route |
|---|---|---|
| GET | `/pratinjau/keranjang` | `preview.cart` |
| GET | `/pratinjau/checkout` | `preview.checkout` |

### 2.2 Harus ditambahkan backend

| Method | URI | Nama route | Fungsi |
|---|---|---|---|
| POST | `/masuk` | `login.attempt` | Proses login |
| POST | `/daftar` | `register.store` | Registrasi pelanggan |
| POST | `/keluar` | `logout` | Logout (CSRF, method POST) |
| POST | `/keranjang/item` | `cart.items.store` | Tambah item |
| PATCH | `/keranjang/item/{cartItem}` | `cart.items.update` | Ubah jumlah/catatan |
| DELETE | `/keranjang/item/{cartItem}` | `cart.items.destroy` | Hapus item |
| POST | `/keranjang/ganti-toko` | `cart.switch-shop` | Kosongkan keranjang lalu ganti kedai |
| POST | `/checkout` | `checkout.store` | Buat pesanan |
| POST | `/pesanan/{order}/bayar` | `orders.pay` | Simulasi pembayaran |
| POST | `/pesanan/{order}/batal` | `orders.cancel` | Batalkan pesanan |
| POST | `/owner/menu` | `owner.menu.store` | Tambah menu |
| PUT | `/owner/menu/{menu}` | `owner.menu.update` | Ubah menu |
| POST | `/owner/menu/{menu}/stok` | `owner.menu.restock` | Tambah stok |
| POST | `/owner/kategori` | `owner.categories.store` | Tambah kategori |
| PUT | `/owner/kategori/{category}` | `owner.categories.update` | Ubah kategori |
| PUT | `/owner/toko` | `owner.shop.update` | Ubah profil kedai |
| PUT | `/owner/toko/penerimaan` | `owner.shop.acceptance` | Buka/tutup penerimaan |
| POST | `/owner/staf` | `owner.staff.assign` | Tugaskan staf |
| DELETE | `/owner/staf/{user}` | `owner.staff.remove` | Lepas staf |
| PATCH | `/pesanan/{order}/status` | `orders.status` | Ubah status pesanan |
| PATCH | `/pesanan/{order}/pembayaran` | `orders.payment` | Konfirmasi pembayaran tunai |
| POST | `/kasir` | `pos.store` | Buat pesanan dari kasir |
| PATCH | `/admin/toko/{coffeeShop}` | `admin.shops.status` | Aktifkan/nonaktifkan kedai |
| POST | `/admin/pengguna` | `admin.users.store` | Buat akun owner/staf |
| PATCH | `/admin/pengguna/{user}` | `admin.users.status` | Aktif/nonaktifkan akun |

Nama route di atas adalah usulan; yang wajib dipertahankan hanya nama pada §2.1.

---

## 3. Kontrak Variabel per View

Semua tanggal yang dikirim ke view berupa `Carbon` agar Blade bisa memanggil
`timezone('Asia/Jakarta')` dan `translatedFormat()`.

### 3.1 `home.blade.php`

| Variabel | Tipe | Field wajib |
|---|---|---|
| `$shops` | Collection | `name`, `slug`, `address`, `description`, `is_accepting_orders` (bool) |
| `$query` | string | Kata kunci pencarian, boleh kosong |

### 3.2 `shops/show.blade.php`

| Variabel | Tipe | Field wajib |
|---|---|---|
| `$shop` | object | `name`, `address`, `description`, `is_accepting_orders` |
| `$categories` | Collection ber-key `id` | `id`, `name` |
| `$groupedMenus` | array `[category_id => Collection]` | — |
| `$groupedMenus[].menu` | object | `id`, `name`, `description`, `price`, `stock`, `is_available` |

`$shop` harus 404 jika slug tidak ada atau kedai tidak aktif.

### 3.3 `cart/index.blade.php`

| Variabel | Tipe | Field wajib |
|---|---|---|
| `$cart` | object\|null | `shop_name`, `items`, `total_amount` |
| `$cart->items[]` | array/object | `name`, `unit_price`, `quantity`, `note` |

`$cart = null` menampilkan keadaan kosong.

### 3.4 `checkout/index.blade.php`

Memakai `$cart` yang sama dengan §3.3.

### 3.5 `orders/show.blade.php`

| Field | Tipe | Catatan |
|---|---|---|
| `order_code` | string | contoh `NB-20261022-104` |
| `shop_name_snapshot` | string | |
| `status` | enum | lihat §5 |
| `payment_status` | enum | lihat §5 |
| `payment_method` | enum | `qris_simulation`, `transfer_simulation`, `cash` |
| `fulfillment_type` | enum | `pickup`, `dine_in` |
| `table_label` | string\|null | wajib jika `dine_in` |
| `customer_name_snapshot` | string | |
| `customer_phone_snapshot` | string | |
| `customer_note` | string\|null | |
| `expires_at` | Carbon\|null | dipakai hitung mundur 15 menit |
| `created_at` | Carbon | |
| `total_amount` | int | rupiah |
| `items[]` | array | `name`, `unit_price`, `quantity`, `note` |

### 3.6 `orders/receipt.blade.php`

Sama dengan §3.5, ditambah `shop_address_snapshot`.

### 3.7 `orders/index.blade.php`

| Variabel | Tipe | Field wajib |
|---|---|---|
| `$orders` | Collection | `order_code`, `shop_name_snapshot`, `created_at`, `status`, `status_label`, `badge_class`, `total_amount`, `items[]` |

`badge_class` salah satu: `badge-success`, `badge-warning`, `badge-danger`, `badge-neutral`.

### 3.8 `owner/dashboard.blade.php`

| Variabel | Field wajib |
|---|---|
| `$shop` | `name`, `is_accepting_orders` |
| `$summary` | `completed_orders`, `revenue`, `awaiting_payment`, `in_progress` (semua int) |
| `$orders` | `order_code`, `status`, `status_label`, `created_at`, `fulfillment_type`, `total_amount`, `items[]` |

### 3.9 `owner/orders.blade.php`

| Variabel | Field wajib |
|---|---|
| `$shop` | `name`, `is_accepting_orders` |
| `$tabs` | array: key → `label`, `orders`, `action`, `empty` |

`action` adalah label tombol, misal `Konfirmasi tunai`, `Mulai dibuat`, `Tandai selesai`.

### 3.10 `owner/menu.blade.php`

| Variabel | Field wajib |
|---|---|
| `$shop` | `name` |
| `$categories` | `id`, `name` |
| `$menus` | `id`, `name`, `description`, `category_id`, `category_name`, `price`, `stock`, `is_available` |
| `$query`, `$selectedCategory` | string\|null |

### 3.11 `owner/categories.blade.php`

`$categories[]`: `name`, `description`, `menu_count`, `sort_order`, `is_active`.

### 3.12 `owner/shop.blade.php`

`$shop`: `name`, `address`, `description`, `opening_hours_text`, `status`, `is_accepting_orders`, `updated_at` (string siap tampil).

### 3.13 `owner/staff.blade.php`

`$staff[]`: `name`, `email`, `assigned_at` (string), `is_active`.
`$candidates[]`: `name`, `email`.

### 3.14 `owner/reports.blade.php`

`$report`: `orders`, `revenue`, `items` (int), `rows[]` dengan `name`, `quantity`, `amount`.
`$from`, `$to`: string `Y-m-d`.

### 3.15 `staff/dashboard.blade.php`

Sama dengan §3.9.

### 3.16 `staff/history.blade.php`

`$orders[]`: `order_code`, `created_at`, `fulfillment_type`, `status_label`, `total_amount`.

### 3.17 `staff/pos.blade.php`

`$categories[]`: `name`, `menus[]` dengan `name`, `price`, `stock`.

### 3.18 `admin/dashboard.blade.php`

`$summary`: `active_shops`, `pending_shops`, `users`.
`$pending[]`: `name`, `address`, `submitted_at`.

### 3.19 `admin/shops.blade.php`

`$shops[]`: `name`, `address`, `owner_name`, `submitted_at`, `status`, `status_label`.

### 3.20 `admin/users.blade.php`

`$users[]`: `name`, `email`, `role_label`, `is_active`.

### 3.21 Layout dashboard

| Variabel | Isi |
|---|---|
| `$account` | `name`, `role_label` |
| `$nav` | array `['label' => string, 'route' => string\|null]` |

`route = null` merender item nonaktif bertanda "Segera".

---

## 4. Field Form yang Harus Diterima Backend

| Form | Field |
|---|---|
| Login | `email`, `password` |
| Register | `name`, `email`, `phone`, `password`, `password_confirmation` |
| Checkout | `fulfillment_type`, `table_label`, `customer_name`, `customer_phone`, `customer_note`, `payment_method` |
| Tambah stok | `amount` (integer ≥ 1) |
| Tugaskan staf | `email` (harus persis sama dengan akun ber-role `staff`) |
| Buat akun (admin) | `name`, `email`, `role` (`owner`\|`staff`) |
| Profil kedai | `name`, `address`, `opening_hours_text`, `description` |
| POS kasir | `customer_name`, `fulfillment_type`, `payment_method`, item keranjang |

Semua form POST wajib memakai `@csrf` (sudah terpasang).

---

## 5. Enum dan Status

### 5.1 `orders.status`

`pending_payment` → `queued` → `processing` → `ready` → `completed`, atau `cancelled`.

Aturan transisi:

| Dari | Ke | Siapa |
|---|---|---|
| `pending_payment` | `queued` | sistem saat pembayaran lunas |
| `pending_payment` | `cancelled` | pelanggan, atau sistem saat kedaluwarsa |
| `queued` | `processing` | barista |
| `processing` | `ready` | barista |
| `ready` | `completed` | barista/kasir |
| `queued`/`processing` | `cancelled` | kasir/owner, stok dikembalikan |

### 5.2 `orders.payment_status`

`pending` → `paid`, atau `failed` / `refunded`. MVP tidak memakai `refunded`.

### 5.3 Label tampilan

| Status | Label | Kelas badge |
|---|---|---|
| `pending_payment` | Menunggu pembayaran | `badge-warning` |
| `queued` | Dalam antrean | `badge-info` |
| `processing` | Sedang dibuat | `badge-info` |
| `ready` | Siap diambil | `badge-info` |
| `completed` | Selesai | `badge-success` |
| `cancelled` | Dibatalkan | `badge-danger` |

---

## 6. Middleware dan Otorisasi

| Grup route | Middleware wajib |
|---|---|
| `/owner/*` | `auth`, `role:owner`, cakupan `coffee_shop_id` milik owner |
| `/staf/*`, `/kasir` | `auth`, `role:staff`, cakupan kedai dari `coffee_shop_staff` |
| `/admin/*` | `auth`, `role:admin` |
| `/pesanan*`, `/riwayat`, `/keranjang`, `/checkout` | `auth`, hanya data milik user |

Aturan yang tidak boleh dilanggar:

1. Staf hanya melihat pesanan kedainya. Percobaan lintas kedai harus `403`.
2. Owner hanya mengubah menu/kategori dengan `coffee_shop_id` miliknya.
3. `menus.coffee_shop_id` wajib sama dengan `categories.coffee_shop_id` saat simpan.
4. Pesanan hanya bisa diubah statusnya oleh staf kedai terkait.
5. Akun nonaktif (`is_active = false`) ditolak saat login.

---

## 7. Aturan Tampilan yang Harus Dipertahankan

1. Nominal rupiah memakai `number_format($value, 0, ',', '.')`, tanpa desimal.
2. Label **SIMULASI PEMBAYARAN** wajib tetap tampil di halaman pesanan dan struk.
3. Tidak ada catatan demo, nama AI, atau tombol ganti peran di UI.
4. Tombol yang belum punya endpoint tetap `disabled`; jangan dikirim ke `#` tanpa handler.
5. Stok nol selalu tampil sebagai label **Habis** dan tidak bisa dipesan.
6. Tanggal tampil dalam WIB.

---

## 8. Langkah Serah Terima

1. Backend membuat migration dan model sesuai konsep, lalu `php artisan migrate --seed`.
2. Backend mengganti isi `PageController` dengan query Eloquent; nama method dan nama route tetap.
3. Backend menambahkan route POST/PATCH dari §2.2 dan `AuthController`.
4. Backend memasang middleware di §6, lalu menguji akses lintas kedai menghasilkan `403`.
5. Frontend menghapus dua route `/pratinjau/*` setelah keranjang dan checkout tersambung.
6. Kedua pihak menjalankan `php artisan test` sebelum merge.

Kriteria selesai:

- Semua route §2.1 mengembalikan `200` dengan data dari database.
- Login sebagai owner A tidak bisa membuka data kedai B.
- Checkout mengurangi stok tepat sekali dan menulis `order_items` snapshot.
- Pesanan belum dibayar kedaluwarsa dalam 15 menit dan stok kembali.
- `/pratinjau/*` menghasilkan `404` di `APP_ENV=production`.
