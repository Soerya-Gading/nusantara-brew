<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardBatchPreviewTest extends TestCase
{
    public function test_owner_categories_page(): void
    {
        $this->get('/owner/kategori')->assertOk()
            ->assertSee('Kategori menu')
            ->assertSee('Minuman Kopi')
            ->assertSee('Makanan &amp; Camilan', false)
            ->assertSee('Urutan')
            ->assertSee('disabled', false);
    }

    public function test_owner_shop_page_has_order_acceptance_toggle(): void
    {
        $this->get('/owner/toko')->assertOk()
            ->assertSee('Profil kedai')
            ->assertSee('Menerima pesanan')
            ->assertSee('Jl. Braga No. 42, Bandung')
            ->assertSee('disabled', false);
    }

    public function test_owner_staff_page_searches_by_exact_email(): void
    {
        $this->get('/owner/staf')->assertOk()
            ->assertSee('Penugasan staf')
            ->assertSee('email')
            ->assertSee('Andi Pratama')
            ->assertSee('Belum ditugaskan')
            ->assertDontSee('@example.com</a>');
    }

    public function test_owner_report_uses_completed_paid_orders_only(): void
    {
        $this->get('/owner/laporan')->assertOk()
            ->assertSee('Laporan penjualan')
            ->assertSee('Omzet pesanan selesai')
            ->assertSee('Bukan laba')
            ->assertSee('Cold Brew Flores')
            ->assertDontSee('Menunggu pembayaran</td>', false);
    }

    public function test_staff_queue_shows_assigned_shop(): void
    {
        $this->get('/staf')->assertOk()
            ->assertSee('Antrean pesanan')
            ->assertSee('Kopi Lembur Bandung')
            ->assertSee('Andi Pratama')
            ->assertSee('Staf')
            ->assertSee('NB-20261022-102');
    }

    public function test_staff_history_page(): void
    {
        $this->get('/staf/riwayat')->assertOk()
            ->assertSee('Riwayat pesanan')
            ->assertSee('Selesai')
            ->assertSee('NB-20261022-101');
    }

    public function test_admin_dashboard_and_shop_approval(): void
    {
        $this->get('/admin')->assertOk()
            ->assertSee('Ringkasan platform')
            ->assertSee('Menunggu persetujuan')
            ->assertSee('Gayo &amp; Co Coffee House', false);

        $this->get('/admin/toko')->assertOk()
            ->assertSee('Persetujuan kedai')
            ->assertSee('Gayo &amp; Co Coffee House', false)
            ->assertSee('Aktifkan')
            ->assertSee('disabled', false);
    }

    public function test_admin_users_page_can_provision_staff_without_public_registration(): void
    {
        $this->get('/admin/pengguna')->assertOk()
            ->assertSee('Manajemen pengguna')
            ->assertSee('Buat akun owner atau staf')
            ->assertSee('Hendra Wijaya')
            ->assertSee('Nonaktifkan')
            ->assertDontSee('Daftar publik');
    }

    public function test_dashboard_previews_are_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        foreach (['/owner/kategori', '/owner/toko', '/owner/staf', '/owner/laporan', '/staf', '/staf/riwayat', '/admin', '/admin/toko', '/admin/pengguna'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }
}
