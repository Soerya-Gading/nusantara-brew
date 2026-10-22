@extends('layouts.app')
@section('title', 'Peta Halaman — Nusantara Brew')

@section('content')
<div class="container" style="padding-block:var(--space-6);">
    <header style="margin-bottom:var(--space-5);">
        <p class="order-eyebrow">Alat pengembangan</p>
        <h1 style="font-size:clamp(1.4rem,4vw,2rem);margin-bottom:var(--space-2);">Peta halaman</h1>
        <p class="text-sm text-muted">
            Halaman ini hanya tampil di lingkungan lokal dan pengujian. Tidak tersedia di produksi.
            Data masih berasal dari controller, belum dari database.
        </p>
    </header>

    @foreach($groups as $group)
        <section style="margin-bottom:var(--space-6);">
            <h2 style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--color-ink-muted);margin-bottom:var(--space-3);">
                {{ $group['title'] }}
            </h2>
            <div style="display:grid;gap:var(--space-3);grid-template-columns:repeat(auto-fill,minmax(min(100%,260px),1fr));">
                @foreach($group['pages'] as $page)
                    <a href="{{ $page['url'] }}" class="card" style="padding:var(--space-4);text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:4px;">
                        <strong style="font-size:var(--font-size-sm);">{{ $page['name'] }}</strong>
                        <code style="font-size:var(--font-size-xs);color:var(--color-ink-muted);overflow-wrap:anywhere;">{{ $page['path'] }}</code>
                        <span style="font-size:var(--font-size-xs);color:var(--color-ink-subtle);">{{ $page['note'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@endsection
