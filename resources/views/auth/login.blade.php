@extends('layouts.app')

@section('title', 'Masuk Akun — Nusantara Brew')

@section('content')
<div class="container-sm">
    <div class="auth-card card">

        {{-- ─── Merek ────────────────────────────────────────────────────── --}}
        <div class="auth-brand">
            <a href="{{ route('shops.index') }}" class="brand auth-brand-link" aria-label="Kembali ke Nusantara Brew">
                <div class="brand-logo-icon">NB</div>
                <span class="brand-name">Nusantara Brew</span>
            </a>
        </div>

        {{-- ─── Judul ────────────────────────────────────────────────────── --}}
        <h1 class="auth-title">Masuk ke akun Anda</h1>
        <p class="auth-subtitle">Selamat datang kembali. Silakan masukkan email dan kata sandi Anda.</p>

        {{-- ─── Pesan Validasi (akan diisi setelah integrasi autentikasi) ── --}}
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul style="margin: 0; padding-left: var(--space-4);">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{--
            Form action dan tombol submit akan diaktifkan setelah integrasi
            controller autentikasi (AuthController@login) dan POST route tersedia.
            Saat ini form menggunakan action="#" dan tombol disabled agar
            tidak mengirim data ke URL maupun endpoint palsu.
        --}}
        <form
            method="POST"
            action="#"
            id="login-form"
            novalidate
            onsubmit="return false;"
        >
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label for="login-email" class="form-label">
                    Alamat Email <span class="required" aria-hidden="true">*</span>
                </label>
                <input
                    type="email"
                    id="login-email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    required
                    aria-describedby="login-email-error"
                    disabled
                >
                @error('email')
                    <span id="login-email-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="login-password" class="form-label">
                    Kata Sandi <span class="required" aria-hidden="true">*</span>
                </label>
                <input
                    type="password"
                    id="login-password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                    aria-describedby="login-password-error"
                    disabled
                >
                @error('password')
                    <span id="login-password-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            {{--
                Tombol dinonaktifkan (disabled) sampai POST route dan controller
                autentikasi tersedia. Hapus atribut `disabled` saat integrasi.
            --}}
            <button
                type="submit"
                class="btn btn-primary btn-block btn-lg"
                disabled
                aria-disabled="true"
                style="margin-top: var(--space-2);"
            >
                Masuk
            </button>
        </form>

        {{-- ─── Tautan silang ke registrasi ─────────────────────────────── --}}
        <p class="auth-switch">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold">Daftar sekarang</a>
        </p>

    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
@endsection
