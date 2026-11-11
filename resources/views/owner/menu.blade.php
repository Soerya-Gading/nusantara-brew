@extends('layouts.dashboard')
@section('title', 'Katalog & Stok — Nusantara Brew')
@section('topbar_title', 'Katalog & stok')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Katalog &amp; stok</h1>
        <p class="page-subtitle">Stok menunjukkan porsi yang masih bisa dijual, bukan bahan baku.</p>
    </div>
    <!-- disabled -->
    <button type="button" class="btn btn-primary" onclick="document.getElementById('add-menu-dialog').showModal()">Tambah menu</button>
</header>

<dialog id="add-menu-dialog" style="padding:var(--space-4);border-radius:var(--radius-md);border:1px solid var(--color-border);max-width:500px;width:100%;">
    <form method="POST" action="{{ route('owner.menu.store') }}">
        @csrf
        <h3 style="margin-top:0;">Tambah Menu Baru</h3>
        <div class="form-group">
            <label class="form-label" for="menu-name">Nama Menu</label>
            <input class="form-control" id="menu-name" name="name" required placeholder="Contoh: Kopi Tubruk">
        </div>
        <div class="form-group">
            <label class="form-label" for="menu-category">Kategori</label>
            <select class="form-control" id="menu-category" name="category_id" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="menu-price">Harga (Rp)</label>
            <input class="form-control" id="menu-price" name="price" type="number" min="1" required placeholder="25000">
        </div>
        <div class="form-group">
            <label class="form-label" for="menu-stock">Stok Awal (Porsi)</label>
            <input class="form-control" id="menu-stock" name="stock" type="number" min="0" required value="10">
        </div>
        <div class="form-group">
            <label class="form-label" for="menu-desc">Deskripsi</label>
            <textarea class="form-control" id="menu-desc" name="description" rows="2" placeholder="Deskripsi menu..."></textarea>
        </div>
        <div style="display:flex;gap:var(--space-2);justify-content:flex-end;margin-top:var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-menu-dialog').close()">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Menu</button>
        </div>
    </form>
</dialog>

<form method="GET" action="{{ route('owner.menu') }}" class="menu-filter">
    <label for="q" class="sr-only">Cari menu</label>
    <input id="q" name="q" class="form-control" value="{{ $query }}" placeholder="Cari nama menu">
    <label for="category" class="sr-only">Kategori</label>
    <select id="category" name="category" class="form-control">
        <option value="">Semua kategori</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary">Terapkan</button>
</form>

<div class="table-wrap">
    <table class="table">
        <caption class="sr-only">Daftar menu kedai</caption>
        <thead>
            <tr>
                <th scope="col">Menu</th>
                <th scope="col">Kategori</th>
                <th scope="col">Harga</th>
                <th scope="col">Stok</th>
                <th scope="col">Status</th>
                <th scope="col">Tambah stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menus as $menu)
                <tr>
                    <td>
                        <strong>{{ $menu->name }}</strong>
                        <span class="table-sub">{{ \Illuminate\Support\Str::limit($menu->description, 70) }}</span>
                    </td>
                    <td>{{ $menu->category_name }}</td>
                    <td>Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                    <td>{{ $menu->stock }}</td>
                    <td>
                        @if($menu->stock === 0)
                            <span class="badge badge-danger">Habis</span>
                        @elseif(!$menu->is_available)
                            <span class="badge badge-warning">Tidak dijual</span>
                        @else
                            <span class="badge badge-success">Tersedia</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('owner.menu.restock', $menu->id) }}" class="restock-form">
                            @csrf
                            <label class="sr-only" for="restock-{{ $menu->id }}">Jumlah tambahan stok {{ $menu->name }}</label>
                            <input id="restock-{{ $menu->id }}" type="number" min="1" max="999" value="1" name="amount">
                            <button type="submit" class="btn btn-secondary btn-sm">Tambah</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada menu yang cocok dengan pencarian.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-muted" style="margin-top:var(--space-3);">Menambah stok memakai penambahan saldo, bukan menimpa angka lama. Menu yang sudah pernah dipesan tidak dihapus permanen.</p>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
@endsection
