<?php

namespace Tests\Feature\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders_form_fields(): void
    {
        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('name="nama"', escape: false);
        $response->assertSee('name="phone"', escape: false);
        $response->assertSee('name="pesan"', escape: false);
    }

    public function test_contact_page_has_no_post_route(): void
    {
        $this->assertFalse(
            Route::has('kontak.store'),
            'Form Kontak belum boleh punya route submit sungguhan (FR-007) — ditunda ke AMC-216.'
        );
    }
}
