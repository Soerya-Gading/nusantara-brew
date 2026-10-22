@extends('layouts.dashboard')
@section('title', 'Profil Kedai — Nusantara Brew')
@section('topbar_title', 'Profil kedai')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Profil kedai</h1>
        <p class="page-subtitle">Informasi ini tampil pada katalog publik.</p>
    </div>
    <span class="badge {{ $shop->status === 'active' ? 'badge-success' : 'badge-warning' }}">
        {{ $shop->status === 'active' ? 'Disetujui platform' : 'Menunggu persetujuan' }}
    </span>
</header>

<div class="settings-grid">
    <section class="card dash-panel">
        <h2 class="panel-title">Informasi kedai</h2>
        <form method="POST" action="#" onsubmit="return false;">
            @csrf
            <div class="form-group">
                <label for="shop-name" class="form-label">Nama kedai</label>
                <input id="shop-name" name="name" class="form-control" value="{{ $shop->name }}" maxlength="150" disabled>
            </div>
            <div class="form-group">
                <label for="shop-address" class="form-label">Alamat</label>
                <input id="shop-address" name="address" class="form-control" value="{{ $shop->address }}" maxlength="255" disabled>
            </div>
            <div class="form-group">
                <label for="shop-hours" class="form-label">Jam buka</label>
                <input id="shop-hours" name="opening_hours_text" class="form-control" value="{{ $shop->opening_hours_text }}" maxlength="120" disabled>
                <span class="form-hint">Informasi saja; status menerima pesanan diatur terpisah.</span>
            </div>
            <div class="form-group">
                <label for="shop-description" class="form-label">Deskripsi</label>
                <textarea id="shop-description" name="description" class="form-control" rows="4" disabled>{{ $shop->description }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary" disabled>Simpan perubahan</button>
        </form>
    </section>

    <section class="card dash-panel">
        <h2 class="panel-title">Penerimaan pesanan</h2>
        <p class="text-sm text-muted">Menutup penerimaan menghentikan pesanan baru. Pesanan yang sudah dibayar tetap harus diselesaikan.</p>
        <div class="switch-row">
            <div>
                <strong>{{ $shop->is_accepting_orders ? 'Menerima pesanan' : 'Tidak menerima pesanan' }}</strong>
                <p class="text-xs text-muted">Diperbarui {{ $shop->updated_at }} WIB</p>
            </div>
            <button type="button" class="btn btn-secondary" disabled>{{ $shop->is_accepting_orders ? 'Tutup penerimaan' : 'Buka penerimaan' }}</button>
        </div>
        <hr class="panel-divider">
        <p class="text-xs text-muted">Status persetujuan platform hanya dapat diubah administrator.</p>
    </section>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}">
@endsection
