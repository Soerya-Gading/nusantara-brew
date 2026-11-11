<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderWorkflowService
{
    public function startProcessing(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);

            if ($locked->status !== Order::STATUS_QUEUED || $locked->payment_status !== Order::PAYMENT_PAID) {
                throw new UnprocessableEntityHttpException('Pesanan hanya dapat mulai dibuat jika berada dalam antrean dan telah lunas.');
            }

            $locked->status = Order::STATUS_PROCESSING;
            $locked->save();

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event' => 'processing_started',
                'from_status' => Order::STATUS_QUEUED,
                'to_status' => Order::STATUS_PROCESSING,
                'from_payment_status' => Order::PAYMENT_PAID,
                'to_payment_status' => Order::PAYMENT_PAID,
                'reason' => "Pesanan mulai dibuat oleh {$actor->name}",
                'created_at' => now(),
            ]);

            return $locked;
        });
    }

    public function markReady(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);

            if ($locked->status !== Order::STATUS_PROCESSING || $locked->payment_status !== Order::PAYMENT_PAID) {
                throw new UnprocessableEntityHttpException('Pesanan hanya dapat ditandai siap jika sedang dibuat.');
            }

            $locked->status = Order::STATUS_READY;
            $locked->save();

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event' => 'order_ready',
                'from_status' => Order::STATUS_PROCESSING,
                'to_status' => Order::STATUS_READY,
                'from_payment_status' => Order::PAYMENT_PAID,
                'to_payment_status' => Order::PAYMENT_PAID,
                'reason' => "Pesanan selesai dibuat dan siap diambil/disajikan oleh {$actor->name}",
                'created_at' => now(),
            ]);

            return $locked;
        });
    }

    public function markCompleted(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);

            if ($locked->status !== Order::STATUS_READY || $locked->payment_status !== Order::PAYMENT_PAID) {
                throw new UnprocessableEntityHttpException('Pesanan hanya dapat ditandai selesai jika statusnya sudah siap.');
            }

            $locked->status = Order::STATUS_COMPLETED;
            $locked->completed_at = now();
            $locked->save();

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event' => 'order_completed',
                'from_status' => Order::STATUS_READY,
                'to_status' => Order::STATUS_COMPLETED,
                'from_payment_status' => Order::PAYMENT_PAID,
                'to_payment_status' => Order::PAYMENT_PAID,
                'reason' => "Pesanan telah diserahkan / disajikan oleh {$actor->name}",
                'created_at' => now(),
            ]);

            return $locked;
        });
    }

    public function cancelOrder(Order $order, ?User $actor = null, string $reason = 'Pesanan dibatalkan'): Order
    {
        return DB::transaction(function () use ($order, $actor, $reason) {
            $locked = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            // BR-15 & BR-26: Hanya pesanan belum bayar yang boleh dibatalkan
            if ($locked->status !== Order::STATUS_PENDING_PAYMENT || $locked->payment_status !== Order::PAYMENT_PENDING) {
                throw new UnprocessableEntityHttpException('Pesanan yang sudah dibayar atau diproses tidak dapat dibatalkan.');
            }

            $locked->status = Order::STATUS_CANCELLED;
            $locked->payment_status = Order::PAYMENT_VOIDED;
            $locked->cancelled_at = now();
            $locked->cancellation_reason = $reason;

            // BR-15: Pelepasan stok tepat sekali
            if ($locked->stock_released_at === null) {
                $menuIds = $locked->items->pluck('menu_id')->sort()->values()->all();
                $menus = Menu::whereIn('id', $menuIds)->orderBy('id', 'asc')->lockForUpdate()->get()->keyBy('id');

                foreach ($locked->items as $item) {
                    $menu = $menus->get($item->menu_id);
                    if ($menu) {
                        $menu->increment('stock', $item->quantity);
                    }
                }
                $locked->stock_released_at = now();
            }

            $locked->save();

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'actor_user_id' => $actor?->id,
                'event' => 'order_cancelled',
                'from_status' => Order::STATUS_PENDING_PAYMENT,
                'to_status' => Order::STATUS_CANCELLED,
                'from_payment_status' => Order::PAYMENT_PENDING,
                'to_payment_status' => Order::PAYMENT_VOIDED,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            return $locked;
        });
    }

    public function expireUnpaidOrders(): int
    {
        $expiredOrders = Order::where('status', Order::STATUS_PENDING_PAYMENT)
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            try {
                $this->cancelOrder($order, null, 'Waktu pembayaran 15 menit telah habis (kedaluwarsa otomatis).');
                $count++;
            } catch (\Throwable) {
                // Ignore conflict jika sedang diproses transaksi lain
            }
        }

        return $count;
    }
}
