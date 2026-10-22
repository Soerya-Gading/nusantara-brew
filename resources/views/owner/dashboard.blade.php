@extends('layouts.dashboard')
@section('title', 'Ringkasan Kedai — Nusantara Brew')
@section('topbar_title', 'Ringkasan')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">{{ $shop->name }}</h1>
        <p class="page-subtitle">Ringkasan operasional hari ini. Data mengikuti pesanan yang sudah selesai dan dibayar.</p>
    </div>
    <span class="badge {{ $shop->is_accepting_orders ? 'badge-success' : 'badge-warning' }}">
        {{ $shop->is_accepting_orders ? 'Menerima pesanan' : 'Tidak menerima pesanan' }}
    </span>
</header>

<section class="metrics-grid" aria-label="Ringkasan hari ini">
    <article class="metric-card">
        <span class="metric-label">Pesanan selesai hari ini</span>
        <strong class="metric-value">{{ $summary['completed_orders'] }}</strong>
        <span class="metric-hint">Hanya pesanan berstatus selesai dan lunas</span>
    </article>
    <article class="metric-card">
        <span class="metric-label">Omzet pesanan selesai</span>
        <strong class="metric-value">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</strong>
        <span class="metric-hint">Bukan laba; biaya bahan belum dihitung</span>
    </article>
    <article class="metric-card">
        <span class="metric-label">Menunggu pembayaran</span>
        <strong class="metric-value">{{ $summary['awaiting_payment'] }}</strong>
        <span class="metric-hint">Belum masuk antrean produksi</span>
    </article>
    <article class="metric-card">
        <span class="metric-label">Sedang diproses</span>
        <strong class="metric-value">{{ $summary['in_progress'] }}</strong>
        <span class="metric-hint">Antrean, sedang dibuat, dan siap</span>
    </article>
</section>

<section aria-labelledby="recent-orders-title">
    <div class="page-header">
        <h2 id="recent-orders-title" class="page-title" style="font-size:1.1rem;">Pesanan terbaru</h2>
        <a href="{{ route('owner.orders') }}" class="btn btn-secondary btn-sm">Lihat antrean</a>
    </div>

    @forelse($orders as $order)
        <article class="order-ticket" style="margin-bottom:var(--space-3);">
            <div class="ticket-header">
                <span class="ticket-code">{{ $order->order_code }}</span>
                <span class="badge {{ $order->status === 'pending_payment' ? 'badge-warning' : 'badge-info' }}">{{ $order->status_label }}</span>
            </div>
            <div class="ticket-items">
                @foreach($order->items as $item)
                    <div class="ticket-item-row">
                        <span>{{ $item->quantity }} × {{ $item->name }}</span>
                        <span>Rp {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                    @if($item->note)
                        <p class="ticket-note">{{ $item->note }}</p>
                    @endif
                @endforeach
            </div>
            <div class="ticket-footer">
                <span class="text-xs text-muted">
                    {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M, H:i') }} WIB &middot;
                    {{ $order->fulfillment_type === 'dine_in' ? 'Dine-in' : 'Pickup' }}
                </span>
                <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
            </div>
        </article>
    @empty
        <div class="card" style="padding:var(--space-5);">
            <p class="text-sm text-muted">Belum ada pesanan masuk hari ini.</p>
        </div>
    @endforelse
</section>
@endsection
