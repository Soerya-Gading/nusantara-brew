<?php

namespace Tests\Feature;

use Tests\TestCase;

class CartViewTest extends TestCase
{
    public function test_cart_empty_state_is_available_without_database(): void
    {
        $this->get('/keranjang')->assertOk()
            ->assertSee('Keranjang Anda masih kosong')
            ->assertSee(route('shops.index'), false)
            ->assertDontSee('localStorage')
            ->assertDontSee('Lanjut Checkout');
    }
}
