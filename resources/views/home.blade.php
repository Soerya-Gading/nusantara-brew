@extends('layouts.app')

@section('title', 'Nusantara Brew — Satu Platform untuk Semua Rasa Lokal')

@section('content')
<div class="container">

    {{-- ─── Hero ─────────────────────────────────────────────────────────── --}}
    <section class="editorial-hero" style="padding-top: var(--space-8); padding-bottom: var(--space-4);">
        <h1 class="editorial-hero-title">Temukan seduhan pilihanmu.</h1>
        <p class="editorial-hero-desc">
            Jelajahi coffee shop lokal pilihan dalam satu wadah. Pesan untuk diambil sendiri (pickup) atau nikmati langsung di tempat (dine-in).
        </p>
    </section>

    {{-- ─── Form Pencarian ──────────────────────────────────────────────── --}}
    <section class="shops-search-section" aria-label="Cari kedai kopi">
        <form method="GET" action="{{ route('shops.index') }}" role="search" class="search-form">
            <label for="shop-search" class="sr-only">Cari nama kedai kopi</label>
            <div class="search-bar-wrap">
                <span class="search-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </span>
                <input
                    type="search"
                    id="shop-search"
                    name="q"
                    class="form-control search-input"
                    placeholder="Cari nama kedai kopi..."
                    value="{{ $query }}"
                    autocomplete="off"
                    spellcheck="false"
                >
                @if($query !== '')
                    <a href="{{ route('shops.index') }}" class="search-clear-btn" aria-label="Hapus pencarian">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- ─── Header Daftar Toko ──────────────────────────────────────────── --}}
    <section aria-labelledby="stores-heading" class="shops-list-section">
        <div class="shops-list-header">
            <h2 id="stores-heading">
                @if($query !== '')
                    Hasil pencarian untuk &ldquo;{{ $query }}&rdquo;
                @else
                    Kedai Kopi Terdaftar
                @endif
            </h2>
            <span class="text-sm text-muted">
                {{ $shops->count() }} kedai aktif
            </span>
        </div>

        {{-- ─── Daftar ─────────────────────────────────────────────────── --}}
        @forelse($shops as $shop)
            @if($loop->first)
            <div class="store-grid" role="list">
            @endif

            <article class="store-card" id="store-{{ $shop->id }}" role="listitem">
                {{-- Placeholder: inisial dua huruf nama toko --}}
                <div class="store-card-img-wrap store-card-placeholder" aria-hidden="true">
                    <div class="store-placeholder-icon">{{ mb_strtoupper(mb_substr($shop->name, 0, 2)) }}</div>
                    <div class="store-card-badge">
                        @if($shop->is_accepting_orders)
                            <span class="badge badge-success">
                                <span class="badge-dot"></span>
                                Menerima Pesanan
                            </span>
                        @else
                            <span class="badge badge-neutral">
                                <span class="badge-dot"></span>
                                Tutup Sementara
                            </span>
                        @endif
                    </div>
                </div>

                <div class="store-card-body">
                    <h3 class="store-card-name">{{ $shop->name }}</h3>
                    <p class="store-card-address">{{ $shop->address }}</p>
                    <p class="text-sm" style="color: var(--color-ink-muted); line-height: var(--line-height-relaxed); flex: 1;">
                        {{ $shop->description }}
                    </p>
                    <div class="store-card-hours" style="margin-top: var(--space-4);">
                        <a href="{{ route('shops.show', $shop->slug) }}" class="store-menu-link">
                            Lihat Menu
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>

            @if($loop->last)
            </div>{{-- /.store-grid --}}
            @endif

        @empty
            {{-- ─── Keadaan Kosong ──────────────────────────────────────── --}}
            <div class="shops-empty-state" role="status">
                <div class="shops-empty-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </div>
                <h3>Tidak ada kedai yang cocok</h3>
                <p>Tidak ada kedai kopi dengan nama &ldquo;{{ $query }}&rdquo;. Coba kata kunci lain.</p>
                <a href="{{ route('shops.index') }}" class="btn btn-secondary" style="margin-top: var(--space-4);">
                    Tampilkan Semua Kedai
                </a>
            </div>
        @endforelse
    </section>

</div>
@endsection

@section('styles')
<style>
/* ─── Pencarian ──────────────────────────────────────────────────────────── */
.shops-search-section {
    margin-bottom: var(--space-6);
}

.search-form {
    max-width: 480px;
}

/* Tombol hapus pencarian di dalam search bar */
.search-clear-btn {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-ink-subtle);
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: color var(--transition-fast);
}

.search-clear-btn:hover {
    color: var(--color-ink);
}

/* ─── Header bagian toko ─────────────────────────────────────────────────── */
.shops-list-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
    flex-wrap: wrap;
}

.shops-list-header h2 {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--color-ink);
}

/* ─── Placeholder gambar toko ───────────────────────────────────────────── */
.store-card-placeholder {
    background-color: var(--color-primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
}

.store-placeholder-icon {
    font-size: 1.6rem;
    font-weight: var(--font-weight-bold);
    color: var(--color-primary);
    opacity: 0.4;
    letter-spacing: -0.03em;
    user-select: none;
}

/* ─── Tautan "Lihat Menu" di store-card ──────────────────────────────────── */
.store-menu-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--color-primary);
    text-decoration: none;
    transition: color var(--transition-fast), gap var(--transition-fast);
}

.store-menu-link:hover {
    color: var(--color-primary-hover);
    gap: var(--space-2);
    text-decoration: none;
}

/* ─── Empty state ────────────────────────────────────────────────────────── */
.shops-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--space-3);
    padding: var(--space-12) var(--space-4);
    background-color: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    max-width: 420px;
    margin: 0 auto;
}

.shops-empty-icon {
    color: var(--color-ink-subtle);
    opacity: 0.6;
}

.shops-empty-state h3 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--color-ink);
}

.shops-empty-state p {
    font-size: var(--font-size-sm);
    color: var(--color-ink-muted);
    max-width: 320px;
}
</style>
@endsection
