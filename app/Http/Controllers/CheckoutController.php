<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService
    ) {}

    public function index()
    {
        $cart = $this->cartService->getCartForUser(auth()->user());

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Keranjang belanja Anda masih kosong.');
        }

        return view('checkout.index', compact('cart'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fulfillment_type' => ['required', 'in:pickup,dine_in'],
            'table_label' => ['nullable', 'string', 'max:30', 'required_if:fulfillment_type,dine_in'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:qris_simulation,cash'],
        ], [
            'fulfillment_type.required' => 'Pilih cara menikmati pesanan.',
            'table_label.required_if' => 'Nomor atau label meja wajib diisi jika memilih nikmati di tempat.',
            'customer_name.required' => 'Nama lengkap pemesan wajib diisi.',
            'customer_phone.required' => 'Nomor telepon pemesan wajib diisi.',
            'payment_method.required' => 'Pilih metode pembayaran.',
        ]);

        try {
            $order = $this->checkoutService->checkout(auth()->user(), $validated);

            return redirect()->route('orders.show', ['order_code' => $order->order_code])
                ->with('success', 'Pesanan Anda berhasil dibuat. Silakan selesaikan pembayaran.');
        } catch (ConflictHttpException $e) {
            return redirect()->route('cart.index')->withErrors(['cart' => $e->getMessage()]);
        } catch (UnprocessableEntityHttpException $e) {
            return redirect()->back()->withInput()->withErrors(['checkout' => $e->getMessage()]);
        }
    }
}
