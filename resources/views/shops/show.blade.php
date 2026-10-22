@extends('layouts.app')

@section('title', $shop->name . ' — Nusantara Brew')

@section('content')
<div class="container">

    {{-- ─── Breadcrumb / Kembali ─────────────────────────────────────────── --}}
    <nav class="back-nav" aria-label="Navigasi balik">
        <a href="{{ route('shops.index') }}" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Toko
        </a>
    </nav>

    {{-- ─── Info Header Toko ─────────────────────────────────────────────── --}}
    <div class="shop-header-card">
        {{-- Logo placeholder teks --}}
        <div class="shop-header-logo shop-logo-placeholder" aria-hidden="true">
            {{ mb_strtoupper(mb_substr($shop->name, 0, 2)) }}
        </div>

        <div class="shop-header-info">
            <div class="shop-header-top">
                <h1 class="shop-header-name">{{ $shop->name }}</h1>
                @if($shop->is_accepting_orders)
                    <span class="badge badge-success">
                        <span class="badge-dot"></span>Menerima Pesanan
                    </span>
                @else
                    <span class="badge badge-neutral">
                        <span class="badge-dot"></span>Tutup Sementara
                    </span>
                @endif
            </div>

            <p class="shop-header-address">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 21c-4.5-4.5-7-8-7-11a7 7 0 0114 0c0 3-2.5 6.5-7 11z"/>
                    <circle cx="12" cy="10" r="2"/>
                </svg>
                {{ $shop->address }}
            </p>

            <p class="shop-header-desc">{{ $shop->description }}</p>
        </div>
    </div>

    {{-- ─── Daftar Menu per Kategori ─────────────────────────────────────── --}}
    <section aria-labelledby="menu-heading">
        <h2 id="menu-heading" class="menu-section-title">Menu</h2>

        @forelse($groupedMenus as $categoryId => $items)
            @php $category = $categories->get($categoryId) @endphp

            <div class="menu-category-block">
                <h3 class="menu-category-heading">
                    {{ $category ? $category->name : 'Lainnya' }}
                </h3>

                <div class="menu-grid" role="list">
                    @foreach($items as $menu)
                    <article class="menu-card{{ ! $menu->is_available ? ' menu-card--unavailable' : '' }}"
                             id="menu-{{ $menu->id }}" role="listitem">

                        <div class="menu-card-body">
                            <div class="menu-card-top">
                                <h4 class="menu-card-name">{{ $menu->name }}</h4>
                                @if(! $menu->is_available)
                                    <span class="badge badge-neutral menu-habis-badge">Habis</span>
                                @endif
                            </div>

                            <p class="menu-card-desc">{{ $menu->description }}</p>

                            <div class="menu-card-footer">
                                <span class="menu-price">
                                    Rp {{ number_format($menu->price, 0, ',', '.') }}
                                </span>
                                @if($menu->is_available && $menu->stock > 0)
                                    <span class="menu-stock-tag">Stok: {{ $menu->stock }}</span>
                                @endif
                            </div>

                            <button type="button" class="menu-detail-trigger"
                                    data-menu-dialog
                                    data-name="{{ $menu->name }}"
                                    data-description="{{ $menu->description }}"
                                    data-price="Rp {{ number_format($menu->price, 0, ',', '.') }}"
                                    data-stock="{{ $menu->stock }}"
                                    data-available="{{ $menu->is_available && $menu->stock > 0 ? '1' : '0' }}">
                                Lihat detail
                            </button>
                        </div>

                    </article>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="menu-empty-state">
                <p>Menu belum tersedia untuk kedai ini.</p>
            </div>
        @endforelse
    </section>

    <dialog class="menu-dialog" id="menu-dialog" aria-labelledby="menu-dialog-name">
        <div class="menu-dialog-inner">
            <button type="button" class="menu-dialog-close" id="menu-dialog-close" aria-label="Tutup detail menu">&times;</button>
            <h3 id="menu-dialog-name" class="menu-dialog-name"></h3>
            <p id="menu-dialog-description" class="menu-dialog-description"></p>
            <div class="menu-dialog-meta">
                <strong id="menu-dialog-price" class="menu-dialog-price"></strong>
                <span id="menu-dialog-stock" class="menu-stock-tag"></span>
            </div>
            <p id="menu-dialog-note" class="menu-dialog-note" hidden>Menu ini sedang tidak tersedia.</p>
            {{-- Aktifkan setelah keranjang backend tersedia. --}}
            <button type="button" class="btn btn-primary btn-block" id="menu-dialog-add" disabled>Tambah ke keranjang</button>
            <p class="menu-dialog-hint">Keranjang hanya bisa memuat menu dari satu kedai.</p>
        </div>
    </dialog>

