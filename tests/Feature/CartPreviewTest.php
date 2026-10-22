<?php

namespace Tests\Feature;

use Tests\TestCase;

class CartPreviewTest extends TestCase
{
    public function test_filled_cart_preview_renders_items_and_total(): void
    {
        $this->get('/pratinjau/keranjang')->assertOk()
            ->assertSee('Cold Brew Flores')
            ->assertSee('Croissant Mentega')
            ->assertSee('Rp 92.000')
            ->assertSee('Tanpa gula')
            ->assertSee('disabled', false);
    }

    public function test_preview_is_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/pratinjau/keranjang')->assertNotFound();
    }
}
