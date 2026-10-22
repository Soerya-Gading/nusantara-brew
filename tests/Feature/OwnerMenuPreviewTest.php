<?php

namespace Tests\Feature;

use Tests\TestCase;

class OwnerMenuPreviewTest extends TestCase
{
    public function test_menu_page_lists_items_with_stock_and_restock_form(): void
    {
        $this->get('/owner/menu')->assertOk()
            ->assertSee('Katalog & stok')
            ->assertSee('Cold Brew Flores')
            ->assertSee('Latte Aren')
            ->assertSee('Habis')
            ->assertSee('Tambah stok')
            ->assertSee('min="1"', false)
            ->assertSee('disabled', false)
            ->assertDontSee('localStorage');
    }

    public function test_menu_preview_is_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/owner/menu')->assertNotFound();
    }
}
