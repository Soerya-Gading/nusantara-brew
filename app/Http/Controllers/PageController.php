<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Halaman tampilan dengan data contoh.
 *
 * Frontend sudah final; kelas ini hanya penyedia data sementara agar tampilan
 * bisa diperiksa tanpa database. Backend mengganti isi method dengan query
 * Eloquent dan menambahkan middleware auth/otorisasi. Nama route dan variabel
 * view tidak berubah saat penggantian.
 */
class PageController extends Controller
{
    public function cart()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        // Fixture tampilan saja. Backend menggantinya dengan cart customer terautentikasi.
        $items = collect([
            (object) ['menu_id' => 1, 'name' => 'Cold Brew Flores', 'unit_price' => 35000, 'quantity' => 2, 'note' => 'Tanpa gula'],
            (object) ['menu_id' => 2, 'name' => 'Croissant Mentega', 'unit_price' => 22000, 'quantity' => 1, 'note' => ''],
        ]);

        $cart = (object) [
            'coffee_shop_id' => 1,
            'shop_name' => 'Kopi Lembur Bandung',
            'items' => $items,
            'total_amount' => $items->sum(fn ($item) => $item->unit_price * $item->quantity),
        ];

        return view('cart.index', compact('cart'));
    }

    public function checkout()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        // Gunakan fixture cart yang sama agar kontrak ringkasan tidak berbeda.
        $cart = $this->cart()->getData()['cart'];

        return view('checkout.index', compact('cart'));
    }

    public function order()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        return view('orders.show', ['order' => $this->orderFixture()]);
    }

    public function receipt()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        return view('orders.receipt', ['order' => $this->orderFixture()]);
    }

    public function ownerDashboard()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $order = $this->orderFixture();
        $order->status_label = 'Menunggu pembayaran';

        $ready = clone $order;
        $ready->order_code = 'NB-20261022-103';
        $ready->status = 'ready';
        $ready->status_label = 'Siap diambil';
        $ready->total_amount = 51000;

        $shop = (object) [
            'name' => $order->shop_name_snapshot,
            'is_accepting_orders' => true,
        ];

        $summary = [
            'completed_orders' => 12,
            'revenue' => 1485000,
            'awaiting_payment' => 1,
            'in_progress' => 2,
        ];

        return view('owner.dashboard', [
            'shop' => $shop,
            'orders' => collect([$order, $ready]),
            'summary' => $summary,
        ]);
    }

    public function ownerOrders()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $base = $this->orderFixture();
        $base->status_label = 'Menunggu pembayaran';

        $queued = clone $base;
        $queued->order_code = 'NB-20261022-102';
        $queued->status = 'queued';
        $queued->status_label = 'Dalam antrean';

        $ready = clone $base;
        $ready->order_code = 'NB-20261022-103';
        $ready->status = 'ready';
        $ready->status_label = 'Siap diambil';
        $ready->fulfillment_type = 'dine_in';
        $ready->table_label = 'A3';
        $ready->total_amount = 51000;

        $shop = (object) [
            'name' => $base->shop_name_snapshot,
            'is_accepting_orders' => true,
        ];

        $tabs = [
            'awaiting' => ['label' => 'Menunggu pembayaran', 'orders' => collect([$base]), 'action' => 'Konfirmasi tunai', 'empty' => 'Tidak ada pesanan menunggu pembayaran.'],
            'queued' => ['label' => 'Antrean', 'orders' => collect([$queued]), 'action' => 'Mulai dibuat', 'empty' => 'Antrean produksi kosong.'],
            'ready' => ['label' => 'Siap', 'orders' => collect([$ready]), 'action' => 'Tandai selesai', 'empty' => 'Belum ada pesanan siap.'],
        ];

        return view('owner.orders', compact('shop', 'tabs'));
    }

    public function ownerMenu(Request $request)
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $categories = collect([
            (object) ['id' => 1, 'name' => 'Minuman Kopi'],
            (object) ['id' => 2, 'name' => 'Makanan & Camilan'],
        ]);

        $menus = collect([
            (object) ['id' => 1, 'name' => 'Cold Brew Flores', 'description' => 'Diseduh 18 jam dari biji single origin Flores.', 'category_id' => 1, 'category_name' => 'Minuman Kopi', 'price' => 35000, 'stock' => 10, 'is_available' => true],
            (object) ['id' => 2, 'name' => 'Espresso Sore', 'description' => 'Double shot espresso blend Toraja–Aceh.', 'category_id' => 1, 'category_name' => 'Minuman Kopi', 'price' => 28000, 'stock' => 8, 'is_available' => true],
            (object) ['id' => 3, 'name' => 'Latte Aren', 'description' => 'Espresso dengan susu segar dan gula aren cair.', 'category_id' => 1, 'category_name' => 'Minuman Kopi', 'price' => 38000, 'stock' => 0, 'is_available' => false],
            (object) ['id' => 4, 'name' => 'Croissant Mentega', 'description' => 'Croissant berlapis dengan mentega pilihan.', 'category_id' => 2, 'category_name' => 'Makanan & Camilan', 'price' => 22000, 'stock' => 5, 'is_available' => true],
        ]);

        $query = trim((string) $request->query('q'));
        $selectedCategory = $request->query('category');

        $menus = $menus
            ->when($query !== '', fn ($items) => $items->filter(fn ($menu) => str_contains(mb_strtolower($menu->name), mb_strtolower($query))))
            ->when($selectedCategory, fn ($items) => $items->where('category_id', (int) $selectedCategory))
            ->values();

        return view('owner.menu', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'categories' => $categories,
            'menus' => $menus,
            'query' => $query,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    public function ownerCategories()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $categories = collect([
            (object) ['name' => 'Minuman Kopi', 'description' => 'Espresso based, manual brew, dan minuman dingin.', 'menu_count' => 3, 'sort_order' => 1, 'is_active' => true],
            (object) ['name' => 'Makanan & Camilan', 'description' => 'Pendamping minum, dipanggang setiap pagi.', 'menu_count' => 1, 'sort_order' => 2, 'is_active' => true],
            (object) ['name' => 'Musiman', 'description' => 'Menu terbatas yang berganti tiap bulan.', 'menu_count' => 0, 'sort_order' => 3, 'is_active' => false],
        ]);

        return view('owner.categories', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'categories' => $categories,
        ]);
    }

    public function ownerShop()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $shop = (object) [
            'name' => 'Kopi Lembur Bandung',
            'address' => 'Jl. Braga No. 42, Bandung',
            'description' => 'Kedai kopi bergaya industrial di jantung Braga. Andalannya single origin Flores dan cold brew tradisional yang diseduh sejak dini hari.',
            'opening_hours_text' => 'Setiap hari, 07.00–22.00',
            'status' => 'active',
            'is_accepting_orders' => true,
            'updated_at' => '22 Okt 2026, 08.15',
        ];

        return view('owner.shop', compact('shop'));
    }

    public function ownerStaff()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $staff = collect([
            (object) ['name' => 'Andi Pratama', 'email' => 'andi.pratama@nusantarabrew.test', 'assigned_at' => 'ditugaskan 12 Sep 2026', 'is_active' => true],
            (object) ['name' => 'Sari Melati', 'email' => 'sari.melati@nusantarabrew.test', 'assigned_at' => 'ditugaskan 03 Okt 2026', 'is_active' => true],
        ]);

        $candidates = collect([
            (object) ['name' => 'Rudi Hartono', 'email' => 'rudi.hartono@nusantarabrew.test'],
        ]);

        return view('owner.staff', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'staff' => $staff,
            'candidates' => $candidates,
        ]);
    }

    public function ownerReports(Request $request)
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $from = $request->query('from', '2026-10-01');
        $to = $request->query('to', '2026-10-22');

        $report = [
            'orders' => 12,
            'revenue' => 1485000,
            'items' => 27,
            'rows' => collect([
                ['name' => 'Cold Brew Flores', 'quantity' => 14, 'amount' => 490000],
                ['name' => 'Espresso Sore', 'quantity' => 7, 'amount' => 196000],
                ['name' => 'Croissant Mentega', 'quantity' => 6, 'amount' => 132000],
            ]),
        ];

        return view('owner.reports', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'report' => $report,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function staffDashboard()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $base = $this->orderFixture();
        $base->status_label = 'Dalam antrean';
        $base->status = 'queued';

        $processing = clone $base;
        $processing->order_code = 'NB-20261022-102';
        $processing->status = 'processing';
        $processing->status_label = 'Sedang dibuat';
        $processing->fulfillment_type = 'dine_in';
        $processing->table_label = 'A3';

        $tabs = [
            'queued' => ['label' => 'Baru', 'orders' => collect([$base]), 'action' => 'Mulai dibuat', 'empty' => 'Tidak ada pesanan baru.'],
            'processing' => ['label' => 'Sedang dibuat', 'orders' => collect([$processing]), 'action' => 'Tandai siap', 'empty' => 'Tidak ada pesanan yang sedang dibuat.'],
            'ready' => ['label' => 'Siap', 'orders' => collect(), 'action' => 'Tandai selesai', 'empty' => 'Belum ada pesanan siap diambil.'],
        ];

        return view('staff.dashboard', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'tabs' => $tabs,
            'account' => (object) ['name' => 'Andi Pratama', 'role_label' => 'Staf'],
            'nav' => $this->staffNav(),
        ]);
    }

    public function staffHistory()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $done = $this->orderFixture();
        $done->order_code = 'NB-20261022-101';
        $done->status = 'completed';
        $done->status_label = 'Selesai';
        $done->total_amount = 66000;

        $cancelled = clone $done;
        $cancelled->order_code = 'NB-20261021-098';
        $cancelled->status_label = 'Selesai';

        return view('staff.history', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'orders' => collect([$done, $cancelled]),
            'account' => (object) ['name' => 'Andi Pratama', 'role_label' => 'Staf'],
            'nav' => $this->staffNav(),
        ]);
    }

    public function adminDashboard()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $summary = ['active_shops' => 3, 'pending_shops' => 1, 'users' => 24];

        $pending = collect([
            (object) ['name' => 'Gayo & Co Coffee House', 'address' => 'Jl. Cihampelas No. 118, Bandung', 'submitted_at' => '21 Okt 2026'],
        ]);

        return view('admin.dashboard', [
            'summary' => $summary,
            'pending' => $pending,
            'account' => (object) ['name' => 'Rina Kusuma', 'role_label' => 'Admin'],
            'nav' => $this->adminNav(),
        ]);
    }

    public function adminShops()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $shops = collect([
            (object) ['name' => 'Kopi Lembur Bandung', 'address' => 'Jl. Braga No. 42, Bandung', 'owner_name' => 'Hendra Wijaya', 'submitted_at' => '02 Sep 2026', 'status' => 'active', 'status_label' => 'Aktif'],
            (object) ['name' => 'Tanah Kopi Nusantara', 'address' => 'Jl. Dago Pojok No. 8, Bandung', 'owner_name' => 'Maya Lestari', 'submitted_at' => '10 Sep 2026', 'status' => 'active', 'status_label' => 'Aktif'],
            (object) ['name' => 'Gayo & Co Coffee House', 'address' => 'Jl. Cihampelas No. 118, Bandung', 'owner_name' => 'Fajar Nugroho', 'submitted_at' => '21 Okt 2026', 'status' => 'pending', 'status_label' => 'Menunggu persetujuan'],
            (object) ['name' => 'Gubug Kopi Lawang', 'address' => 'Jl. Cipaganti No. 17, Bandung', 'owner_name' => 'Dewi Anggraini', 'submitted_at' => '14 Agu 2026', 'status' => 'inactive', 'status_label' => 'Nonaktif'],
        ]);

        return view('admin.shops', [
            'shops' => $shops,
            'account' => (object) ['name' => 'Rina Kusuma', 'role_label' => 'Admin'],
            'nav' => $this->adminNav(),
        ]);
    }

    public function adminUsers()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $users = collect([
            (object) ['name' => 'Hendra Wijaya', 'email' => 'hendra@nusantarabrew.test', 'role_label' => 'Pemilik', 'is_active' => true],
            (object) ['name' => 'Maya Lestari', 'email' => 'maya@nusantarabrew.test', 'role_label' => 'Pemilik', 'is_active' => true],
            (object) ['name' => 'Andi Pratama', 'email' => 'andi.pratama@nusantarabrew.test', 'role_label' => 'Staf', 'is_active' => true],
            (object) ['name' => 'Budi Santoso', 'email' => 'budi.santoso@mail.test', 'role_label' => 'Pelanggan', 'is_active' => true],
            (object) ['name' => 'Sari Melati', 'email' => 'sari.melati@nusantarabrew.test', 'role_label' => 'Staf', 'is_active' => false],
        ]);

        return view('admin.users', [
            'users' => $users,
            'account' => (object) ['name' => 'Rina Kusuma', 'role_label' => 'Admin'],
            'nav' => $this->adminNav(),
        ]);
    }

    private function staffNav(): array
    {
        return [
            ['label' => 'Antrean pesanan', 'route' => 'staff.queue'],
            ['label' => 'Kasir', 'route' => 'staff.pos'],
            ['label' => 'Riwayat kedai', 'route' => 'staff.history'],
        ];
    }

    public function staffPos()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $categories = collect([
            (object) ['name' => 'Minuman Kopi', 'menus' => collect([
                (object) ['name' => 'Cold Brew Flores', 'price' => 35000, 'stock' => 10],
                (object) ['name' => 'Espresso Sore', 'price' => 28000, 'stock' => 8],
                (object) ['name' => 'Latte Aren', 'price' => 38000, 'stock' => 0],
            ])],
            (object) ['name' => 'Makanan & Camilan', 'menus' => collect([
                (object) ['name' => 'Croissant Mentega', 'price' => 22000, 'stock' => 5],
                (object) ['name' => 'Kue Lapis Singkong', 'price' => 15000, 'stock' => 3],
            ])],
        ]);

        return view('staff.pos', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'categories' => $categories,
            'account' => (object) ['name' => 'Andi Pratama', 'role_label' => 'Staf'],
            'nav' => $this->staffNav(),
        ]);
    }

    public function customerOrders()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $pending = $this->orderFixture();
        $pending->status_label = 'Menunggu pembayaran';
        $pending->badge_class = 'badge-warning';

        $done = $this->orderFixture();
        $done->order_code = 'NB-20261021-098';
        $done->status = 'completed';
        $done->status_label = 'Selesai';
        $done->badge_class = 'badge-success';
        $done->total_amount = 66000;

        return view('orders.index', [
            'orders' => collect([$pending, $done]),
            'account' => (object) ['name' => 'Budi Santoso', 'role_label' => 'Pelanggan'],
        ]);
    }

    private function adminNav(): array
    {
        return [
            ['label' => 'Ringkasan platform', 'route' => 'admin.dashboard'],
            ['label' => 'Persetujuan kedai', 'route' => 'admin.shops'],
            ['label' => 'Manajemen pengguna', 'route' => 'admin.users'],
        ];
    }

    /**
     * Fixture tampilan. Backend menggantinya dengan order milik customer terautentikasi.
     */
    private function orderFixture(): object
    {
        $cart = $this->cart()->getData()['cart'];

        return (object) [
            'order_code' => 'NB-20261022-104',
            'shop_name_snapshot' => $cart->shop_name,
            'shop_address_snapshot' => 'Jl. Braga No. 42, Bandung',
            'customer_name_snapshot' => 'Budi Santoso',
            'customer_phone_snapshot' => '081234567890',
            'fulfillment_type' => 'pickup',
            'table_label' => null,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'payment_method' => 'qris_simulation',
            'total_amount' => $cart->total_amount,
            'customer_note' => 'Tolong pisahkan sedotan.',
            'items' => $cart->items,
            'created_at' => now()->subMinutes(3),
            'expires_at' => now()->addMinutes(12),
        ];
    }
}
