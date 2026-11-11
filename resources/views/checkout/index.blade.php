@extends('layouts.app')
@section('title', 'Checkout — Nusantara Brew')

@section('content')
<div class="container">
    <header class="cart-page-heading">
        <h1>Checkout</h1>
        <p class="text-muted">Tentukan cara menikmati pesanan Anda.</p>
    </header>

    @if($errors->has('checkout'))
        <div class="alert alert-danger" style="margin-bottom: var(--space-4);">
            {{ $errors->first('checkout') }}
        </div>
    @endif

    <!-- onsubmit="return false;" disabled -->
    <form method="POST" action="{{ request()->routeIs('preview.checkout') ? '#' : route('checkout.store') }}" @if(request()->routeIs('preview.checkout')) onsubmit="return false;" @endif class="cart-layout" id="checkout-form">
        @csrf
        <div class="checkout-sections">
            <section class="card cart-panel">
                <h2>Cara menikmati pesanan</h2>
                <fieldset class="checkout-options">
                    <legend class="sr-only">Jenis pemesanan</legend>
                    <label><input type="radio" name="fulfillment_type" value="pickup" {{ old('fulfillment_type', 'pickup') === 'pickup' ? 'checked' : '' }}> <span><strong>Ambil sendiri</strong><small>Ambil pesanan langsung di kedai.</small></span></label>
                    <label><input type="radio" name="fulfillment_type" value="dine_in" {{ old('fulfillment_type') === 'dine_in' ? 'checked' : '' }}> <span><strong>Nikmati di tempat</strong><small>Pesan dari meja Anda di kedai.</small></span></label>
                </fieldset>
                <div class="form-group" id="table-field" hidden>
                    <label for="table-label" class="form-label">Nomor atau label meja</label>
                    <input id="table-label" class="form-control" name="table_label" maxlength="30" value="{{ old('table_label') }}" placeholder="Contoh: A3">
                </div>
            </section>
            <section class="card cart-panel">
                <h2>Kontak pemesan</h2>
                <div class="form-group">
                    <label for="customer-name" class="form-label">Nama lengkap</label>
                    <input id="customer-name" class="form-control" name="customer_name" autocomplete="name" maxlength="120" required value="{{ old('customer_name', auth()->user()?->name) }}">
                    @error('customer_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="customer-phone" class="form-label">Nomor telepon</label>
                    <input id="customer-phone" class="form-control" name="customer_phone" type="tel" autocomplete="tel" maxlength="30" required value="{{ old('customer_phone', auth()->user()?->phone) }}">
                    @error('customer_phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="customer-note" class="form-label">Catatan pesanan <span class="text-muted">(opsional)</span></label>
                    <textarea id="customer-note" class="form-control" name="customer_note" maxlength="500" rows="3">{{ old('customer_note') }}</textarea>
                </div>
            </section>
            <section class="card cart-panel">
                <h2>Metode pembayaran</h2>
                <fieldset class="checkout-options">
                    <legend class="sr-only">Pilih metode pembayaran</legend>
                    <label><input type="radio" name="payment_method" value="qris_simulation" {{ old('payment_method', 'qris_simulation') === 'qris_simulation' ? 'checked' : '' }}> <span><strong>QRIS simulasi</strong><small>SIMULASI PEMBAYARAN — bukan QRIS nyata.</small></span></label>
                    <label><input type="radio" name="payment_method" value="cash" {{ old('payment_method') === 'cash' ? 'checked' : '' }}> <span><strong>Tunai di kasir</strong><small>Bayar di kasir sebelum pesanan mulai dibuat.</small></span></label>
                </fieldset>
                <p class="text-sm text-muted">Selesaikan pembayaran dalam 15 menit setelah pesanan dibuat.</p>
            </section>
        </div>
        <aside class="card cart-panel cart-summary">
            <h2>Ringkasan pesanan</h2>
            <p class="text-muted">{{ $cart->shop_name }}</p>
            <dl>
                @foreach($cart->items as $item)
                    <div><dt>{{ $item->quantity }} × {{ $item->name }}</dt><dd>Rp {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</dd></div>
                @endforeach
                <div class="cart-summary-total"><dt>Total</dt><dd>Rp {{ number_format($cart->total_amount, 0, ',', '.') }}</dd></div>
            </dl>
            @if(request()->routeIs('preview.checkout'))
                <button type="submit" class="btn btn-primary btn-block" disabled>Buat Pesanan</button>
            @else
                <button type="submit" class="btn btn-primary btn-block">Buat Pesanan</button>
            @endif
        </aside>
    </form>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cart.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/checkout.css') }}">
@endsection
@section('scripts')
<script src="{{ asset('assets/js/checkout.js') }}" defer></script>
@endsection
