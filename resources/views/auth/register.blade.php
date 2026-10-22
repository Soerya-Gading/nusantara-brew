@extends('layouts.app')

@section('title', 'Daftar Akun — Nusantara Brew')

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
        <h1 class="auth-title">Buat akun pelanggan</h1>
        <p class="auth-subtitle">Daftar untuk memesan dari kedai kopi pilihan Anda.</p>

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
            controller autentikasi (AuthController@register) dan POST route tersedia.
            Saat ini form menggunakan action="#" dan tombol disabled agar
            tidak mengirim data ke URL maupun endpoint palsu.
        --}}
        <form
            method="POST"
            action="#"
            id="register-form"
            novalidate
            onsubmit="return false;"
        >
            @csrf

            {{-- Nama Lengkap --}}
            <div class="form-group">
                <label for="reg-name" class="form-label">
                    Nama Lengkap <span class="required" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="reg-name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    placeholder="Contoh: Rahmat Hidayat"
                    autocomplete="name"
                    required
                    aria-describedby="reg-name-error"
                    disabled
                >
                @error('name')
                    <span id="reg-name-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="reg-email" class="form-label">
                    Alamat Email <span class="required" aria-hidden="true">*</span>
                </label>
                <input
                    type="email"
                    id="reg-email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    required
                    aria-describedby="reg-email-error"
                    disabled
                >
                @error('email')
                    <span id="reg-email-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            {{-- Nomor Telepon --}}
            <div class="form-group">
                <label for="reg-phone" class="form-label">
                    Nomor Telepon <span class="required" aria-hidden="true">*</span>
                </label>
                <input
                    type="tel"
                    id="reg-phone"
                    name="phone"
                    class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone') }}"
                    placeholder="08xxxxxxxxxx"
                    autocomplete="tel"
                    required
                    aria-describedby="reg-phone-error"
                    disabled
                >
                @error('phone')
                    <span id="reg-phone-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
                <span class="form-hint">Digunakan untuk konfirmasi pesanan.</span>
            </div>

            {{-- Password & Konfirmasi (dua kolom di layar lebar) --}}
            <div class="auth-password-row">
                <div class="form-group">
                    <label for="reg-password" class="form-label">
                        Kata Sandi <span class="required" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="password"
                        id="reg-password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                        required
                        aria-describedby="reg-password-error"
                        disabled
                    >
                    @error('password')
                        <span id="reg-password-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reg-password-confirm" class="form-label">
                        Konfirmasi Kata Sandi <span class="required" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="password"
                        id="reg-password-confirm"
                        name="password_confirmation"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        placeholder="Ulangi kata sandi"
                        autocomplete="new-password"
                        required
                        aria-describedby="reg-password-confirm-error"
                        disabled
                    >
                    @error('password_confirmation')
                        <span id="reg-password-confirm-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>
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
                Buat Akun
            </button>
        </form>

        {{-- ─── Tautan silang ke login ───────────────────────────────────── --}}
        <p class="auth-switch">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold">Masuk</a>
        </p>

    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
@endsection
