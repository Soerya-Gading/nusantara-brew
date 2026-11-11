<?php

namespace App\Services;

use App\Models\CoffeeShopStaff;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PaymentService
{
    public function __construct(
        protected OrderWorkflowService $workflowService
    ) {}

    public function simulateQrisPayment(Order $order, User $customer): Order
    {
        return DB::transaction(function () use ($order, $customer) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);

            if ($locked->user_id !== $customer->id) {
                throw new AccessDeniedHttpException('Anda hanya dapat mensimulasikan pembayaran untuk pesanan Anda sendiri.');
            }

            if ($locked->payment_method !== Order::METHOD_QRIS) {
                throw new UnprocessableEntityHttpException('Pesanan ini bukan menggunakan metode QRIS simulasi.');
            }

            // BR-18: Cek kedaluwarsa sebelum pembayaran
            if ($locked->isExpired()) {
                $this->workflowService->cancelOrder($locked, null, 'Pembayaran QRIS ditolak: batas waktu 15 menit telah terlampaui.');
                throw new UnprocessableEntityHttpException('Waktu pembayaran telah habis (kedaluwarsa). Pesanan dibatalkan.');
            }

            // Jika sudah dibayar sebelumnya (idempotency)
            if ($locked->payment_status === Order::PAYMENT_PAID && $locked->status !== Order::STATUS_PENDING_PAYMENT) {
                return $locked;
            }

            if ($locked->status !== Order::STATUS_PENDING_PAYMENT || $locked->payment_status !== Order::PAYMENT_PENDING) {
                throw new UnprocessableEntityHttpException('Pesanan tidak dalam status menunggu pembayaran.');
            }

            $locked->status = Order::STATUS_QUEUED;
            $locked->payment_status = Order::PAYMENT_PAID;
            $locked->paid_at = now();
            $locked->paid_by_user_id = $customer->id;
            $locked->save();

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'actor_user_id' => $customer->id,
                'event' => 'payment_received',
                'from_status' => Order::STATUS_PENDING_PAYMENT,
                'to_status' => Order::STATUS_QUEUED,
                'from_payment_status' => Order::PAYMENT_PENDING,
                'to_payment_status' => Order::PAYMENT_PAID,
                'reason' => 'Simulasi pembayaran QRIS berhasil dicatat',
                'created_at' => now(),
            ]);

            return $locked;
        });
    }

    public function confirmCashPayment(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            $locked = Order::with('coffeeShop')->lockForUpdate()->findOrFail($order->id);

            // Cek otorisasi owner / staff kedai
            $isOwner = $locked->coffeeShop && $locked->coffeeShop->owner_user_id === $actor->id;
            $isStaff = CoffeeShopStaff::where('coffee_shop_id', $locked->coffee_shop_id)
                ->where('user_id', $actor->id)
                ->exists();

            if (!$isOwner && !$isStaff) {
                throw new AccessDeniedHttpException('Anda tidak memiliki hak untuk mengonfirmasi pembayaran kedai ini.');
            }

            if ($locked->payment_method !== Order::METHOD_CASH) {
                throw new UnprocessableEntityHttpException('Pesanan ini bukan menggunakan metode tunai di kasir.');
            }

            // BR-18: Cek kedaluwarsa
            if ($locked->isExpired()) {
                $this->workflowService->cancelOrder($locked, $actor, 'Konfirmasi tunai ditolak: batas waktu 15 menit telah terlampaui.');
                throw new UnprocessableEntityHttpException('Waktu pembayaran telah habis (kedaluwarsa). Pesanan dibatalkan.');
            }

            if ($locked->payment_status === Order::PAYMENT_PAID && $locked->status !== Order::STATUS_PENDING_PAYMENT) {
                return $locked;
            }

            if ($locked->status !== Order::STATUS_PENDING_PAYMENT || $locked->payment_status !== Order::PAYMENT_PENDING) {
                throw new UnprocessableEntityHttpException('Pesanan tidak dalam status menunggu pembayaran.');
            }

            $locked->status = Order::STATUS_QUEUED;
            $locked->payment_status = Order::PAYMENT_PAID;
            $locked->paid_at = now();
            $locked->paid_by_user_id = $actor->id;
            $locked->save();

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event' => 'cash_confirmed',
                'from_status' => Order::STATUS_PENDING_PAYMENT,
                'to_status' => Order::STATUS_QUEUED,
                'from_payment_status' => Order::PAYMENT_PENDING,
                'to_payment_status' => Order::PAYMENT_PAID,
                'reason' => "Pembayaran tunai dikonfirmasi oleh {$actor->name}",
                'created_at' => now(),
            ]);

            return $locked;
        });
    }
}
