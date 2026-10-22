@extends('layouts.dashboard')
@section('title', 'Ringkasan Platform — Nusantara Brew')
@section('topbar_title', 'Ringkasan platform')

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Ringkasan platform</h1>
        <p class="page-subtitle">Persetujuan kedai dan akun berhak khusus. Admin tidak mengelola transaksi kedai.</p>
    </div>
</header>

<section class="metrics-grid" aria-label="Ringkasan platform">
    <article class="metric-card">
        <span class="metric-label">Kedai aktif</span>
        <strong class="metric-value">{{ $summary['active_shops'] }}</strong>
        <span class="metric-hint">Tampil di katalog publik</span>
    </article>
    <article class="metric-card">
        <span class="metric-label">Menunggu persetujuan</span>
        <strong class="metric-value">{{ $summary['pending_shops'] }}</strong>
        <span class="metric-hint">Perlu diperiksa</span>
    </article>
    <article class="metric-card">
        <span class="metric-label">Akun terdaftar</span>
        <strong class="metric-value">{{ $summary['users'] }}</strong>
        <span class="metric-hint">Pelanggan dan akun operasional</span>
    </article>
</section>

<section class="card dash-panel">
    <div class="panel-heading">
        <h2 class="panel-title">Kedai menunggu persetujuan</h2>
        <a href="{{ route('admin.shops') }}" class="btn btn-secondary btn-sm">Kelola</a>
    </div>
    @forelse($pending as $shop)
        <div class="list-row">
            <div>
                <strong>{{ $shop->name }}</strong>
                <p class="text-xs text-muted">{{ $shop->address }} &middot; diajukan {{ $shop->submitted_at }}</p>
            </div>
            <span class="badge badge-warning">Menunggu persetujuan</span>
        </div>
    @empty
        <p class="text-sm text-muted">Tidak ada pengajuan kedai baru.</p>
    @endforelse
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}">
@endsection
