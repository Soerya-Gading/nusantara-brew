@extends('layouts.dashboard')
@section('title', 'Kasir — Nusantara Brew')
@section('topbar_title', 'Kasir')
@section('topbar_context', $shop->name)

@section('content')
<div class="pos-layout">
    <section class="pos-catalog" aria-labelledby="pos-menu-title">
        <div class="page-header">
            <div>
                <h1 class="page-title" id="pos-menu-title">Pilih menu</h1>
                <p class="page-subtitle">Pesanan dibuat atas nama pelanggan di kedai.</p>
            </div>
        </div>

        <div class="pos-search">
            <label class="sr-only" for="pos-search">Cari menu</label>
            <input id="pos-search" type="search" class="form-control" placeholder="Cari menu" data-pos-search>
        </div>

        @foreach($categories as $category)
            <div class="pos-category" data-pos-category>
                <h2 class="pos-category-title">{{ $category->name }}</h2>
                <div class="pos-menu-grid">
                    @foreach($category->menus as $menu)
                        <button type="button" class="pos-menu-card" data-pos-item data-name="{{ $menu->name }}" data-price="{{ $menu->price }}" data-stock="{{ $menu->stock }}" @if($menu->stock === 0) data-sold-out="1" @endif>
                            <span class="pos-menu-name">{{ $menu->name }}</span>
                            <span class="pos-menu-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                            @if($menu->stock === 0)
                                <span class="badge badge-danger">Habis</span>
                            @else
                                <span class="pos-menu-stock">Stok {{ $menu->stock }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach

        <p class="text-sm text-muted" data-pos-empty hidden>Tidak ada menu yang cocok.</p>
    </section>

    <aside class="pos-cart" aria-labelledby="pos-cart-title">
        <div class="pos-cart-head">
            <h2 id="pos-cart-title">Pesanan baru</h2>
            <span class="badge badge-neutral" data-pos-count>0 item</span>
        </div>

        <ul class="pos-cart-list" data-pos-list>
            <li class="pos-cart-empty" data-pos-placeholder>Belum ada item. Pilih menu di sebelah kiri.</li>
        </ul>

        <form method="POST" action="#" onsubmit="return false;" class="pos-checkout">
            @csrf
            <div class="form-group">
                <label for="pos-name" class="form-label">Nama pelanggan</label>
                <input id="pos-name" name="customer_name" class="form-control" maxlength="120" required disabled>
            </div>
            <div class="form-group">
                <label for="pos-fulfillment" class="form-label">Layanan</label>
                <select id="pos-fulfillment" name="fulfillment_type" class="form-control" disabled>
                    <option value="dine_in">Dine-in</option>
                    <option value="pickup">Pickup</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pos-payment" class="form-label">Pembayaran</label>
                <select id="pos-payment" name="payment_method" class="form-control" disabled>
                    <option value="cash">Tunai</option>
                    <option value="qris_simulation">QRIS simulasi</option>
                </select>
            </div>

            <div class="pos-total">
                <span>Total</span>
                <strong data-pos-total>Rp 0</strong>
            </div>

            {{-- Aktifkan setelah endpoint POS tersedia. --}}
            <button type="submit" class="btn btn-primary btn-block" disabled>Buat pesanan</button>
            <p class="text-xs text-muted">Stok dan pembayaran diverifikasi server saat pesanan dibuat.</p>
        </form>
    </aside>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/js/pos.js') }}" defer></script>
@endsection
