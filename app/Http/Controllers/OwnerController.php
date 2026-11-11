<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CoffeeShop;
use App\Models\CoffeeShopStaff;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    public function __construct(
        protected OrderWorkflowService $workflowService,
        protected PaymentService $paymentService,
        protected StockService $stockService
    ) {}

    protected function getOwnerShop(): CoffeeShop
    {
        $user = auth()->user();
        $shop = $user?->currentShop();

        if (!$shop) {
            abort(403, 'Anda belum memiliki kedai yang terdaftar atau ditugaskan.');
        }

        return $shop;
    }

    public function dashboard()
    {
        $shop = $this->getOwnerShop();
        $user = auth()->user();

        $completedOrdersCount = Order::where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->count();

        $revenue = (int) Order::where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->sum('total_amount');

        $awaitingPaymentCount = Order::where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_PENDING_PAYMENT)
            ->count();

        $inProgressCount = Order::where('coffee_shop_id', $shop->id)
            ->whereIn('status', [Order::STATUS_QUEUED, Order::STATUS_PROCESSING, Order::STATUS_READY])
            ->count();

        $orders = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->latest()
            ->take(10)
            ->get();

        $summary = [
            'completed_orders' => $completedOrdersCount,
            'revenue' => $revenue,
            'awaiting_payment' => $awaitingPaymentCount,
            'in_progress' => $inProgressCount,
        ];

        return view('owner.dashboard', [
            'shop' => (object) [
                'name' => $shop->name,
                'is_accepting_orders' => $shop->is_accepting_orders,
            ],
            'orders' => $orders,
            'summary' => $summary,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    public function orders()
    {
        $shop = $this->getOwnerShop();
        $user = auth()->user();

        $awaiting = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_PENDING_PAYMENT)
            ->oldest()
            ->get();

        $queued = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_QUEUED)
            ->oldest()
            ->get();

        $ready = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_READY)
            ->oldest()
            ->get();

        $tabs = [
            'awaiting' => [
                'label' => 'Menunggu pembayaran',
                'orders' => $awaiting,
                'action' => 'Konfirmasi tunai',
                'empty' => 'Tidak ada pesanan menunggu pembayaran.',
            ],
            'queued' => [
                'label' => 'Antrean',
                'orders' => $queued,
                'action' => 'Mulai dibuat',
                'empty' => 'Antrean produksi kosong.',
            ],
            'ready' => [
                'label' => 'Siap',
                'orders' => $ready,
                'action' => 'Tandai selesai',
                'empty' => 'Belum ada pesanan siap.',
            ],
        ];

        return view('owner.orders', [
            'shop' => (object) [
                'name' => $shop->name,
                'is_accepting_orders' => $shop->is_accepting_orders,
            ],
            'tabs' => $tabs,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    public function updateOrderStatus(Order $order, Request $request)
    {
        $shop = $this->getOwnerShop();
        if ($order->coffee_shop_id !== $shop->id) {
            abort(403);
        }

        $user = auth()->user();

        if ($order->status === Order::STATUS_QUEUED) {
            $this->workflowService->startProcessing($order, $user);
        } elseif ($order->status === Order::STATUS_PROCESSING) {
            $this->workflowService->markReady($order, $user);
        } elseif ($order->status === Order::STATUS_READY) {
            $this->workflowService->markCompleted($order, $user);
        }

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function confirmPayment(Order $order)
    {
        $shop = $this->getOwnerShop();
        if ($order->coffee_shop_id !== $shop->id) {
            abort(403);
        }

        $this->paymentService->confirmCashPayment($order, auth()->user());

        return redirect()->back()->with('success', 'Pembayaran tunai berhasil dikonfirmasi.');
    }

    public function menu(Request $request)
    {
        $shop = $this->getOwnerShop();
        $user = auth()->user();

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
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    public function storeMenu(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validated = $request->validate([
            'category_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $category = Category::where('coffee_shop_id', $shop->id)->findOrFail($validated['category_id']);

        Menu::create([
            'coffee_shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'is_active' => true,
            'is_available' => $validated['stock'] > 0,
        ]);

        return redirect()->route('owner.menu')->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        $shop = $this->getOwnerShop();
        if ($menu->coffee_shop_id !== $shop->id) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:1'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $category = Category::where('coffee_shop_id', $shop->id)->findOrFail($validated['category_id']);

        $menu->update([
            'category_id' => $category->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'is_available' => $request->has('is_available') ? (bool) $request->is_available : $menu->is_available,
        ]);

        return redirect()->route('owner.menu')->with('success', 'Menu berhasil diperbarui.');
    }

    public function restockMenu(Request $request, Menu $menu)
    {
        $shop = $this->getOwnerShop();
        if ($menu->coffee_shop_id !== $shop->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $this->stockService->restock($menu, $validated['amount'], auth()->user());

        return redirect()->route('owner.menu')->with('success', 'Stok menu berhasil ditambahkan.');
    }

    public function categories()
    {
        $shop = $this->getOwnerShop();
        $user = auth()->user();

        $categories = Category::withCount('menus')
            ->where('coffee_shop_id', $shop->id)
            ->orderBy('sort_order')
            ->get();

        return view('owner.categories', [
            'shop' => (object) ['name' => $shop->name],
            'categories' => $categories,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    public function storeCategory(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        Category::create([
            'coffee_shop_id' => $shop->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('owner.categories')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $shop = $this->getOwnerShop();
        if ($category->coffee_shop_id !== $shop->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $category->is_active,
        ]);

        return redirect()->route('owner.categories')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function shop()
    {
        $shop = $this->getOwnerShop();
        $user = auth()->user();

        $shopData = (object) [
            'name' => $shop->name,
            'address' => $shop->address,
            'description' => $shop->description,
            'opening_hours_text' => $shop->opening_hours_text,
            'status' => $shop->status,
            'is_accepting_orders' => $shop->is_accepting_orders,
            'updated_at' => $shop->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H.i') ?? '',
        ];

        return view('owner.shop', [
            'shop' => $shopData,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    public function updateShop(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string'],
            'opening_hours_text' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $shop->update($validated);

        return redirect()->route('owner.shop')->with('success', 'Profil kedai berhasil diperbarui.');
    }

    public function updateAcceptance(Request $request)
    {
        $shop = $this->getOwnerShop();
        $shop->is_accepting_orders = $request->boolean('is_accepting_orders');
        $shop->save();

        return redirect()->route('owner.shop')->with('success', 'Status penerimaan pesanan berhasil diperbarui.');
    }

    public function staff()
    {
        $shop = $this->getOwnerShop();
        $user = auth()->user();

        $staffAssignments = CoffeeShopStaff::with('user')
            ->where('coffee_shop_id', $shop->id)
            ->get();

        $staffList = $staffAssignments->map(function ($assignment) {
            return (object) [
                'id' => $assignment->user->id,
                'name' => $assignment->user->name,
                'email' => $assignment->user->email,
                'assigned_at' => 'ditugaskan ' . ($assignment->created_at ? $assignment->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') : ''),
                'is_active' => $assignment->user->is_active,
            ];
        });

        // Kandidat staff yang belum terikat ke kedai manapun
        $assignedUserIds = CoffeeShopStaff::pluck('user_id')->all();
        $candidates = User::where('role', User::ROLE_STAFF)
            ->where('is_active', true)
            ->whereNotIn('id', $assignedUserIds)
            ->get(['id', 'name', 'email']);

        return view('owner.staff', [
            'shop' => (object) ['name' => $shop->name],
            'staff' => $staffList,
            'candidates' => $candidates,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    public function assignStaff(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $staffUser = User::where('email', $validated['email'])
            ->where('role', User::ROLE_STAFF)
            ->where('is_active', true)
            ->first();

        if (!$staffUser) {
            return redirect()->back()->withErrors(['email' => 'Akun staf dengan email tersebut tidak ditemukan atau tidak aktif.']);
        }

        $alreadyAssigned = CoffeeShopStaff::where('user_id', $staffUser->id)->exists();
        if ($alreadyAssigned) {
            return redirect()->back()->withErrors(['email' => 'Staf tersebut sudah ditugaskan ke kedai lain.']);
        }

        CoffeeShopStaff::create([
            'coffee_shop_id' => $shop->id,
            'user_id' => $staffUser->id,
        ]);

        return redirect()->route('owner.staff')->with('success', 'Staf berhasil ditugaskan ke kedai.');
    }

    public function removeStaff(User $user)
    {
        $shop = $this->getOwnerShop();

        CoffeeShopStaff::where('coffee_shop_id', $shop->id)
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('owner.staff')->with('success', 'Penugasan staf telah dilepas.');
    }

    public function reports(Request $request)
    {
        $shop = $this->getOwnerShop();
        $user = auth()->user();

        $from = $request->query('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->query('to', now()->format('Y-m-d'));

        $startDateTime = Carbon::createFromFormat('Y-m-d', $from, 'Asia/Jakarta')->startOfDay()->utc();
        $endDateTime = Carbon::createFromFormat('Y-m-d', $to, 'Asia/Jakarta')->endOfDay()->utc();

        // BR-20 / Section 11: Omzet hanya dari pesanan completed dan paid
        $orders = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereBetween('completed_at', [$startDateTime, $endDateTime])
            ->get();

        $orderCount = $orders->count();
        $revenue = (int) $orders->sum('total_amount');

        $rowsMap = [];
        $totalItemsCount = 0;

        foreach ($orders as $ord) {
            foreach ($ord->items as $item) {
                $totalItemsCount += $item->quantity;
                $name = $item->name;
                if (!isset($rowsMap[$name])) {
                    $rowsMap[$name] = [
                        'name' => $name,
                        'quantity' => 0,
                        'amount' => 0,
                    ];
                }
                $rowsMap[$name]['quantity'] += $item->quantity;
                $rowsMap[$name]['amount'] += $item->line_total;
            }
        }

        $report = [
            'orders' => $orderCount,
            'revenue' => $revenue,
            'items' => $totalItemsCount,
            'rows' => collect(array_values($rowsMap)),
        ];

        return view('owner.reports', [
            'shop' => (object) ['name' => $shop->name],
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }
}
