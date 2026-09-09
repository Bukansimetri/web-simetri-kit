<?php

namespace Tests\Feature\Pages;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTestimonialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_testimonial_is_shown_on_about_page(): void
    {
        Testimonial::factory()->create([
            'name' => 'Budi Santoso',
            'attribution' => 'Pemilik Rumah, Bandung',
            'content' => 'Pemasangan rapi dan hemat listrik terasa.',
            'rating' => 5,
        ]);

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('Budi Santoso', escape: false);
        $response->assertSee('Pemilik Rumah, Bandung', escape: false);
        $response->assertSee('Pemasangan rapi dan hemat listrik terasa.', escape: false);
    }

    public function test_inactive_testimonial_is_not_shown(): void
    {
        Testimonial::factory()->inactive()->create(['name' => 'Klien Nonaktif']);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertDontSee('Klien Nonaktif', escape: false);
    }

    public function test_testimonials_render_in_order(): void
    {
        Testimonial::factory()->create(['name' => 'Kedua', 'order' => 2]);
        Testimonial::factory()->create(['name' => 'Pertama', 'order' => 1]);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSeeInOrder(['Pertama', 'Kedua'], escape: false);
    }

    public function test_about_page_renders_without_testimonials_section_when_none_active(): void
    {
        Testimonial::factory()->inactive()->create();

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('Nilai-Nilai Kami', escape: false);
        $response->assertSee('Visi Kami', escape: false);
        $response->assertDontSee('Apa Kata Klien Kami', escape: false);
    }

    public function test_home_page_shows_active_testimonials(): void
    {
        Testimonial::factory()->create(['name' => 'Klien Beranda', 'content' => 'Sangat puas dengan layanan SUOER.']);
        Testimonial::factory()->inactive()->create(['name' => 'Klien Nonaktif Beranda']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Klien Beranda', escape: false)
            ->assertSee('Apa Kata Mereka Tentang SUOER?', escape: false)
            ->assertDontSee('Klien Nonaktif Beranda', escape: false);
    }
}
