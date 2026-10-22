<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemainingPagesPreviewTest extends TestCase
{
    public function test_customer_order_history_lists_orders_with_status(): void
    {
        $this->get('/riwayat')->assertOk()
            ->assertSee('Riwayat pesanan')
            ->assertSee('NB-20261022-104')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Selesai')
            ->assertSee('Rp 92.000')
            ->assertSee('Lihat detail');
    }

    public function test_cashier_pos_has_menu_search_and_cart_panel(): void
    {
        $this->get('/kasir')->assertOk()
            ->assertSee('Kasir')
            ->assertSee('Cari menu')
            ->assertSee('Cold Brew Flores')
            ->assertSee('Pesanan baru')
            ->assertSee('Tunai')
            ->assertSee('disabled', false);
    }

    public function test_shop_detail_page_has_menu_dialog(): void
    {
        $this->get('/toko/kopi-lembur-bandung')->assertOk()
            ->assertSee('dialog', false)
            ->assertSee('data-menu-dialog', false)
            ->assertSee('Tambah ke keranjang')
            ->assertSee('disabled', false);
    }

    public function test_remaining_previews_are_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->get('/riwayat')->assertNotFound();
        $this->get('/kasir')->assertNotFound();
    }
}
