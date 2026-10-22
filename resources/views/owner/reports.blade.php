@extends('layouts.dashboard')
@section('title', 'Laporan Penjualan — Nusantara Brew')
@section('topbar_title', 'Laporan penjualan')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Laporan penjualan</h1>
        <p class="page-subtitle">Hanya pesanan selesai dan sudah dibayar yang dihitung.</p>
    </div>
</header>

<form method="GET" action="{{ route('owner.reports') }}" class="menu-filter">
    <div class="form-group" style="margin:0;flex:1 1 200px;">
        <label for="from" class="form-label">Dari tanggal</label>
        <input id="from" type="date" name="from" class="form-control" value="{{ $from }}">
    </div>
    <div class="form-group" style="margin:0;flex:1 1 200px;">
        <label for="to" class="form-label">Sampai tanggal</label>
        <input id="to" type="date" name="to" class="form-control" value="{{ $to }}">
    </div>
    <button type="submit" class="btn btn-secondary">Terapkan</button>
</form>

<section class="metrics-grid" aria-label="Ringkasan laporan">
    <article class="metric-card">
        <span class="metric-label">Pesanan selesai</span>
        <strong class="metric-value">{{ $report['orders'] }}</strong>
        <span class="metric-hint">Periode {{ $from }} sampai {{ $to }}</span>
    </article>
    <article class="metric-card">
        <span class="metric-label">Omzet pesanan selesai</span>
        <strong class="metric-value">Rp {{ number_format($report['revenue'], 0, ',', '.') }}</strong>
        <span class="metric-hint">Bukan laba</span>
    </article>
    <article class="metric-card">
        <span class="metric-label">Porsi terjual</span>
        <strong class="metric-value">{{ $report['items'] }}</strong>
        <span class="metric-hint">Dari item pesanan selesai</span>
    </article>
</section>

<div class="table-wrap">
    <table class="table">
        <caption class="sr-only">Menu terjual pada periode laporan</caption>
        <thead>
            <tr><th scope="col">Menu</th><th scope="col">Porsi terjual</th><th scope="col">Nilai</th></tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['quantity'] }}</td>
                    <td>Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada pesanan selesai pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="text-xs text-muted" style="margin-top:var(--space-3);">Laporan memakai waktu penyelesaian pesanan dan zona waktu WIB.</p>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
@endsection
