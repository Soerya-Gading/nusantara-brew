<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CoffeeShop;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function __construct(
        protected OrderWorkflowService $workflowService,
        protected PaymentService $paymentService
    ) {}

    protected function getStaffShop(): CoffeeShop
    {
        $shop = auth()->user()->assignedShop()->first();
        if (!$shop) {
            abort(403, 'Anda belum ditugaskan ke kedai kopi manapun.');
        }

        return $shop;
    }

    private function staffNav(): array
    {
        return [
            ['label' => 'Antrean pesanan', 'route' => 'staff.queue'],
            ['label' => 'Kasir', 'route' => 'staff.pos'],
            ['label' => 'Riwayat kedai', 'route' => 'staff.history'],
        ];
    }

    public function queue()
    {
        $shop = $this->getStaffShop();
        $user = auth()->user();

        $queued = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_QUEUED)
            ->oldest()
            ->get();

        $processing = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_PROCESSING)
            ->oldest()
            ->get();

        $ready = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->where('status', Order::STATUS_READY)
            ->oldest()
            ->get();

        $tabs = [
            'queued' => [
                'label' => 'Baru',
                'orders' => $queued,
                'action' => 'Mulai dibuat',
                'empty' => 'Tidak ada pesanan baru.',
            ],
            'processing' => [
                'label' => 'Sedang dibuat',
                'orders' => $processing,
                'action' => 'Tandai siap',
                'empty' => 'Tidak ada pesanan yang sedang dibuat.',
            ],
            'ready' => [
                'label' => 'Siap',
                'orders' => $ready,
                'action' => 'Tandai selesai',
                'empty' => 'Belum ada pesanan siap diambil.',
            ],
        ];

        return view('staff.dashboard', [
            'shop' => (object) [
                'name' => $shop->name,
                'is_accepting_orders' => $shop->is_accepting_orders,
            ],
            'tabs' => $tabs,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
            'nav' => $this->staffNav(),
        ]);
    }

    public function history()
    {
        $shop = $this->getStaffShop();
        $user = auth()->user();

        $orders = Order::with('items')
            ->where('coffee_shop_id', $shop->id)
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->latest()
            ->take(30)
            ->get();

        return view('staff.history', [
            'shop' => (object) ['name' => $shop->name],
            'orders' => $orders,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
            'nav' => $this->staffNav(),
        ]);
    }

    public function pos()
    {
        $shop = $this->getStaffShop();
        $user = auth()->user();

        $categories = Category::with(['menus' => function ($q) {
            $q->where('is_active', true);
        }])
            ->where('coffee_shop_id', $shop->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('staff.pos', [
            'shop' => (object) ['name' => $shop->name],
            'categories' => $categories,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
            'nav' => $this->staffNav(),
        ]);
    }
}
