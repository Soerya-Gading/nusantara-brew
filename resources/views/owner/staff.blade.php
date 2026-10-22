@extends('layouts.dashboard')
@section('title', 'Penugasan Staf — Nusantara Brew')
@section('topbar_title', 'Penugasan staf')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Penugasan staf</h1>
        <p class="page-subtitle">Satu akun staf hanya ditugaskan ke satu kedai.</p>
    </div>
</header>

<section class="card dash-panel" style="margin-bottom:var(--space-5);">
    <h2 class="panel-title">Tugaskan staf</h2>
    <form method="POST" action="#" onsubmit="return false;" class="inline-form">
        @csrf
        <div class="form-group" style="flex:1 1 260px;margin:0;">
            <label for="staff-email" class="form-label">Email akun staf</label>
            <input id="staff-email" name="email" type="email" class="form-control" placeholder="nama@email.com" autocomplete="off" disabled>
            <span class="form-hint">Masukkan email persis. Sistem tidak menampilkan daftar pengguna lain.</span>
        </div>
        <button type="submit" class="btn btn-primary" disabled>Tugaskan</button>
    </form>
</section>

<section class="card dash-panel">
    <h2 class="panel-title">Staf kedai ini</h2>
    @forelse($staff as $member)
        <div class="list-row">
            <div>
                <strong>{{ $member->name }}</strong>
                <p class="text-xs text-muted">{{ $member->email }} &middot; {{ $member->assigned_at }}</p>
            </div>
            <div class="row-actions">
                <span class="badge {{ $member->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                <button type="button" class="btn btn-secondary btn-sm" disabled>Lepas</button>
            </div>
        </div>
    @empty
        <p class="text-sm text-muted">Belum ada staf yang ditugaskan.</p>
    @endforelse

    <hr class="panel-divider">
    <h3 class="panel-title" style="font-size:.95rem;">Calon staf tanpa penugasan</h3>
    @foreach($candidates as $candidate)
        <div class="list-row">
            <div>
                <strong>{{ $candidate->name }}</strong>
                <p class="text-xs text-muted">{{ $candidate->email }}</p>
            </div>
            <span class="badge badge-neutral">Belum ditugaskan</span>
        </div>
    @endforeach
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}">
@endsection
