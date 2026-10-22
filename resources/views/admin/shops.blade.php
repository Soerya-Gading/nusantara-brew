@extends('layouts.dashboard')
@section('title', 'Persetujuan Kedai — Nusantara Brew')
@section('topbar_title', 'Persetujuan kedai')

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Persetujuan kedai</h1>
        <p class="page-subtitle">Kedai nonaktif tetap menyimpan riwayat pesanan.</p>
    </div>
</header>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Kedai</th>
                <th scope="col">Pemilik</th>
                <th scope="col">Diajukan</th>
                <th scope="col">Status</th>
                <th scope="col">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shops as $shop)
                <tr>
                    <td>
                        <strong>{{ $shop->name }}</strong>
                        <span class="table-sub">{{ $shop->address }}</span>
                    </td>
                    <td>{{ $shop->owner_name }}</td>
                    <td>{{ $shop->submitted_at }}</td>
                    <td>
                        <span class="badge {{ $shop->status === 'active' ? 'badge-success' : ($shop->status === 'pending' ? 'badge-warning' : 'badge-neutral') }}">
                            {{ $shop->status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="row-actions">
                            @if($shop->status === 'pending')
                                <button type="button" class="btn btn-primary btn-sm" disabled>Aktifkan</button>
                                <button type="button" class="btn btn-secondary btn-sm" disabled>Tolak</button>
                            @elseif($shop->status === 'active')
                                <button type="button" class="btn btn-secondary btn-sm" disabled>Nonaktifkan</button>
                            @else
                                <button type="button" class="btn btn-secondary btn-sm" disabled>Aktifkan kembali</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
@endsection
