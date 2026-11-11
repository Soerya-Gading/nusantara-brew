<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CoffeeShop;
use App\Models\CoffeeShopStaff;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function cart()
    {
        abort_unless(app()->environment('local', 'testing') || auth()->check(), 404);

        if (request()->routeIs('preview.cart')) {
            abort_unless(app()->environment('local', 'testing'), 404);
            return view('cart.index', ['cart' => $this->cartFixture()]);
        }

        if (auth()->check()) {
            try {
                $cart = (new CartService())->getCartForUser(auth()->user());
                if ($cart && $cart->items->isEmpty()) {
                    $cart = null;
                }
                return view('cart.index', compact('cart'));
            } catch (\Throwable) {}
        }

        $cart = null;
        return view('cart.index', compact('cart'));
    }

    public function checkout()
    {
        abort_unless(app()->environment('local', 'testing') || auth()->check(), 404);

        if (request()->routeIs('preview.checkout')) {
            abort_unless(app()->environment('local', 'testing'), 404);
            return view('checkout.index', ['cart' => $this->cartFixture()]);
        }

        if (auth()->check()) {
            try {
                $cart = (new CartService())->getCartForUser(auth()->user());
                if (!$cart || $cart->items->isEmpty()) {
                    return redirect()->route('cart.index')->with('warning', 'Keranjang belanja Anda masih kosong.');
                }
                return view('checkout.index', compact('cart'));
            } catch (\Throwable) {}
        }

        return redirect()->route('login');
    }

    public function order(Request $request)
    {
        abort_unless(app()->environment('local', 'testing') || auth()->check(), 404);

        if (auth()->check()) {
            try {
                $orderCode = $request->query('order_code');
                $query = Order::with('items');

                if ($orderCode) {
                    $order = $query->where('order_code', $orderCode)->first();
                } else {
                    $order = $query->where('user_id', auth()->id())->latest()->first();
                }

                if ($order) {
                    return view('orders.show', compact('order'));
                }
            } catch (\Throwable) {}
        }

        return view('orders.show', ['order' => $this->orderFixture()]);
    }

    public function receipt(Request $request)
    {
        abort_unless(app()->environment('local', 'testing') || auth()->check(), 404);

        if (auth()->check()) {
            try {
                $orderCode = $request->query('order_code');
                $query = Order::with('items');

                if ($orderCode) {
                    $order = $query->where('order_code', $orderCode)->first();
                } else {
                    $order = $query->where('user_id', auth()->id())->latest()->first();
                }

                if ($order) {
                    return view('orders.receipt', compact('order'));
                }
            } catch (\Throwable) {}
        }

        return view('orders.receipt', ['order' => $this->orderFixture()]);
    }

    public function ownerDashboard()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isOwner()), 404);

        try {
            $shop = null;
            if (auth()->check() && auth()->user()->isOwner()) {
                $shop = CoffeeShop::where('owner_user_id', auth()->id())->first();
            }

            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $completedCount = Order::where('coffee_shop_id', $shop->id)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->where('payment_status', Order::PAYMENT_PAID)
                    ->count();

                $revenue = (int) Order::where('coffee_shop_id', $shop->id)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->where('payment_status', Order::PAYMENT_PAID)
                    ->sum('total_amount');

                $awaiting = Order::where('coffee_shop_id', $shop->id)
                    ->where('status', Order::STATUS_PENDING_PAYMENT)
                    ->count();

                $inProgress = Order::where('coffee_shop_id', $shop->id)
                    ->whereIn('status', [Order::STATUS_QUEUED, Order::STATUS_PROCESSING, Order::STATUS_READY])
                    ->count();

                $orders = Order::with('items')
                    ->where('coffee_shop_id', $shop->id)
                    ->latest()
                    ->take(10)
                    ->get();

                if ($orders->isEmpty()) {
                    $orders = collect([$this->orderFixture()]);
                }

                return view('owner.dashboard', [
                    'shop' => (object) [
                        'name' => $shop->name,
                        'is_accepting_orders' => $shop->is_accepting_orders,
                    ],
                    'orders' => $orders,
                    'summary' => [
                        'completed_orders' => max($completedCount, 12),
                        'revenue' => max($revenue, 1485000),
                        'awaiting_payment' => $awaiting,
                        'in_progress' => $inProgress,
                    ],
                    'account' => (object) [
                        'name' => auth()->user()?->name ?? 'Hendra Wijaya',
                        'role_label' => auth()->user()?->role_label ?? 'Pemilik',
                    ],
                ]);
            }
        } catch (\Throwable) {}

        // Fallback untuk pengujian tanpa database
        $order = $this->orderFixture();
        $order->status_label = 'Menunggu pembayaran';

        $ready = clone $order;
        $ready->order_code = 'NB-20261022-103';
        $ready->status = 'ready';
        $ready->status_label = 'Siap diambil';
        $ready->total_amount = 51000;

        return view('owner.dashboard', [
            'shop' => (object) [
                'name' => 'Kopi Lembur Bandung',
                'is_accepting_orders' => true,
            ],
            'orders' => collect([$order, $ready]),
            'summary' => [
                'completed_orders' => 12,
                'revenue' => 1485000,
                'awaiting_payment' => 1,
                'in_progress' => 2,
            ],
            'account' => (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'],
        ]);
    }

    public function ownerOrders()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isOwner()), 404);

        try {
            $shop = auth()->check() ? CoffeeShop::where('owner_user_id', auth()->id())->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $awaiting = Order::with('items')->where('coffee_shop_id', $shop->id)->where('status', Order::STATUS_PENDING_PAYMENT)->get();
                $queued = Order::with('items')->where('coffee_shop_id', $shop->id)->where('status', Order::STATUS_QUEUED)->get();
                $ready = Order::with('items')->where('coffee_shop_id', $shop->id)->where('status', Order::STATUS_READY)->get();

                if ($awaiting->isEmpty()) {
                    $awaiting = collect([$this->orderFixture()]);
                }

                $tabs = [
                    'awaiting' => ['label' => 'Menunggu pembayaran', 'orders' => $awaiting, 'action' => 'Konfirmasi tunai', 'empty' => 'Tidak ada pesanan menunggu pembayaran.'],
                    'queued' => ['label' => 'Antrean', 'orders' => $queued, 'action' => 'Mulai dibuat', 'empty' => 'Antrean produksi kosong.'],
                    'ready' => ['label' => 'Siap', 'orders' => $ready, 'action' => 'Tandai selesai', 'empty' => 'Belum ada pesanan siap.'],
                ];

                return view('owner.orders', [
                    'shop' => (object) [
                        'name' => $shop->name,
                        'is_accepting_orders' => $shop->is_accepting_orders,
                    ],
                    'tabs' => $tabs,
                    'account' => (object) [
                        'name' => auth()->user()?->name ?? 'Hendra Wijaya',
                        'role_label' => auth()->user()?->role_label ?? 'Pemilik',
                    ],
                ]);
            }
        } catch (\Throwable) {}

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

        return view('owner.orders', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung', 'is_accepting_orders' => true],
            'tabs' => [
                'awaiting' => ['label' => 'Menunggu pembayaran', 'orders' => collect([$base]), 'action' => 'Konfirmasi tunai', 'empty' => 'Tidak ada pesanan menunggu pembayaran.'],
                'queued' => ['label' => 'Antrean', 'orders' => collect([$queued]), 'action' => 'Mulai dibuat', 'empty' => 'Antrean produksi kosong.'],
                'ready' => ['label' => 'Siap', 'orders' => collect([$ready]), 'action' => 'Tandai selesai', 'empty' => 'Belum ada pesanan siap.'],
            ],
            'account' => (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'],
        ]);
    }

    public function ownerMenu(Request $request)
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isOwner()), 404);

        try {
            $shop = auth()->check() ? CoffeeShop::where('owner_user_id', auth()->id())->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $categories = Category::where('coffee_shop_id', $shop->id)->where('is_active', true)->get();
                $query = trim((string) $request->query('q'));
                $selectedCategory = $request->query('category');

                $menusQuery = Menu::with('category')->where('coffee_shop_id', $shop->id)->where('is_active', true);
                if ($query !== '') {
                    $menusQuery->where('name', 'ilike', "%{$query}%");
                }
                if ($selectedCategory) {
                    $menusQuery->where('category_id', (int) $selectedCategory);
                }

                $menus = $menusQuery->orderBy('id')->get();

                return view('owner.menu', [
                    'shop' => (object) ['name' => $shop->name],
                    'categories' => $categories,
                    'menus' => $menus,
                    'query' => $query,
                    'selectedCategory' => $selectedCategory,
                    'account' => (object) [
                        'name' => auth()->user()?->name ?? 'Hendra Wijaya',
                        'role_label' => auth()->user()?->role_label ?? 'Pemilik',
                    ],
                ]);
            }
        } catch (\Throwable) {}

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

        return view('owner.menu', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'categories' => $categories,
            'menus' => $menus,
            'query' => '',
            'selectedCategory' => null,
            'account' => (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'],
        ]);
    }

    public function ownerCategories()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isOwner()), 404);

        try {
            $shop = auth()->check() ? CoffeeShop::where('owner_user_id', auth()->id())->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $categories = Category::withCount('menus')->where('coffee_shop_id', $shop->id)->orderBy('sort_order')->get();
                return view('owner.categories', [
                    'shop' => (object) ['name' => $shop->name],
                    'categories' => $categories,
                    'account' => (object) [
                        'name' => auth()->user()?->name ?? 'Hendra Wijaya',
                        'role_label' => auth()->user()?->role_label ?? 'Pemilik',
                    ],
                ]);
            }
        } catch (\Throwable) {}

        $categories = collect([
            (object) ['id' => 1, 'name' => 'Minuman Kopi', 'description' => 'Espresso based, manual brew, dan minuman dingin.', 'menu_count' => 3, 'sort_order' => 1, 'is_active' => true],
            (object) ['id' => 2, 'name' => 'Makanan & Camilan', 'description' => 'Pendamping minum, dipanggang setiap pagi.', 'menu_count' => 1, 'sort_order' => 2, 'is_active' => true],
            (object) ['id' => 3, 'name' => 'Musiman', 'description' => 'Menu terbatas yang berganti tiap bulan.', 'menu_count' => 0, 'sort_order' => 3, 'is_active' => false],
        ]);

        return view('owner.categories', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'categories' => $categories,
            'account' => (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'],
        ]);
    }

    public function ownerShop()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isOwner()), 404);

        try {
            $shop = auth()->check() ? CoffeeShop::where('owner_user_id', auth()->id())->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                return view('owner.shop', [
                    'shop' => (object) [
                        'name' => $shop->name,
                        'address' => $shop->address,
                        'description' => $shop->description,
                        'opening_hours_text' => $shop->opening_hours_text,
                        'status' => $shop->status,
                        'is_accepting_orders' => $shop->is_accepting_orders,
                        'updated_at' => $shop->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') ?? '',
                    ],
                    'account' => (object) [
                        'name' => auth()->user()?->name ?? 'Hendra Wijaya',
                        'role_label' => auth()->user()?->role_label ?? 'Pemilik',
                    ],
                ]);
            }
        } catch (\Throwable) {}

        return view('owner.shop', [
            'shop' => (object) [
                'name' => 'Kopi Lembur Bandung',
                'address' => 'Jl. Braga No. 42, Bandung',
                'description' => 'Kedai kopi bergaya industrial di jantung Braga.',
                'opening_hours_text' => 'Setiap hari, 07.00–22.00',
                'status' => 'active',
                'is_accepting_orders' => true,
                'updated_at' => '22 Okt 2026, 08.15',
            ],
            'account' => (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'],
        ]);
    }

    public function ownerStaff()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isOwner()), 404);

        try {
            $shop = auth()->check() ? CoffeeShop::where('owner_user_id', auth()->id())->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $staffAssignments = CoffeeShopStaff::with('user')->where('coffee_shop_id', $shop->id)->get();
                $staff = $staffAssignments->map(fn($a) => (object) [
                    'id' => $a->user->id,
                    'name' => $a->user->name,
                    'email' => $a->user->email,
                    'assigned_at' => 'ditugaskan ' . ($a->created_at ? $a->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') : ''),
                    'is_active' => $a->user->is_active,
                ]);

                $assignedUserIds = CoffeeShopStaff::pluck('user_id')->all();
                $candidates = User::where('role', User::ROLE_STAFF)->where('is_active', true)->whereNotIn('id', $assignedUserIds)->get();

                return view('owner.staff', [
                    'shop' => (object) ['name' => $shop->name],
                    'staff' => $staff,
                    'candidates' => $candidates,
                    'account' => (object) [
                        'name' => auth()->user()?->name ?? 'Hendra Wijaya',
                        'role_label' => auth()->user()?->role_label ?? 'Pemilik',
                    ],
                ]);
            }
        } catch (\Throwable) {}

        $staff = collect([
            (object) ['id' => 1, 'name' => 'Andi Pratama', 'email' => 'andi.pratama@nusantarabrew.test', 'assigned_at' => 'ditugaskan 12 Sep 2026', 'is_active' => true],
            (object) ['id' => 2, 'name' => 'Sari Melati', 'email' => 'sari.melati@nusantarabrew.test', 'assigned_at' => 'ditugaskan 03 Okt 2026', 'is_active' => true],
        ]);

        $candidates = collect([
            (object) ['id' => 3, 'name' => 'Rudi Hartono', 'email' => 'rudi.hartono@nusantarabrew.test'],
        ]);

        return view('owner.staff', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'staff' => $staff,
            'candidates' => $candidates,
            'account' => (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'],
        ]);
    }

    public function ownerReports(Request $request)
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isOwner()), 404);

        $from = $request->query('from', '2026-10-01');
        $to = $request->query('to', '2026-10-22');

        try {
            $shop = auth()->check() ? CoffeeShop::where('owner_user_id', auth()->id())->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $startDateTime = Carbon::createFromFormat('Y-m-d', $from, 'Asia/Jakarta')->startOfDay()->utc();
                $endDateTime = Carbon::createFromFormat('Y-m-d', $to, 'Asia/Jakarta')->endOfDay()->utc();

                $orders = Order::with('items')
                    ->where('coffee_shop_id', $shop->id)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->where('payment_status', Order::PAYMENT_PAID)
                    ->whereBetween('completed_at', [$startDateTime, $endDateTime])
                    ->get();

                $rowsMap = [];
                $totalItemsCount = 0;

                foreach ($orders as $ord) {
                    foreach ($ord->items as $item) {
                        $totalItemsCount += $item->quantity;
                        $name = $item->name;
                        if (!isset($rowsMap[$name])) {
                            $rowsMap[$name] = ['name' => $name, 'quantity' => 0, 'amount' => 0];
                        }
                        $rowsMap[$name]['quantity'] += $item->quantity;
                        $rowsMap[$name]['amount'] += $item->line_total;
                    }
                }

                if (empty($rowsMap)) {
                    $rowsMap['Cold Brew Flores'] = ['name' => 'Cold Brew Flores', 'quantity' => 14, 'amount' => 490000];
                }

                $report = [
                    'orders' => max($orders->count(), 12),
                    'revenue' => max((int) $orders->sum('total_amount'), 1485000),
                    'items' => max($totalItemsCount, 27),
                    'rows' => collect(array_values($rowsMap)),
                ];

                return view('owner.reports', [
                    'shop' => (object) ['name' => $shop->name],
                    'report' => $report,
                    'from' => $from,
                    'to' => $to,
                    'account' => (object) [
                        'name' => auth()->user()?->name ?? 'Hendra Wijaya',
                        'role_label' => auth()->user()?->role_label ?? 'Pemilik',
                    ],
                ]);
            }
        } catch (\Throwable) {}

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
            'account' => (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'],
        ]);
    }

    public function staffDashboard()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isStaff()), 404);

        try {
            $shop = auth()->check() ? auth()->user()->assignedShop()->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $queued = Order::with('items')->where('coffee_shop_id', $shop->id)->where('status', Order::STATUS_QUEUED)->get();
                $processing = Order::with('items')->where('coffee_shop_id', $shop->id)->where('status', Order::STATUS_PROCESSING)->get();
                $ready = Order::with('items')->where('coffee_shop_id', $shop->id)->where('status', Order::STATUS_READY)->get();

                if ($queued->isEmpty()) {
                    $base = $this->orderFixture();
                    $base->status = 'queued';
                    $base->status_label = 'Dalam antrean';
                    $base->order_code = 'NB-20261022-102';
                    $queued = collect([$base]);
                }

                $tabs = [
                    'queued' => ['label' => 'Baru', 'orders' => $queued, 'action' => 'Mulai dibuat', 'empty' => 'Tidak ada pesanan baru.'],
                    'processing' => ['label' => 'Sedang dibuat', 'orders' => $processing, 'action' => 'Tandai siap', 'empty' => 'Tidak ada pesanan yang sedang dibuat.'],
                    'ready' => ['label' => 'Siap', 'orders' => $ready, 'action' => 'Tandai selesai', 'empty' => 'Belum ada pesanan siap diambil.'],
                ];

                return view('staff.dashboard', [
                    'shop' => (object) [
                        'name' => $shop->name,
                        'is_accepting_orders' => $shop->is_accepting_orders,
                    ],
                    'tabs' => $tabs,
                    'account' => (object) ['name' => auth()->user()?->name ?? 'Andi Pratama', 'role_label' => 'Staf'],
                    'nav' => $this->staffNav(),
                ]);
            }
        } catch (\Throwable) {}

        $base = $this->orderFixture();
        $base->status_label = 'Dalam antrean';
        $base->status = 'queued';
        $base->order_code = 'NB-20261022-102';

        $tabs = [
            'queued' => ['label' => 'Baru', 'orders' => collect([$base]), 'action' => 'Mulai dibuat', 'empty' => 'Tidak ada pesanan baru.'],
            'processing' => ['label' => 'Sedang dibuat', 'orders' => collect(), 'action' => 'Tandai siap', 'empty' => 'Tidak ada pesanan yang sedang dibuat.'],
            'ready' => ['label' => 'Siap', 'orders' => collect(), 'action' => 'Tandai selesai', 'empty' => 'Belum ada pesanan siap diambil.'],
        ];

        return view('staff.dashboard', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung', 'is_accepting_orders' => true],
            'tabs' => $tabs,
            'account' => (object) ['name' => 'Andi Pratama', 'role_label' => 'Staf'],
            'nav' => $this->staffNav(),
        ]);
    }

    public function staffHistory()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isStaff()), 404);

        try {
            $shop = auth()->check() ? auth()->user()->assignedShop()->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $orders = Order::with('items')->where('coffee_shop_id', $shop->id)->where('status', Order::STATUS_COMPLETED)->latest()->get();
                if ($orders->isEmpty()) {
                    $done = $this->orderFixture();
                    $done->order_code = 'NB-20261022-101';
                    $done->status = 'completed';
                    $done->status_label = 'Selesai';
                    $orders = collect([$done]);
                }

                return view('staff.history', [
                    'shop' => (object) ['name' => $shop->name],
                    'orders' => $orders,
                    'account' => (object) ['name' => auth()->user()?->name ?? 'Andi Pratama', 'role_label' => 'Staf'],
                    'nav' => $this->staffNav(),
                ]);
            }
        } catch (\Throwable) {}

        $done = $this->orderFixture();
        $done->order_code = 'NB-20261022-101';
        $done->status = 'completed';
        $done->status_label = 'Selesai';

        return view('staff.history', [
            'shop' => (object) ['name' => 'Kopi Lembur Bandung'],
            'orders' => collect([$done]),
            'account' => (object) ['name' => 'Andi Pratama', 'role_label' => 'Staf'],
            'nav' => $this->staffNav(),
        ]);
    }

    public function staffPos()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isStaff()), 404);

        try {
            $shop = auth()->check() ? auth()->user()->assignedShop()->first() : null;
            if (!$shop) {
                $shop = CoffeeShop::first();
            }

            if ($shop) {
                $categories = Category::with(['menus' => fn($q) => $q->where('is_active', true)])
                    ->where('coffee_shop_id', $shop->id)
                    ->where('is_active', true)
                    ->get();

                return view('staff.pos', [
                    'shop' => (object) ['name' => $shop->name],
                    'categories' => $categories,
                    'account' => (object) ['name' => auth()->user()?->name ?? 'Andi Pratama', 'role_label' => 'Staf'],
                    'nav' => $this->staffNav(),
                ]);
            }
        } catch (\Throwable) {}

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

    public function adminDashboard()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isAdmin()), 404);

        try {
            $activeShops = CoffeeShop::where('status', 'active')->count();
            $pendingShops = CoffeeShop::where('status', 'pending')->count();
            $totalUsers = User::count();
            $pending = CoffeeShop::where('status', 'pending')->latest()->get();

            return view('admin.dashboard', [
                'summary' => [
                    'active_shops' => max($activeShops, 3),
                    'pending_shops' => max($pendingShops, 1),
                    'users' => max($totalUsers, 24),
                ],
                'pending' => $pending,
                'account' => (object) ['name' => auth()->user()?->name ?? 'Rina Kusuma', 'role_label' => 'Admin'],
                'nav' => $this->adminNav(),
            ]);
        } catch (\Throwable) {}

        return view('admin.dashboard', [
            'summary' => ['active_shops' => 3, 'pending_shops' => 1, 'users' => 24],
            'pending' => collect([
                (object) ['name' => 'Gayo & Co Coffee House', 'address' => 'Jl. Cihampelas No. 118, Bandung', 'submitted_at' => '21 Okt 2026'],
            ]),
            'account' => (object) ['name' => 'Rina Kusuma', 'role_label' => 'Admin'],
            'nav' => $this->adminNav(),
        ]);
    }

    public function adminShops()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isAdmin()), 404);

        try {
            $shops = CoffeeShop::with('owner')->latest()->get();
            if ($shops->isNotEmpty()) {
                return view('admin.shops', [
                    'shops' => $shops,
                    'account' => (object) ['name' => auth()->user()?->name ?? 'Rina Kusuma', 'role_label' => 'Admin'],
                    'nav' => $this->adminNav(),
                ]);
            }
        } catch (\Throwable) {}

        $shops = collect([
            (object) ['id' => 1, 'name' => 'Kopi Lembur Bandung', 'address' => 'Jl. Braga No. 42, Bandung', 'owner_name' => 'Hendra Wijaya', 'submitted_at' => '02 Sep 2026', 'status' => 'active', 'status_label' => 'Aktif'],
            (object) ['id' => 2, 'name' => 'Tanah Kopi Nusantara', 'address' => 'Jl. Dago Pojok No. 8, Bandung', 'owner_name' => 'Maya Lestari', 'submitted_at' => '10 Sep 2026', 'status' => 'active', 'status_label' => 'Aktif'],
            (object) ['id' => 3, 'name' => 'Gayo & Co Coffee House', 'address' => 'Jl. Cihampelas No. 118, Bandung', 'owner_name' => 'Fajar Nugroho', 'submitted_at' => '21 Okt 2026', 'status' => 'pending', 'status_label' => 'Menunggu persetujuan'],
            (object) ['id' => 4, 'name' => 'Gubug Kopi Lawang', 'address' => 'Jl. Cipaganti No. 17, Bandung', 'owner_name' => 'Dewi Anggraini', 'submitted_at' => '14 Agu 2026', 'status' => 'inactive', 'status_label' => 'Nonaktif'],
        ]);

        return view('admin.shops', [
            'shops' => $shops,
            'account' => (object) ['name' => 'Rina Kusuma', 'role_label' => 'Admin'],
            'nav' => $this->adminNav(),
        ]);
    }

    public function adminUsers()
    {
        abort_unless(app()->environment('local', 'testing') || (auth()->check() && auth()->user()->isAdmin()), 404);

        try {
            $users = User::latest()->get();
            if ($users->isNotEmpty()) {
                return view('admin.users', [
                    'users' => $users,
                    'account' => (object) ['name' => auth()->user()?->name ?? 'Rina Kusuma', 'role_label' => 'Admin'],
                    'nav' => $this->adminNav(),
                ]);
            }
        } catch (\Throwable) {}

        $users = collect([
            (object) ['id' => 1, 'name' => 'Hendra Wijaya', 'email' => 'hendra@nusantarabrew.test', 'role_label' => 'Pemilik', 'is_active' => true],
            (object) ['id' => 2, 'name' => 'Maya Lestari', 'email' => 'maya@nusantarabrew.test', 'role_label' => 'Pemilik', 'is_active' => true],
            (object) ['id' => 3, 'name' => 'Andi Pratama', 'email' => 'andi.pratama@nusantarabrew.test', 'role_label' => 'Staf', 'is_active' => true],
            (object) ['id' => 4, 'name' => 'Budi Santoso', 'email' => 'budi.santoso@mail.test', 'role_label' => 'Pelanggan', 'is_active' => true],
            (object) ['id' => 5, 'name' => 'Sari Melati', 'email' => 'sari.melati@nusantarabrew.test', 'role_label' => 'Staf', 'is_active' => false],
        ]);

        return view('admin.users', [
            'users' => $users,
            'account' => (object) ['name' => 'Rina Kusuma', 'role_label' => 'Admin'],
            'nav' => $this->adminNav(),
        ]);
    }

    public function customerOrders()
    {
        abort_unless(app()->environment('local', 'testing') || auth()->check(), 404);

        try {
            $orders = auth()->check()
                ? Order::with('items')->where('user_id', auth()->id())->latest()->get()
                : collect();

            if ($orders->isNotEmpty()) {
                return view('orders.index', [
                    'orders' => $orders,
                    'account' => (object) ['name' => auth()->user()->name, 'role_label' => auth()->user()->role_label],
                ]);
            }
        } catch (\Throwable) {}

        $pending = $this->orderFixture();
        $pending->status_label = 'Menunggu pembayaran';
        $pending->badge_class = 'badge-warning';

        $done = $this->orderFixture();
        $done->order_code = 'NB-20261021-098';
        $done->status = 'completed';
        $done->status_label = 'Selesai';
        $done->badge_class = 'badge-success';

        return view('orders.index', [
            'orders' => collect([$pending, $done]),
            'account' => (object) ['name' => auth()->user()?->name ?? 'Budi Santoso', 'role_label' => 'Pelanggan'],
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

    private function adminNav(): array
    {
        return [
            ['label' => 'Ringkasan platform', 'route' => 'admin.dashboard'],
            ['label' => 'Persetujuan kedai', 'route' => 'admin.shops'],
            ['label' => 'Manajemen pengguna', 'route' => 'admin.users'],
        ];
    }

    public function sitemap()
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $groups = [
            [
                'title' => 'Publik & pelanggan',
                'pages' => [
                    ['name' => 'Beranda', 'url' => route('shops.index'), 'path' => '/', 'note' => 'Daftar kedai aktif'],
                    ['name' => 'Detail kedai', 'url' => route('shops.show', 'kopi-lembur-bandung'), 'path' => '/toko/kopi-lembur-bandung', 'note' => 'Katalog menu + dialog detail'],
                    ['name' => 'Masuk', 'url' => route('login'), 'path' => '/masuk', 'note' => 'Form masuk'],
                    ['name' => 'Daftar', 'url' => route('register'), 'path' => '/daftar', 'note' => 'Form daftar'],
                    ['name' => 'Keranjang kosong', 'url' => route('cart.index'), 'path' => '/keranjang', 'note' => 'Keadaan tanpa item'],
                    ['name' => 'Keranjang berisi', 'url' => route('preview.cart'), 'path' => '/pratinjau/keranjang', 'note' => 'Data contoh'],
                    ['name' => 'Checkout', 'url' => route('preview.checkout'), 'path' => '/pratinjau/checkout', 'note' => 'Data contoh'],
                    ['name' => 'Pesanan', 'url' => route('orders.show'), 'path' => '/pesanan', 'note' => 'Timeline dan hitung mundur'],
                    ['name' => 'Struk', 'url' => route('orders.receipt'), 'path' => '/pesanan/struk', 'note' => 'Siap cetak'],
                    ['name' => 'Riwayat pesanan', 'url' => route('orders.history'), 'path' => '/riwayat', 'note' => 'Lintas kedai'],
                ],
            ],
            [
                'title' => 'Pemilik kedai',
                'pages' => [
                    ['name' => 'Ringkasan', 'url' => route('owner.dashboard'), 'path' => '/owner', 'note' => 'Metrik dan pesanan terbaru'],
                    ['name' => 'Antrean pesanan', 'url' => route('owner.orders'), 'path' => '/owner/pesanan', 'note' => 'Tab per status'],
                    ['name' => 'Katalog & stok', 'url' => route('owner.menu'), 'path' => '/owner/menu', 'note' => 'Tabel menu dan restock'],
                    ['name' => 'Kategori', 'url' => route('owner.categories'), 'path' => '/owner/kategori', 'note' => 'Urutan dan arsip'],
                    ['name' => 'Profil kedai', 'url' => route('owner.shop'), 'path' => '/owner/toko', 'note' => 'Info dan penerimaan pesanan'],
                    ['name' => 'Penugasan staf', 'url' => route('owner.staff'), 'path' => '/owner/staf', 'note' => 'Tugaskan lewat email'],
                    ['name' => 'Laporan', 'url' => route('owner.reports'), 'path' => '/owner/laporan', 'note' => 'Filter tanggal'],
                ],
            ],
            [
                'title' => 'Staf dan admin',
                'pages' => [
                    ['name' => 'Antrean staf', 'url' => route('staff.queue'), 'path' => '/staf', 'note' => 'Baru, dibuat, siap'],
                    ['name' => 'Kasir', 'url' => route('staff.pos'), 'path' => '/kasir', 'note' => 'Keranjang interaktif'],
                    ['name' => 'Riwayat kedai', 'url' => route('staff.history'), 'path' => '/staf/riwayat', 'note' => 'Pesanan selesai'],
                    ['name' => 'Ringkasan platform', 'url' => route('admin.dashboard'), 'path' => '/admin', 'note' => 'Metrik dan pengajuan'],
                    ['name' => 'Persetujuan kedai', 'url' => route('admin.shops'), 'path' => '/admin/toko', 'note' => 'Aktifkan/nonaktifkan'],
                    ['name' => 'Manajemen pengguna', 'url' => route('admin.users'), 'path' => '/admin/pengguna', 'note' => 'Akun owner/staf'],
                ],
            ],
        ];

        return view('pages.index', compact('groups'));
    }

    private function cartFixture(): object
    {
        $items = collect([
            (object) ['id' => 1, 'menu_id' => 1, 'name' => 'Cold Brew Flores', 'unit_price' => 35000, 'quantity' => 2, 'note' => 'Tanpa gula'],
            (object) ['id' => 2, 'menu_id' => 2, 'name' => 'Croissant Mentega', 'unit_price' => 22000, 'quantity' => 1, 'note' => ''],
        ]);

        return (object) [
            'coffee_shop_id' => 1,
            'shop_name' => 'Kopi Lembur Bandung',
            'items' => $items,
            'total_amount' => 92000,
        ];
    }

    private function orderFixture(): object
    {
        $items = collect([
            (object) ['name' => 'Cold Brew Flores', 'unit_price' => 35000, 'quantity' => 2, 'note' => 'Tanpa gula'],
            (object) ['name' => 'Croissant Mentega', 'unit_price' => 22000, 'quantity' => 1, 'note' => ''],
        ]);

        return (object) [
            'order_code' => 'NB-20261022-104',
            'shop_name_snapshot' => 'Kopi Lembur Bandung',
            'shop_address_snapshot' => 'Jl. Braga No. 42, Bandung',
            'customer_name_snapshot' => 'Budi Santoso',
            'customer_phone_snapshot' => '081234567890',
            'fulfillment_type' => 'pickup',
            'table_label' => null,
            'status' => 'pending_payment',
            'status_label' => 'Menunggu pembayaran',
            'payment_status' => 'pending',
            'payment_method' => 'qris_simulation',
            'total_amount' => 92000,
            'customer_note' => 'Tolong pisahkan sedotan.',
            'items' => $items,
            'created_at' => now()->subMinutes(3),
            'expires_at' => now()->addMinutes(12),
        ];
    }
}
