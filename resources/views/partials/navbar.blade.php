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
            @auth
                <a href="{{ route('cart.index') }}" class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}">Keranjang</a>
                <a href="{{ route('orders.history') }}" class="nav-link {{ request()->routeIs('orders.history') ? 'active' : '' }}">Riwayat</a>
                @if(auth()->user()->isOwner())
                    <a href="{{ route('owner.dashboard') }}" class="nav-link {{ request()->routeIs('owner.*') ? 'active' : '' }}">Panel Pemilik</a>
                @elseif(auth()->user()->isStaff())
                    <a href="{{ route('staff.queue') }}" class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">Panel Staf</a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Panel Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="display:inline;margin:0;padding:0;">
                    @csrf
                    <button type="submit" class="nav-link" style="background:none;border:none;cursor:pointer;font:inherit;display:inline-block;">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Masuk</a>
                <a href="{{ route('register') }}" class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}">Daftar</a>
            @endauth
        </nav>
    </div>
</header>
