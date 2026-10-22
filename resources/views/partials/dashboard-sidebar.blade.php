@php
    $account = $account ?? (object) ['name' => 'Hendra Wijaya', 'role_label' => 'Pemilik'];

    // Tautan aktif hanya untuk halaman yang sudah tersedia.
    $ownerNav = [
        ['label' => 'Ringkasan', 'route' => 'owner.dashboard'],
        ['label' => 'Antrean pesanan', 'route' => 'owner.orders'],
        ['label' => 'Katalog & stok', 'route' => 'owner.menu'],
        ['label' => 'Kategori menu', 'route' => 'owner.categories'],
        ['label' => 'Profil kedai', 'route' => 'owner.shop'],
        ['label' => 'Penugasan staf', 'route' => 'owner.staff'],
        ['label' => 'Laporan penjualan', 'route' => 'owner.reports'],
    ];
    $staffNav = [
        ['label' => 'Antrean pesanan', 'route' => 'staff.queue'],
        ['label' => 'Riwayat kedai', 'route' => 'staff.history'],
    ];
    $adminNav = [
        ['label' => 'Ringkasan platform', 'route' => 'admin.dashboard'],
        ['label' => 'Persetujuan kedai', 'route' => 'admin.shops'],
        ['label' => 'Manajemen pengguna', 'route' => 'admin.users'],
    ];

    $nav = $nav ?? $ownerNav;
@endphp

<aside class="dash-sidebar" id="dash-sidebar" aria-label="Navigasi panel">
    <div class="sidebar-header">
        <a href="{{ url('/') }}" class="sidebar-brand">
            <span class="brand-logo-icon" style="width:28px;height:28px;font-size:.85rem;">NB</span>
            <span>Nusantara Brew</span>
        </a>
        <span class="sidebar-role-badge">{{ $account->role_label }}</span>
    </div>

    <nav class="sidebar-nav">
        @foreach($nav as $item)
            @if(!empty($item['route']))
                <a href="{{ route($item['route']) }}"
                   class="sidebar-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                   @if(request()->routeIs($item['route'])) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @else
                <span class="sidebar-link is-pending" aria-disabled="true">
                    {{ $item['label'] }}
                    <span class="sidebar-pending">Segera</span>
                </span>
            @endif
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <p class="sidebar-user">{{ $account->name }}</p>
        {{-- Ganti dengan form POST logout setelah autentikasi tersedia. --}}
        <button type="button" class="sidebar-signout" disabled>Keluar</button>
    </div>
</aside>
