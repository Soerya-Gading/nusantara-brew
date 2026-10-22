<?php

namespace Tests\Feature;

use Tests\TestCase;

class CheckoutPreviewTest extends TestCase
{
    public function test_checkout_preview_has_fulfillment_payment_and_summary(): void
    {
        $this->get('/pratinjau/checkout')->assertOk()
            ->assertSee('name="fulfillment_type"', false)
            ->assertSee('name="table_label"', false)
            ->assertSee('qris_simulation')
            ->assertSee('cash')
            ->assertSee('Rp 92.000')
            ->assertSee('SIMULASI PEMBAYARAN')
            ->assertSee('onsubmit="return false;"', false)
            ->assertSee('disabled', false);
    }

    public function test_checkout_preview_is_not_available_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/pratinjau/checkout')->assertNotFound();
    }
}
