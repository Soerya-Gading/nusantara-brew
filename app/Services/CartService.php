<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CoffeeShop;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CartService
{
    public function getCartForUser(User $user): ?Cart
    {
        return Cart::with(['coffeeShop', 'items.menu'])->where('user_id', $user->id)->first();
    }

    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['coffee_shop_id' => null, 'version' => 1]
        );
    }

    public function addItem(User $user, int $menuId, int $quantity, ?string $note = null): Cart
    {
        if ($quantity < 1 || $quantity > 99) {
            throw new InvalidArgumentException('Jumlah pesanan harus antara 1 sampai 99.');
        }

        return DB::transaction(function () use ($user, $menuId, $quantity, $note) {
            $cart = Cart::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'coffee_shop_id' => null,
                    'version' => 1,
                ]);
            }

            $menu = Menu::with(['coffeeShop', 'category'])->lockForUpdate()->findOrFail($menuId);

            if (!$menu->is_active || !$menu->is_available) {
                throw new UnprocessableEntityHttpException('Menu ini sedang tidak tersedia.');
            }

            if (!$menu->category || !$menu->category->is_active) {
                throw new UnprocessableEntityHttpException('Kategori menu ini sedang tidak aktif.');
            }

            $shop = $menu->coffeeShop;
            if (!$shop || $shop->status !== 'active' || !$shop->is_accepting_orders) {
                throw new UnprocessableEntityHttpException('Kedai sedang tidak menerima pesanan.');
            }

            // BR-02 & BR-06: Keranjang satu toko
            $existingCount = CartItem::where('cart_id', $cart->id)->count();
            if ($existingCount > 0 && $cart->coffee_shop_id !== null && $cart->coffee_shop_id !== $shop->id) {
                throw new ConflictHttpException('Keranjang sudah berisi menu dari kedai lain. Konfirmasi untuk mengganti kedai.');
            }

            // BR-03: Satu baris per menu
            $cartItem = CartItem::where('cart_id', $cart->id)->where('menu_id', $menu->id)->lockForUpdate()->first();

            $newQty = $quantity;
            if ($cartItem) {
                $newQty = $cartItem->quantity + $quantity;
                if ($newQty > 99) {
                    throw new InvalidArgumentException('Maksimal jumlah pesanan per menu adalah 99.');
                }
            }

            if ($newQty > $menu->stock) {
                throw new UnprocessableEntityHttpException("Stok tidak mencukupi. Sisa stok tersedia: {$menu->stock}.");
            }

            if ($cartItem) {
                $cartItem->quantity = $newQty;
                if ($note !== null) {
                    $cartItem->note = mb_substr($note, 0, 500);
                }
                $cartItem->unit_price_seen = $menu->price;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'unit_price_seen' => $menu->price,
                    'note' => $note ? mb_substr($note, 0, 500) : null,
                ]);
            }

            $cart->coffee_shop_id = $shop->id;
            $cart->version += 1;
            $cart->save();

            return $cart->load(['coffeeShop', 'items.menu']);
        });
    }

    public function updateItem(User $user, int $cartItemId, int $quantity, ?string $note = null): CartItem
    {
        if ($quantity < 1 || $quantity > 99) {
            throw new InvalidArgumentException('Jumlah pesanan harus antara 1 sampai 99.');
        }

        return DB::transaction(function () use ($user, $cartItemId, $quantity, $note) {
            $cart = Cart::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $cartItem = CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->lockForUpdate()->firstOrFail();
            $menu = Menu::lockForUpdate()->findOrFail($cartItem->menu_id);

            if ($quantity > $menu->stock) {
                throw new UnprocessableEntityHttpException("Stok tidak mencukupi. Sisa stok tersedia: {$menu->stock}.");
            }

            $cartItem->quantity = $quantity;
            if ($note !== null) {
                $cartItem->note = mb_substr($note, 0, 500);
            }
            $cartItem->unit_price_seen = $menu->price;
            $cartItem->save();

            $cart->version += 1;
            $cart->save();

            return $cartItem;
        });
    }

    public function removeItem(User $user, int $cartItemId): void
    {
        DB::transaction(function () use ($user, $cartItemId) {
            $cart = Cart::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $cartItem = CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->firstOrFail();
            $cartItem->delete();

            $remaining = CartItem::where('cart_id', $cart->id)->count();
            if ($remaining === 0) {
                $cart->coffee_shop_id = null;
            }

            $cart->version += 1;
            $cart->save();
        });
    }

    public function switchShop(User $user, int $menuId, int $quantity, ?string $note = null): Cart
    {
        return DB::transaction(function () use ($user, $menuId, $quantity, $note) {
            $cart = Cart::where('user_id', $user->id)->lockForUpdate()->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->delete();
                $cart->coffee_shop_id = null;
                $cart->version += 1;
                $cart->save();
            }

            return $this->addItem($user, $menuId, $quantity, $note);
        });
    }

    public function emptyCart(Cart $cart): void
    {
        CartItem::where('cart_id', $cart->id)->delete();
        $cart->coffee_shop_id = null;
        $cart->version += 1;
        $cart->save();
    }
}
