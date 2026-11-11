<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $cart = $user ? $this->cartService->getCartForUser($user) : null;

        if ($cart && $cart->items->isEmpty()) {
            $cart = null;
        }

        return view('cart.index', compact('cart'));
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $cart = $this->cartService->addItem(
                auth()->user(),
                $validated['menu_id'],
                $validated['quantity'],
                $validated['note'] ?? null
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Menu berhasil ditambahkan ke keranjang.',
                    'cart' => $cart,
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Menu berhasil ditambahkan ke keranjang.');
        } catch (ConflictHttpException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'conflict' => true,
                    'message' => $e->getMessage(),
                    'menu_id' => $validated['menu_id'],
                    'quantity' => $validated['quantity'],
                    'note' => $validated['note'] ?? null,
                ], 409);
            }

            return redirect()->back()->with('conflict_shop', [
                'message' => $e->getMessage(),
                'menu_id' => $validated['menu_id'],
                'quantity' => $validated['quantity'],
                'note' => $validated['note'] ?? null,
            ]);
        } catch (UnprocessableEntityHttpException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->back()->withErrors(['menu' => $e->getMessage()]);
        }
    }

    public function updateItem(Request $request, int $cartItemId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->cartService->updateItem(
            auth()->user(),
            $cartItemId,
            $validated['quantity'],
            $validated['note'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Item keranjang berhasil diperbarui.']);
        }

        return redirect()->route('cart.index')->with('success', 'Item keranjang diperbarui.');
    }

    public function removeItem(Request $request, int $cartItemId)
    {
        $this->cartService->removeItem(auth()->user(), $cartItemId);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Item keranjang berhasil dihapus.']);
        }

        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function switchShop(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->cartService->switchShop(
            auth()->user(),
            $validated['menu_id'],
            $validated['quantity'],
            $validated['note'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Kedai berhasil diganti dan menu ditambahkan.']);
        }

        return redirect()->route('cart.index')->with('success', 'Keranjang telah diperbarui untuk kedai baru.');
    }
}
