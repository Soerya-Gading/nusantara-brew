@extends('layouts.dashboard')
@section('title', 'Manajemen Pengguna — Nusantara Brew')
@section('topbar_title', 'Manajemen pengguna')

@section('content')
<header class="page-header">
    <div>
        <h1 class="page-title">Manajemen pengguna</h1>
        <p class="page-subtitle">Akun pelanggan hanya lahir dari registrasi publik.</p>
    </div>
</header>

<section class="card dash-panel" style="margin-bottom:var(--space-5);">
    <h2 class="panel-title">Buat akun owner atau staf</h2>
    <p class="text-sm text-muted">Akun operasional tidak bisa mendaftar sendiri. Admin membuatkannya, lalu pemilik kedai menugaskan staf ke kedainya.</p>
    <form method="POST" action="#" onsubmit="return false;" class="inline-form" style="margin-top:var(--space-4);">
        @csrf
        <div class="form-group" style="flex:1 1 220px;margin:0;">
            <label for="new-name" class="form-label">Nama</label>
            <input id="new-name" name="name" class="form-control" maxlength="120" disabled>
        </div>
        <div class="form-group" style="flex:1 1 220px;margin:0;">
            <label for="new-email" class="form-label">Email</label>
            <input id="new-email" name="email" type="email" class="form-control" maxlength="190" disabled>
        </div>
        <div class="form-group" style="flex:1 1 160px;margin:0;">
            <label for="new-role" class="form-label">Peran</label>
            <select id="new-role" name="role" class="form-control" disabled>
                <option value="owner">Owner</option>
                <option value="staff">Staf</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" disabled>Buat akun</button>
    </form>
</section>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Nama</th>
                <th scope="col">Email</th>
                <th scope="col">Peran</th>
                <th scope="col">Status</th>
                <th scope="col">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role_label }}</td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-neutral' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tables.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settings.css') }}">
@endsection
