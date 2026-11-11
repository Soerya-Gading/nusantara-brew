<?php

namespace App\Http\Controllers;

use App\Models\CoffeeShopStaff;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OrderController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected OrderWorkflowService $workflowService
    ) {}

    public function show(Request $request)
    {
        $user = auth()->user();
        $orderCode = $request->query('order_code') ?? $request->route('order');

        if ($orderCode instanceof Order) {
            $order = $orderCode;
        } elseif ($orderCode) {
            $order = Order::with('items')->where('order_code', $orderCode)->firstOrFail();
        } else {
            $order = Order::with('items')->where('user_id', $user->id)->latest()->first();
        }

        if (!$order) {
            abort(404, 'Belum ada pesanan yang ditemukan.');
        }

        $this->authorizeOrderAccess($order, $user);

        return view('orders.show', compact('order'));
    }

    public function receipt(Request $request)
    {
        $user = auth()->user();
        $orderCode = $request->query('order_code') ?? $request->route('order');

        if ($orderCode instanceof Order) {
            $order = $orderCode;
        } elseif ($orderCode) {
            $order = Order::with('items')->where('order_code', $orderCode)->firstOrFail();
        } else {
            $order = Order::with('items')->where('user_id', $user->id)->latest()->first();
        }

        if (!$order) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        $this->authorizeOrderAccess($order, $user);

        return view('orders.receipt', compact('order'));
    }

    public function history()
    {
        $user = auth()->user();
        $orders = Order::with('items')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('orders.index', [
            'orders' => $orders,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    public function pay(Order $order)
    {
        $user = auth()->user();
        if ($order->user_id !== $user->id) {
            abort(403, 'Anda hanya dapat membayar pesanan milik Anda sendiri.');
        }

        $this->paymentService->simulateQrisPayment($order, $user);

        return redirect()->route('orders.show', ['order_code' => $order->order_code])
            ->with('success', 'Simulasi pembayaran QRIS berhasil! Pesanan masuk antrean kedai.');
    }

    public function cancel(Order $order)
    {
        $user = auth()->user();
        $this->authorizeOrderAccess($order, $user);

        $this->workflowService->cancelOrder($order, $user, 'Dibatalkan oleh pelanggan');

        return redirect()->route('orders.show', ['order_code' => $order->order_code])
            ->with('success', 'Pesanan telah dibatalkan.');
    }

    protected function authorizeOrderAccess(Order $order, $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($order->user_id === $user->id) {
            return;
        }

        if ($user->isOwner() && $order->coffeeShop && $order->coffeeShop->owner_user_id === $user->id) {
            return;
        }

        if ($user->isStaff() && CoffeeShopStaff::where('coffee_shop_id', $order->coffee_shop_id)->where('user_id', $user->id)->exists()) {
            return;
        }

        abort(403, 'Anda tidak memiliki hak akses untuk pesanan ini.');
    }
}
