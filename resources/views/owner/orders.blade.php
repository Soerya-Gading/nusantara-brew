@extends('layouts.dashboard')
@section('title', 'Antrean Pesanan — Nusantara Brew')
@section('topbar_title', 'Antrean pesanan')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Antrean pesanan</h1>
        <p class="page-subtitle">Pesanan belum dibayar tidak masuk produksi. Perubahan status akan tersedia setelah backend tersambung.</p>
    </div>
    <span class="badge {{ $shop->is_accepting_orders ? 'badge-success' : 'badge-warning' }}">
        {{ $shop->is_accepting_orders ? 'Menerima pesanan' : 'Tidak menerima pesanan' }}
    </span>
</header>

<div class="queue-tabs" role="tablist" aria-label="Kelompok antrean">
    @foreach($tabs as $key => $tab)
        <button type="button" class="queue-tab {{ $loop->first ? 'active' : '' }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="queue-panel-{{ $key }}" id="queue-tab-{{ $key }}">
            {{ $tab['label'] }}
            <span class="queue-badge">{{ $tab['orders']->count() }}</span>
        </button>
    @endforeach
</div>

@foreach($tabs as $key => $tab)
    <section id="queue-panel-{{ $key }}" role="tabpanel" aria-labelledby="queue-tab-{{ $key }}" @if(!$loop->first) hidden @endif>
        @forelse($tab['orders'] as $order)
            <article class="order-ticket" style="margin-bottom:var(--space-4);">
                <div class="ticket-header">
                    <span class="ticket-code">{{ $order->order_code }}</span>
                    <span class="badge badge-info">{{ $order->status_label }}</span>
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
                        {{ $order->fulfillment_type === 'dine_in' ? 'Dine-in · Meja ' . $order->table_label : 'Pickup' }}
                    </span>
                    <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                </div>
                @if($order->customer_note)
                    <p class="text-sm text-muted">Catatan: {{ $order->customer_note }}</p>
                @endif
                <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;">
                    <a href="{{ route('orders.show') }}" class="btn btn-secondary btn-sm">Lihat detail</a>
                    {{-- Aktifkan setelah service workflow backend tersedia. --}}
                    <button type="button" class="btn btn-primary btn-sm" disabled>{{ $tab['action'] }}</button>
                </div>
            </article>
        @empty
            <div class="card" style="padding:var(--space-5);">
                <p class="text-sm text-muted">{{ $tab['empty'] }}</p>
            </div>
        @endforelse
    </section>
@endforeach
@endsection

@section('scripts')
<script>
    const tabs = document.querySelectorAll('.queue-tab');
    tabs.forEach(tab => tab.addEventListener('click', () => {
        tabs.forEach(other => {
            const selected = other === tab;
            other.classList.toggle('active', selected);
            other.setAttribute('aria-selected', String(selected));
            document.getElementById(other.getAttribute('aria-controls')).hidden = !selected;
        });
    }));
</script>
@endsection
