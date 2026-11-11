<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute publik
|--------------------------------------------------------------------------
*/
Route::get('/', [ShopController::class, 'index'])->name('shops.index');
Route::get('/toko/{slug}', [ShopController::class, 'show'])->name('shops.show');

// Halaman autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Pelanggan
|--------------------------------------------------------------------------
*/
Route::get('/keranjang', [PageController::class, 'cart'])->name('cart.index');
Route::post('/keranjang/item', [CartController::class, 'addItem'])->middleware('auth')->name('cart.items.store');
Route::patch('/keranjang/item/{cartItem}', [CartController::class, 'updateItem'])->middleware('auth')->name('cart.items.update');
Route::delete('/keranjang/item/{cartItem}', [CartController::class, 'removeItem'])->middleware('auth')->name('cart.items.destroy');
Route::post('/keranjang/ganti-toko', [CartController::class, 'switchShop'])->middleware('auth')->name('cart.switch-shop');

Route::get('/checkout', [PageController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('auth')->name('checkout.store');

Route::get('/pratinjau/keranjang', [PageController::class, 'cart'])->name('preview.cart');
Route::get('/pratinjau/checkout', [PageController::class, 'checkout'])->name('preview.checkout');

Route::get('/pesanan', [PageController::class, 'order'])->name('orders.show');
Route::get('/pesanan/struk', [PageController::class, 'receipt'])->name('orders.receipt');
Route::get('/riwayat', [PageController::class, 'customerOrders'])->name('orders.history');

Route::post('/pesanan/{order}/bayar', [OrderController::class, 'pay'])->middleware('auth')->name('orders.pay');
Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->middleware('auth')->name('orders.cancel');

Route::patch('/pesanan/{order}/status', [OwnerController::class, 'updateOrderStatus'])->middleware('auth')->name('orders.status');
Route::patch('/pesanan/{order}/pembayaran', [OwnerController::class, 'confirmPayment'])->middleware('auth')->name('orders.payment');

/*
|--------------------------------------------------------------------------
| Panel pemilik kedai
|--------------------------------------------------------------------------
*/
Route::get('/owner', [PageController::class, 'ownerDashboard'])->name('owner.dashboard');
Route::get('/owner/pesanan', [PageController::class, 'ownerOrders'])->name('owner.orders');
Route::get('/owner/menu', [PageController::class, 'ownerMenu'])->name('owner.menu');
Route::post('/owner/menu', [OwnerController::class, 'storeMenu'])->middleware(['auth', 'role:owner'])->name('owner.menu.store');
Route::put('/owner/menu/{menu}', [OwnerController::class, 'updateMenu'])->middleware(['auth', 'role:owner'])->name('owner.menu.update');
Route::post('/owner/menu/{menu}/stok', [OwnerController::class, 'restockMenu'])->middleware(['auth', 'role:owner'])->name('owner.menu.restock');

Route::get('/owner/kategori', [PageController::class, 'ownerCategories'])->name('owner.categories');
Route::post('/owner/kategori', [OwnerController::class, 'storeCategory'])->middleware(['auth', 'role:owner'])->name('owner.categories.store');
Route::put('/owner/kategori/{category}', [OwnerController::class, 'updateCategory'])->middleware(['auth', 'role:owner'])->name('owner.categories.update');

Route::get('/owner/toko', [PageController::class, 'ownerShop'])->name('owner.shop');
Route::put('/owner/toko', [OwnerController::class, 'updateShop'])->middleware(['auth', 'role:owner'])->name('owner.shop.update');
Route::put('/owner/toko/penerimaan', [OwnerController::class, 'updateAcceptance'])->middleware(['auth', 'role:owner'])->name('owner.shop.acceptance');

Route::get('/owner/staf', [PageController::class, 'ownerStaff'])->name('owner.staff');
Route::post('/owner/staf', [OwnerController::class, 'assignStaff'])->middleware(['auth', 'role:owner'])->name('owner.staff.assign');
Route::delete('/owner/staf/{user}', [OwnerController::class, 'removeStaff'])->middleware(['auth', 'role:owner'])->name('owner.staff.remove');

Route::get('/owner/laporan', [PageController::class, 'ownerReports'])->name('owner.reports');

/*
|--------------------------------------------------------------------------
| Panel staf (barista & kasir)
|--------------------------------------------------------------------------
*/
Route::get('/staf', [PageController::class, 'staffDashboard'])->name('staff.queue');
Route::get('/staf/riwayat', [PageController::class, 'staffHistory'])->name('staff.history');
Route::get('/kasir', [PageController::class, 'staffPos'])->name('staff.pos');
Route::post('/kasir', [StaffController::class, 'pos'])->middleware(['auth', 'role:staff'])->name('pos.store');

/*
|--------------------------------------------------------------------------
| Panel admin platform
|--------------------------------------------------------------------------
*/
Route::get('/admin', [PageController::class, 'adminDashboard'])->name('admin.dashboard');
Route::get('/admin/toko', [PageController::class, 'adminShops'])->name('admin.shops');
Route::patch('/admin/toko/{coffeeShop}', [AdminController::class, 'updateShopStatus'])->middleware(['auth', 'role:admin'])->name('admin.shops.status');
Route::get('/admin/pengguna', [PageController::class, 'adminUsers'])->name('admin.users');
Route::post('/admin/pengguna', [AdminController::class, 'storeUser'])->middleware(['auth', 'role:admin'])->name('admin.users.store');
Route::patch('/admin/pengguna/{user}', [AdminController::class, 'updateUserStatus'])->middleware(['auth', 'role:admin'])->name('admin.users.status');

/*
|--------------------------------------------------------------------------
| Alat pengembangan
|--------------------------------------------------------------------------
*/
Route::get('/peta', [PageController::class, 'sitemap'])->name('dev.sitemap');
