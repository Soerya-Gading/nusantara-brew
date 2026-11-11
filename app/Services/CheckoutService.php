<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CoffeeShop;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CheckoutService
{
    public function checkout(User $user, array $data): Order
    {
        $fulfillmentType = $data['fulfillment_type'] ?? Order::FULFILLMENT_PICKUP;
        if (!in_array($fulfillmentType, [Order::FULFILLMENT_PICKUP, Order::FULFILLMENT_DINE_IN], true)) {
            throw new InvalidArgumentException('Jenis pemesanan tidak valid.');
        }

        $tableLabel = null;
        if ($fulfillmentType === Order::FULFILLMENT_DINE_IN) {
            $tableLabel = trim((string) ($data['table_label'] ?? ''));
            if ($tableLabel === '') {
                throw new UnprocessableEntityHttpException('Nomor atau label meja wajib diisi untuk pesanan di tempat.');
            }
            $tableLabel = mb_substr($tableLabel, 0, 30);
        }

        $customerName = trim((string) ($data['customer_name'] ?? $user->name));
        if ($customerName === '') {
            throw new UnprocessableEntityHttpException('Nama pemesan wajib diisi.');
        }
        $customerName = mb_substr($customerName, 0, 120);

        $customerPhone = trim((string) ($data['customer_phone'] ?? $user->phone ?? ''));
        if ($customerPhone === '') {
            throw new UnprocessableEntityHttpException('Nomor telepon wajib diisi.');
        }
        $customerPhone = mb_substr($customerPhone, 0, 30);

        $customerNote = isset($data['customer_note']) && trim((string) $data['customer_note']) !== ''
            ? mb_substr(trim((string) $data['customer_note']), 0, 500)
            : null;

        $paymentMethod = $data['payment_method'] ?? Order::METHOD_QRIS;
        if (!in_array($paymentMethod, [Order::METHOD_QRIS, Order::METHOD_CASH], true)) {
            throw new InvalidArgumentException('Metode pembayaran tidak valid.');
        }

        return DB::transaction(function () use (
            $user,
            $fulfillmentType,
            $tableLabel,
            $customerName,
            $customerPhone,
            $customerNote,
            $paymentMethod
        ) {
            // 1. Kunci Cart
            $cart = Cart::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$cart || !$cart->coffee_shop_id) {
                throw new UnprocessableEntityHttpException('Keranjang belanja Anda masih kosong.');
            }

            // BR-20: Cek idempotensi pasangan (cart_id, cart_version)
            $existingOrder = Order::where('cart_id', $cart->id)
                ->where('cart_version', $cart->version)
                ->first();
            if ($existingOrder) {
                if ($existingOrder->user_id !== $user->id) {
                    abort(403);
                }
                return $existingOrder->load('items');
            }

            $cartItems = CartItem::where('cart_id', $cart->id)->get();
            if ($cartItems->isEmpty()) {
                throw new UnprocessableEntityHttpException('Keranjang belanja Anda masih kosong.');
            }

            // 2. Kunci Menu dengan urutan ID konsisten (menghindari deadlock)
            $menuIds = $cartItems->pluck('menu_id')->sort()->values()->all();
            $menus = Menu::with(['coffeeShop', 'category'])
                ->whereIn('id', $menuIds)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 3. Validasi Kedai
            $shop = CoffeeShop::lockForUpdate()->find($cart->coffee_shop_id);
            if (!$shop || $shop->status !== 'active' || !$shop->is_accepting_orders) {
                throw new UnprocessableEntityHttpException('Kedai kopi sedang tutup atau tidak menerima pesanan baru.');
            }

            // 4. Validasi Menu, Kategori, Harga & Stok
            $hasPriceChanged = false;
            foreach ($cartItems as $item) {
                $menu = $menus->get($item->menu_id);
                if (!$menu) {
                    throw new UnprocessableEntityHttpException('Salah satu menu dalam keranjang tidak ditemukan.');
                }

                if ($menu->coffee_shop_id !== $shop->id) {
                    throw new ConflictHttpException('Menu tidak berasal dari kedai yang sama.');
                }

                if (!$menu->is_active || !$menu->is_available) {
                    throw new UnprocessableEntityHttpException("Menu '{$menu->name}' sedang tidak tersedia.");
                }

                if (!$menu->category || !$menu->category->is_active) {
                    throw new UnprocessableEntityHttpException("Kategori menu '{$menu->name}' sedang dinonaktifkan.");
                }

                // BR-08: Peninjauan ulang jika harga berubah
                if ($item->unit_price_seen !== $menu->price) {
                    $item->unit_price_seen = $menu->price;
                    $item->save();
                    $hasPriceChanged = true;
                }

                // BR-11 - BR-14: Tidak boleh overselling
                if ($menu->stock < $item->quantity) {
                    throw new UnprocessableEntityHttpException("Stok menu '{$menu->name}' tidak mencukupi (tersisa {$menu->stock}).");
                }
            }

            if ($hasPriceChanged) {
                $cart->version += 1;
                $cart->save();
                throw new ConflictHttpException('Harga menu telah diperbarui oleh kedai. Silakan periksa kembali total pesanan Anda.');
            }

            // 5. Hitung total rupiah utuh (BR-07, BR-09)
            $totalAmount = 0;
            $itemsData = [];

            foreach ($cartItems as $item) {
                $menu = $menus->get($item->menu_id);
                $lineTotal = $menu->price * $item->quantity;
                $totalAmount += $lineTotal;

                $itemsData[] = [
                    'menu_id' => $menu->id,
                    'menu' => $menu,
                    'menu_name_snapshot' => $menu->name,
                    'unit_price' => $menu->price,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                    'note' => $item->note,
                ];
            }

            // 6. Buat kode order unik: NB-YYYYMMDD-XXXX
            $orderCode = $this->generateUniqueOrderCode();

            // 7. Simpan Order (BR-10: snapshot transaksi)
            $order = Order::create([
                'order_code' => $orderCode,
                'user_id' => $user->id,
                'coffee_shop_id' => $shop->id,
                'cart_id' => $cart->id,
                'cart_version' => $cart->version,
                'shop_name_snapshot' => $shop->name,
                'shop_address_snapshot' => $shop->address,
                'customer_name_snapshot' => $customerName,
                'customer_phone_snapshot' => $customerPhone,
                'fulfillment_type' => $fulfillmentType,
                'table_label' => $tableLabel,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'payment_method' => $paymentMethod,
                'payment_status' => Order::PAYMENT_PENDING,
                'total_amount' => $totalAmount,
                'customer_note' => $customerNote,
                'expires_at' => now()->addMinutes(15),
            ]);

            // 8. Simpan Order Items & Kurangi Stok (BR-13: pengurangan stok atomik)
            foreach ($itemsData as $itemRow) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $itemRow['menu_id'],
                    'menu_name_snapshot' => $itemRow['menu_name_snapshot'],
                    'unit_price' => $itemRow['unit_price'],
                    'quantity' => $itemRow['quantity'],
                    'line_total' => $itemRow['line_total'],
                    'note' => $itemRow['note'],
                ]);

                $menu = $itemRow['menu'];
                $menu->decrement('stock', $itemRow['quantity']);
            }

            // 9. Rekam jejak status awal
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'actor_user_id' => $user->id,
                'event' => 'checkout',
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING_PAYMENT,
                'from_payment_status' => null,
                'to_payment_status' => Order::PAYMENT_PENDING,
                'reason' => "Checkout dibuat ({$fulfillmentType})",
                'created_at' => now(),
            ]);

            // 10. Kosongkan Cart & naikkan versi (BR-01, BR-20)
            CartItem::where('cart_id', $cart->id)->delete();
            $cart->coffee_shop_id = null;
            $cart->version += 1;
            $cart->save();

            return $order->load(['items', 'coffeeShop']);
        });
    }

    private function generateUniqueOrderCode(): string
    {
        $datePrefix = 'NB-' . now()->format('Ymd') . '-';
        do {
            $code = $datePrefix . strtoupper(Str::random(4));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
