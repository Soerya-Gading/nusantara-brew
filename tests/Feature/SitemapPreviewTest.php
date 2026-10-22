<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitemapPreviewTest extends TestCase
{
    public function test_sitemap_links_every_page(): void
    {
        $response = $this->get('/peta')->assertOk()
            ->assertSee('Peta halaman')
            ->assertSee('/owner/laporan')
            ->assertSee('/kasir')
            ->assertSee('/admin/pengguna')
            ->assertSee('/toko/kopi-lembur-bandung');

        // Setiap URL halaman yang ditampilkan harus benar-benar bisa dibuka.
        // Berkas /assets/* dilayani web server, bukan router Laravel.
        preg_match_all('#href="https?://[^/"]+(?::\d+)?([^"]*)"#', $response->getContent(), $matches);
        $paths = array_values(array_filter(
            array_unique($matches[1]),
            fn ($path) => $path !== '' && ! str_starts_with($path, '/assets/')
        ));
        $this->assertNotEmpty($paths);

        foreach ($paths as $path) {
            $this->assertSame(200, $this->get($path)->getStatusCode(), 'Halaman gagal dibuka: '.$path);
        }
    }

    public function test_sitemap_is_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/peta')->assertNotFound();
    }
}
