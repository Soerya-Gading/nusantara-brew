<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderPreviewTest extends TestCase
{
    public function test_order_detail_shows_timeline_payment_notice_and_disabled_actions(): void
    {
        $this->get('/pesanan')->assertOk()
            ->assertSee('NB-20261022-104')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Dalam antrean')
            ->assertSee('Sedang dibuat')
            ->assertSee('Siap')
            ->assertSee('SIMULASI PEMBAYARAN')
            ->assertSee('15 menit')
            ->assertSee('Rp 92.000')
            ->assertSee('Cold Brew Flores')
            ->assertSee('disabled', false)
            ->assertDontSee('localStorage');
    }

    public function test_receipt_uses_snapshot_and_print_action(): void
    {
        $this->get('/pesanan/struk')->assertOk()
            ->assertSee('Struk Pesanan')
            ->assertSee('NB-20261022-104')
            ->assertSee('Cold Brew Flores')
            ->assertSee('window.print()', false);
    }

    public function test_order_previews_are_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/pesanan')->assertNotFound();
        $this->get('/pesanan/struk')->assertNotFound();
    }
}
