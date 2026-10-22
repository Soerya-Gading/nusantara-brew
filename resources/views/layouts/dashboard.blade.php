<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel — Nusantara Brew')</title>

    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    @yield('styles')
</head>
<body>
<div class="dashboard-layout">
    @include('partials.dashboard-sidebar')

    <div class="dash-main">
        <header class="dash-topbar">
            <div class="topbar-left">
                <button type="button" class="topbar-toggle" id="sidebar-toggle" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="dash-sidebar">&#9776;</button>
                <div>
                    <strong class="text-sm">@yield('topbar_title', 'Panel')</strong>
                    @hasSection('topbar_context')
                        <span class="text-xs text-muted">&middot; @yield('topbar_context')</span>
                    @endif
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ url('/') }}" class="text-sm">Lihat situs</a>
            </div>
        </header>

        <main class="dash-content" id="main-content">
            @yield('content')
        </main>
    </div>
</div>

<div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>

<script src="{{ asset('assets/js/dashboard.js') }}" defer></script>
@yield('scripts')
</body>
</html>
