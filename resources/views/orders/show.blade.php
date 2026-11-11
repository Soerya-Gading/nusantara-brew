@extends('layouts.app')
@section('title', 'Pesanan ' . $order->order_code . ' — Nusantara Brew')

@php
    $steps = [
        'pending_payment' => 'Menunggu pembayaran',
        'queued' => 'Dalam antrean',
        'processing' => 'Sedang dibuat',
        'ready' => 'Siap',
        'completed' => 'Selesai',
    ];
    $orderKeys = array_keys($steps);
    $currentIndex = array_search($order->status, $orderKeys, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
    $statusLabel = $order->status === 'cancelled' ? 'Dibatalkan' : $steps[$order->status];
@endphp

@section('content')
<div class="container order-page">
    <a href="{{ route('shops.index') }}" class="cart-back-link">&larr; Kembali ke daftar kedai</a>

    <header class="order-heading">
        <div>
            <p class="order-eyebrow">Kode pesanan</p>
            <h1>{{ $order->order_code }}</h1>
            <p class="text-muted">{{ $order->shop_name_snapshot }}</p>
        </div>
        <span class="badge {{ $order->status === 'cancelled' ? 'badge-danger' : 'badge-warning' }}">{{ $statusLabel }}</span>
    </header>

    @if($order->status === 'pending_payment')
        <section class="order-payment card" aria-labelledby="payment-title">
            <div>
                <h2 id="payment-title">Selesaikan pembayaran</h2>
                <p class="text-sm text-muted">
                    Metode: {{ $order->payment_method === 'cash' ? 'Tunai di kasir' : 'QRIS simulasi' }}.
                    Sisa waktu <strong id="payment-countdown" data-expires-at="{{ $order->expires_at ? $order->expires_at->toIso8601String() : now()->addMinutes(15)->toIso8601String() }}">15 menit</strong>.
                </p>
                <p class="order-simulation-note"><strong>SIMULASI PEMBAYARAN — bukan QRIS nyata.</strong> Tidak ada dana yang diproses.</p>
            </div>
            <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;align-items:center;">
                @if(request()->routeIs('preview.*') || !isset($order->id))
                    <button type="button" class="btn btn-primary" disabled>Simulasikan pembayaran berhasil</button>
                @else
                    @if($order->payment_method === 'qris_simulation')
                        <form method="POST" action="{{ route('orders.pay', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Simulasikan pembayaran berhasil</button>
                        </form>
                    @else
                        <p class="badge badge-warning" style="margin:0;">Silakan bayar tunai di kasir kedai</p>
                    @endif
                    <form method="POST" action="{{ route('orders.cancel', $order->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Batalkan pesanan</button>
                    </form>
                    <!-- disabled -->
                @endif
            </div>
        </section>
    @endif

    <div class="order-layout">
        <section class="card order-panel" aria-labelledby="timeline-title">
            <h2 id="timeline-title">Status pesanan</h2>
            <ol class="order-timeline">
                @foreach($steps as $key => $label)
                    @php $state = $loop->index < $currentIndex ? 'completed' : ($loop->index === $currentIndex ? 'active' : ''); @endphp
                    <li class="timeline-step {{ $state }}">
                        <span class="timeline-dot" aria-hidden="true"></span>
                        <span class="timeline-step-title">{{ $label }}</span>
                        @if($state === 'active')<span class="timeline-step-time">Tahap saat ini</span>@endif
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="card order-panel" aria-labelledby="detail-title">
            <h2 id="detail-title">Rincian pesanan</h2>
            <dl class="order-meta">
                <div><dt>Cara menikmati</dt><dd>{{ $order->fulfillment_type === 'dine_in' ? 'Nikmati di tempat' : 'Ambil sendiri' }}</dd></div>
                @if($order->table_label)
                    <div><dt>Meja</dt><dd>{{ $order->table_label }}</dd></div>
                @endif
                <div><dt>Pemesan</dt><dd>{{ $order->customer_name_snapshot }}</dd></div>
                <div><dt>Telepon</dt><dd>{{ $order->customer_phone_snapshot }}</dd></div>
                <div><dt>Dibuat</dt><dd>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</dd></div>
            </dl>

            <h3 class="order-subheading">Item</h3>
            <ul class="order-items">
                @foreach($order->items as $item)
                    <li>
                        <div>
                            <strong>{{ $item->name }}</strong>
                            <span class="text-sm text-muted">{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                            @if($item->note)<span class="cart-item-note">{{ $item->note }}</span>@endif
                        </div>
                        <span>Rp {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>

            @if($order->customer_note)
                <h3 class="order-subheading">Catatan</h3>
                <p class="text-sm">{{ $order->customer_note }}</p>
            @endif

            <div class="order-total"><span>Total</span><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></div>
            <a href="{{ route('orders.receipt', ['order_code' => $order->order_code]) }}" class="btn btn-secondary btn-block">Lihat struk</a>
        </section>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cart.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/orders.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/js/order-countdown.js') }}" defer></script>
@endsection
