@extends('layouts.dashboard')
@section('title', 'Riwayat Kedai — Nusantara Brew')
@section('topbar_title', 'Riwayat kedai')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Riwayat pesanan</h1>
        <p class="page-subtitle">Pesanan selesai dan dibatalkan pada kedai penugasan Anda.</p>
    </div>
</header>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Kode</th>
                <th scope="col">Waktu</th>
                <th scope="col">Layanan</th>
                <th scope="col">Status</th>
                <th scope="col">Total</th>
                <th scope="col">Struk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->order_code }}</strong></td>
                    <td>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</td>
                    <td>{{ $order->fulfillment_type === 'dine_in' ? 'Dine-in' : 'Pickup' }}</td>
                    <td><span class="badge badge-success">{{ $order->status_label }}</span></td>
                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                    <td><a href="{{ route('orders.receipt') }}" class="text-sm">Lihat struk</a></td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada riwayat pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
@endsection
