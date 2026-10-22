<header class="site-header">
    <div class="container header-inner">
        <a href="{{ url('/') }}" class="brand" aria-label="Nusantara Brew Beranda">
            <div class="brand-logo-icon">NB</div>
            <div class="brand-text-wrap">
                <span class="brand-name">Nusantara Brew</span>
                <span class="brand-tagline">Satu platform untuk semua rasa lokal</span>
            </div>
        </a>

        <nav class="site-nav public-site-nav" aria-label="Navigasi Utama">
            <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Beranda</a>
            <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login', 'register') ? 'active' : '' }}">Masuk</a>
        </nav>
    </div>
</header>
