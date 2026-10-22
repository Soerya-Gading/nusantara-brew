<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendAuthTest extends TestCase
{
    public function test_auth_pages_render_without_a_database_or_submission_endpoint(): void
    {
        foreach (['/masuk', '/daftar'] as $url) {
            $response = $this->get($url);
            $response->assertOk()
                ->assertSee('method="POST"', false)
                ->assertSee('onsubmit="return false;"', false)
                ->assertSee('disabled', false)
                ->assertDontSee('localStorage')
                ->assertDontSee('Pusat Demo');
        }
    }

    public function test_navigation_remains_available_on_mobile_without_javascript(): void
    {
        $this->get('/masuk')->assertOk()
            ->assertSee('public-site-nav', false)
            ->assertSee(route('register'), false)
            ->assertSee(route('shops.index'), false);
    }

    public function test_auth_styles_are_shared_and_registration_has_product_copy(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('/assets/css/auth.css', false);
        $this->get('/daftar')->assertOk()
            ->assertSee('/assets/css/auth.css', false)
            ->assertDontSee('Semua pendaftaran publik menghasilkan akun pelanggan.');
    }
}
