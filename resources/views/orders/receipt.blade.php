@extends('layouts.app')
@section('title', 'Struk ' . $order->order_code . ' — Nusantara Brew')

@section('content')
<div class="container receipt-page">
    <div class="receipt-actions">
        <a href="{{ route('orders.show') }}" class="btn btn-secondary">&larr; Kembali ke pesanan</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak struk</button>
    </div>

    <article class="receipt" aria-labelledby="receipt-title">
        <header class="receipt-header">
            <h1 id="receipt-title">Struk Pesanan</h1>
            <p class="receipt-shop">{{ $order->shop_name_snapshot }}</p>
            <p class="receipt-address">{{ $order->shop_address_snapshot }}</p>
        </header>

        <dl class="receipt-meta">
            <div><dt>Kode</dt><dd>{{ $order->order_code }}</dd></div>
            <div><dt>Waktu</dt><dd>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</dd></div>
            <div><dt>Pemesan</dt><dd>{{ $order->customer_name_snapshot }}</dd></div>
            <div><dt>Layanan</dt><dd>{{ $order->fulfillment_type === 'dine_in' ? 'Dine-in' . ($order->table_label ? ' · Meja ' . $order->table_label : '') : 'Pickup' }}</dd></div>
            <div><dt>Pembayaran</dt><dd>{{ $order->payment_method === 'cash' ? 'Tunai' : 'QRIS simulasi' }}</dd></div>
        </dl>

        <table class="receipt-items">
            <thead><tr><th scope="col">Item</th><th scope="col">Qty</th><th scope="col">Jumlah</th></tr></thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->name }}
                            @if($item->note)<span class="receipt-note">{{ $item->note }}</span>@endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="receipt-total"><span>Total</span><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></div>
        <p class="receipt-footnote">Nilai pada struk mengikuti data saat pesanan dibuat. Pembayaran QRIS pada sistem ini masih simulasi.</p>
    </article>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/orders.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/print.css') }}">
@endsection