</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/menu-dialog.js') }}" defer></script>
@endsection

@section('styles')
<style>
/* ─── Navigasi balik ─────────────────────────────────────────────────────── */
.back-nav {
    margin-bottom: var(--space-5);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--color-ink-muted);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.back-link:hover {
    color: var(--color-primary);
    text-decoration: none;
}

/* ─── Header toko: logo placeholder ─────────────────────────────────────── */
.shop-logo-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--color-primary-light);
    color: var(--color-primary);
    font-size: 1.5rem;
    font-weight: var(--font-weight-bold);
    letter-spacing: -0.02em;
    user-select: none;
}

/* Baris atas: nama + badge sejajar */
.shop-header-top {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    flex-wrap: wrap;
    margin-bottom: var(--space-2);
}

.shop-header-name {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-ink);
}

/* Alamat toko */
.shop-header-address {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    color: var(--color-ink-muted);
    margin-bottom: var(--space-3);
}

/* Deskripsi toko */
.shop-header-desc {
    font-size: var(--font-size-sm);
    color: var(--color-ink-muted);
    line-height: var(--line-height-relaxed);
    max-width: 600px;
}

/* Responsive: tumpuk logo di mobile */
@media (max-width: 540px) {
    .shop-header-card {
        flex-direction: column;
        gap: var(--space-4);
    }
    .shop-header-logo {
        width: 60px !important;
        height: 60px !important;
        font-size: 1.1rem !important;
    }
}

/* ─── Judul seksi menu ───────────────────────────────────────────────────── */
.menu-section-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--color-ink);
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-3);
    border-bottom: 1px solid var(--color-border);
}

/* ─── Blok kategori ──────────────────────────────────────────────────────── */
.menu-category-block {
    margin-bottom: var(--space-8);
}

.menu-category-heading {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--color-ink-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-size: 0.75rem;
    margin-bottom: var(--space-4);
}

/* ─── Menu card: atas (nama + badge habis) ───────────────────────────────── */
.menu-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-2);
    margin-bottom: var(--space-1);
}

.menu-habis-badge {
    flex-shrink: 0;
    margin-top: 2px;
}

/* Kartu menu habis: sedikit redup */
.menu-card--unavailable {
    opacity: 0.6;
}

.menu-card--unavailable .menu-card-name {
    text-decoration: line-through;
    text-decoration-color: var(--color-ink-subtle);
}

/* ─── Empty state menu ───────────────────────────────────────────────────── */
.menu-empty-state {
    padding: var(--space-10) 0;
    text-align: center;
    color: var(--color-ink-muted);
    font-size: var(--font-size-sm);
}

/* ─── Pemicu dialog detail menu ──────────────────────────────────────────── */
.menu-detail-trigger {
    margin-top: var(--space-3);
    padding: 0;
    background: none;
    border: 0;
    font: inherit;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--color-primary);
    cursor: pointer;
    min-height: 32px;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.menu-detail-trigger:hover {
    color: var(--color-primary-dark, var(--color-primary));
}

.menu-detail-trigger:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

/* ─── Dialog detail menu ─────────────────────────────────────────────────── */
.menu-dialog {
    width: min(440px, calc(100vw - 32px));
    padding: 0;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    background: var(--color-surface);
    color: var(--color-ink);
}

.menu-dialog::backdrop {
    background: rgba(28, 22, 18, .45);
}

.menu-dialog-inner {
    padding: clamp(20px, 5vw, 28px);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.menu-dialog-close {
    align-self: flex-end;
    background: none;
    border: 0;
    font-size: 1.5rem;
    line-height: 1;
    color: var(--color-ink-muted);
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
}

.menu-dialog-name {
    font-size: 1.15rem;
    font-weight: var(--font-weight-semibold);
}

.menu-dialog-description {
    font-size: var(--font-size-sm);
    color: var(--color-ink-muted);
    line-height: var(--line-height-relaxed);
}

.menu-dialog-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-3);
    padding: var(--space-3) 0;
    border-top: 1px solid var(--color-border);
    border-bottom: 1px solid var(--color-border);
}

.menu-dialog-price {
    font-size: 1.05rem;
    color: var(--color-primary);
}

.menu-dialog-note {
    font-size: var(--font-size-sm);
    color: var(--color-danger-text, #b3261e);
}

.menu-dialog-hint {
    font-size: var(--font-size-xs);
    color: var(--color-ink-subtle);
    text-align: center;
}
</style>
@endsection
