@extends('layouts.app')
@section('title', 'Riwayat Pesanan — Nusantara Brew')

@section('content')
<div class="container order-page">
    <header class="order-heading">
        <div>
            <p class="order-eyebrow">Akun pelanggan</p>
            <h1>Riwayat pesanan</h1>
            <p class="text-muted">Pesanan dari semua kedai, terbaru lebih dulu.</p>
        </div>
    </header>

    @forelse($orders as $order)
        <article class="card order-panel" style="margin-bottom:var(--space-4);">
            <div class="order-heading" style="margin-bottom:var(--space-3);">
                <div>
                    <strong>{{ $order->order_code }}</strong>
                    <p class="text-sm text-muted">{{ $order->shop_name_snapshot }} &middot; {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</p>
                </div>
                <span class="badge {{ $order->badge_class }}">{{ $order->status_label }}</span>
            </div>

            <ul class="order-items">
                @foreach($order->items as $item)
                    <li>
                        <div>
                            <strong>{{ $item->name }}</strong>
                            <span class="text-sm text-muted">{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        </div>
                        <span>Rp {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="order-total">
                <span>Total</span>
                <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
            </div>

            <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;">
                <a href="{{ route('orders.show') }}" class="btn btn-secondary btn-sm">Lihat detail</a>
                @if($order->status === 'completed')
                    <a href="{{ route('orders.receipt') }}" class="btn btn-secondary btn-sm">Lihat struk</a>
                @endif
            </div>
        </article>
    @empty
        <div class="card" style="padding:var(--space-6);text-align:center;">
            <p class="text-sm text-muted">Belum ada pesanan. Riwayat muncul setelah pesanan pertama dibuat.</p>
            <a href="{{ route('shops.index') }}" class="btn btn-primary" style="margin-top:var(--space-4);">Jelajahi kedai</a>
        </div>
    @endforelse
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cart.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/orders.css') }}">
@endsection
