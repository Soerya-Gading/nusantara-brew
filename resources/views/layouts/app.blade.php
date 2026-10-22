<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nusantara Brew — Satu Platform untuk Semua Rasa Lokal')</title>
    <meta name="description" content="Pesan kopi langsung dari coffee shop lokal pilihan. Ambil sendiri (pickup) atau nikmati di tempat (dine-in).">

    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/customer.css') }}">
    @yield('styles')
</head>
<body>
    @include('partials.navbar')

    <main id="main-content" class="main-content">
        @yield('content')
    </main>

    @include('partials.footer')

    @yield('scripts')
</body>
</html>
