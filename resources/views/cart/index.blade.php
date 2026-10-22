@extends('layouts.app')

@section('title', 'Keranjang — Nusantara Brew')

@section('content')
<div class="container">
    <header class="cart-page-heading">
        <h1>Keranjang</h1>
        <p class="text-muted">Periksa pilihan Anda sebelum melanjutkan pemesanan.</p>
    </header>

    {{-- Backend akan memberikan $cart milik customer yang login.
         Belum ada sumber data transaksi pada tahap frontend ini. --}}
    @if(isset($cart) && $cart->items->isNotEmpty())
        <div class="cart-layout">
            <section class="card cart-panel" aria-labelledby="cart-shop">
                <h2 id="cart-shop">{{ $cart->shop_name }}</h2>
                <p class="text-muted">Satu keranjang untuk satu kedai.</p>
                @foreach($cart->items as $item)
                    <article class="cart-item">
                        <div class="cart-item-info">
                            <h3 class="cart-item-title">{{ $item->name }}</h3>
                            <p class="cart-item-price">Rp {{ number_format($item->unit_price, 0, ',', '.') }} / porsi</p>
                            @if($item->note)
                                <p class="cart-item-note">{{ $item->note }}</p>
                            @endif
                            {{-- Aktifkan tindakan setelah route cart backend tersedia. --}}
                            <div class="cart-item-actions">
                                <label for="quantity-{{ $item->menu_id }}">Jumlah</label>
                                <input id="quantity-{{ $item->menu_id }}" type="number" min="1" max="99" value="{{ $item->quantity }}" disabled>
                                <button type="button" class="btn btn-secondary btn-sm" disabled>Ubah catatan</button>
                                <button type="button" class="btn btn-secondary btn-sm" disabled>Hapus</button>
                            </div>
                        </div>
                        <strong class="cart-line-total">Rp {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</strong>
                    </article>
                @endforeach
                <a href="{{ route('shops.index') }}" class="cart-back-link">Kembali ke daftar kedai</a>
            </section>
            <aside class="card cart-panel cart-summary" aria-labelledby="cart-summary-title">
                <h2 id="cart-summary-title">Ringkasan pesanan</h2>
                <dl>
                    <div><dt>Jumlah porsi</dt><dd>{{ $cart->items->sum('quantity') }}</dd></div>
                    <div class="cart-summary-total"><dt>Total</dt><dd>Rp {{ number_format($cart->total_amount, 0, ',', '.') }}</dd></div>
                </dl>
                <button type="button" class="btn btn-primary btn-block" disabled>Lanjut Checkout</button>
            </aside>
        </div>
    @else
    <section class="cart-empty" aria-labelledby="cart-empty-title">
        <span class="cart-empty-mark" aria-hidden="true">NB</span>
        <h2 id="cart-empty-title">Keranjang Anda masih kosong</h2>
        <p>Temukan kedai pilihan, lalu pilih menu yang ingin Anda nikmati.</p>
        <a href="{{ route('shops.index') }}" class="btn btn-primary">Jelajahi Kedai</a>
    </section>
    @endif
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cart.css') }}">
@endsection
