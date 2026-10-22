<?php

namespace Tests\Feature;

use Tests\TestCase;

class OwnerDashboardPreviewTest extends TestCase
{
    public function test_owner_dashboard_renders_metrics_queue_and_signed_in_identity(): void
    {
        $this->get('/owner')->assertOk()
            ->assertSee('Ringkasan')
            ->assertSee('Kopi Lembur Bandung')
            ->assertSee('Pesanan selesai hari ini')
            ->assertSee('Omzet pesanan selesai')
            ->assertSee('NB-20261022-104')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Keluar')
            ->assertDontSee('MODE DEMO')
            ->assertDontSee('Pusat Demo')
            ->assertDontSee('Ganti Peran');
    }

    public function test_owner_preview_is_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/owner')->assertNotFound();
    }
}
