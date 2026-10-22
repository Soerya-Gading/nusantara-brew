@extends('layouts.dashboard')
@section('title', 'Antrean Pesanan — Nusantara Brew')
@section('topbar_title', 'Antrean pesanan')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Antrean pesanan</h1>
        <p class="page-subtitle">{{ $shop->name }} &middot; hanya pesanan kedai penugasan Anda.</p>
    </div>
</header>

<div class="queue-tabs" role="tablist" aria-label="Kelompok antrean">
    @foreach($tabs as $key => $tab)
        <button type="button" class="queue-tab {{ $loop->first ? 'active' : '' }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="staff-panel-{{ $key }}" id="staff-tab-{{ $key }}">
            {{ $tab['label'] }}
            <span class="queue-badge">{{ $tab['orders']->count() }}</span>
        </button>
    @endforeach
</div>

@foreach($tabs as $key => $tab)
    <section id="staff-panel-{{ $key }}" role="tabpanel" aria-labelledby="staff-tab-{{ $key }}" @if(!$loop->first) hidden @endif>
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
                        {{ $order->fulfillment_type === 'dine_in' ? 'Dine-in · Meja ' . $order->table_label : 'Pickup' }}
                    </span>
                    <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                </div>
                <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;">
                    <a href="{{ route('orders.show') }}" class="btn btn-secondary btn-sm">Lihat detail</a>
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
    const staffTabs = document.querySelectorAll('.queue-tab');
    staffTabs.forEach(tab => tab.addEventListener('click', () => {
        staffTabs.forEach(other => {
            const selected = other === tab;
            other.classList.toggle('active', selected);
            other.setAttribute('aria-selected', String(selected));
            document.getElementById(other.getAttribute('aria-controls')).hidden = !selected;
        });
    }));
</script>
@endsection
