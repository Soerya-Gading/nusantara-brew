<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute publik
|--------------------------------------------------------------------------
*/
Route::get('/', [ShopController::class, 'index'])->name('shops.index');
Route::get('/toko/{slug}', [ShopController::class, 'show'])->name('shops.show');

// Halaman autentikasi (GET saja — POST ditambahkan bersama AuthController).
Route::get('/masuk', fn () => view('auth.login'))->name('login');
Route::get('/daftar', fn () => view('auth.register'))->name('register');

/*
|--------------------------------------------------------------------------
| Pelanggan
|--------------------------------------------------------------------------
| Keranjang dan checkout butuh isi transaksi nyata, jadi masih memakai data
| contoh di /pratinjau agar tidak menyamar sebagai fitur yang sudah berjalan.
| Halaman lain sudah memakai URL final.
*/
Route::view('/keranjang', 'cart.index')->name('cart.index');

Route::get('/pratinjau/keranjang', [PageController::class, 'cart'])->name('preview.cart');
Route::get('/pratinjau/checkout', [PageController::class, 'checkout'])->name('preview.checkout');

Route::get('/pesanan', [PageController::class, 'order'])->name('orders.show');
Route::get('/pesanan/struk', [PageController::class, 'receipt'])->name('orders.receipt');
Route::get('/riwayat', [PageController::class, 'customerOrders'])->name('orders.history');

/*
|--------------------------------------------------------------------------
| Panel pemilik kedai
|--------------------------------------------------------------------------
| Backend menambahkan middleware role:owner + cakupan coffee_shop_id.
*/
Route::get('/owner', [PageController::class, 'ownerDashboard'])->name('owner.dashboard');
Route::get('/owner/pesanan', [PageController::class, 'ownerOrders'])->name('owner.orders');
Route::get('/owner/menu', [PageController::class, 'ownerMenu'])->name('owner.menu');
Route::get('/owner/kategori', [PageController::class, 'ownerCategories'])->name('owner.categories');
Route::get('/owner/toko', [PageController::class, 'ownerShop'])->name('owner.shop');
Route::get('/owner/staf', [PageController::class, 'ownerStaff'])->name('owner.staff');
Route::get('/owner/laporan', [PageController::class, 'ownerReports'])->name('owner.reports');

/*
|--------------------------------------------------------------------------
| Panel staf (barista & kasir)
|--------------------------------------------------------------------------
| Backend menambahkan middleware role:staff + cakupan coffee_shop_id.
*/
Route::get('/staf', [PageController::class, 'staffDashboard'])->name('staff.queue');
Route::get('/staf/riwayat', [PageController::class, 'staffHistory'])->name('staff.history');
Route::get('/kasir', [PageController::class, 'staffPos'])->name('staff.pos');

/*
|--------------------------------------------------------------------------
| Panel admin platform
|--------------------------------------------------------------------------
| Backend menambahkan middleware role:admin.
*/
Route::get('/admin', [PageController::class, 'adminDashboard'])->name('admin.dashboard');
Route::get('/admin/toko', [PageController::class, 'adminShops'])->name('admin.shops');
Route::get('/admin/pengguna', [PageController::class, 'adminUsers'])->name('admin.users');

/*
|--------------------------------------------------------------------------
| Alat pengembangan
|--------------------------------------------------------------------------
| Indeks semua halaman untuk pemeriksaan tampilan. Hanya lokal/pengujian.
*/
Route::get('/peta', [PageController::class, 'sitemap'])->name('dev.sitemap');
