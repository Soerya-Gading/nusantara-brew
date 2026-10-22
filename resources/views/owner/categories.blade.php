@extends('layouts.dashboard')
@section('title', 'Kategori Menu — Nusantara Brew')
@section('topbar_title', 'Kategori menu')
@section('topbar_context', $shop->name)

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Kategori menu</h1>
        <p class="page-subtitle">Urutan menentukan susunan kategori pada katalog publik.</p>
    </div>
    <button type="button" class="btn btn-primary" disabled>Tambah kategori</button>
</header>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Kategori</th>
                <th scope="col">Jumlah menu</th>
                <th scope="col">Urutan</th>
                <th scope="col">Status</th>
                <th scope="col">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>
                        <strong>{{ $category->name }}</strong>
                        <span class="table-sub">{{ $category->description }}</span>
                    </td>
                    <td>{{ $category->menu_count }}</td>
                    <td>{{ $category->sort_order }}</td>
                    <td>
                        <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-neutral' }}">
                            {{ $category->is_active ? 'Aktif' : 'Diarsipkan' }}
                        </span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="btn btn-secondary btn-sm" disabled>Ubah</button>
                            <button type="button" class="btn btn-secondary btn-sm" disabled>{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p class="text-xs text-muted" style="margin-top:var(--space-3);">Kategori yang sudah memiliki menu tidak dihapus permanen; gunakan status arsip.</p>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
@endsection
