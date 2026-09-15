<?php

namespace Database\Factories;

use App\Enums\BannerOverlayStyle;
use App\Enums\BannerTextPosition;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'image_path' => 'banners/'.fake()->uuid().'.webp',
            'alt_text' => fake()->sentence(),
            'link_url' => null,
            'starts_at' => null,
            'ends_at' => null,
            'order' => 0,
            'is_active' => true,
            'badge_text' => null,
            'heading' => null,
            'subheading' => null,
            'cta_primary_label' => null,
            'cta_primary_url' => null,
            'cta_secondary_label' => null,
            'cta_secondary_url' => null,
            'trust_html' => null,
            'overlay_style' => BannerOverlayStyle::Dark,
            'text_position' => BannerTextPosition::Left,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => today()->addDays(3),
            'ends_at' => today()->addDays(13),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => today()->subDays(10),
            'ends_at' => today()->subDay(),
        ]);
    }

    /**
     * Slide hero lengkap: badge, judul, subjudul, kedua CTA, dan trust bar,
     * untuk dipakai test yang menguji render konten penuh (US1/US2/US4).
     */
    public function withContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'badge_text' => 'Solar Panel Terpercaya',
            'heading' => fake()->sentence(6),
            'subheading' => fake()->paragraph(2),
            'cta_primary_label' => 'Konsultasi Gratis',
            'cta_primary_url' => '/kontak',
            'cta_secondary_label' => 'Pelajari Cara Kerja',
            'cta_secondary_url' => '/#kalkulator',
            'trust_html' => '<p><strong>500+ Pelanggan Puas</strong></p>',
        ]);
    }
}
