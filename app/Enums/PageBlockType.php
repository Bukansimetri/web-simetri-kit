<?php

namespace App\Enums;

/**
 * Blok halaman tetap (satu form per blok, tanpa daftar item). Kolom isi tiap
 * blok ditetapkan di sini mengikuti desain halaman.
 */
enum PageBlockType: string
{
    case AboutHero = 'tentang-kami.hero';
    case AboutWhoWeAre = 'tentang-kami.siapa-kami';
    case AboutVision = 'tentang-kami.visi';
    case ContactInfo = 'kontak.info-kontak';
    case ProductsHero = 'produk.hero';
    case CareerHero = 'karir.hero';
    case ArticlesHero = 'artikel.hero';
    case FaqHero = 'faq.hero';
    case ContactHero = 'kontak.hero';
    case PortfolioHero = 'portfolio.hero';

    public function label(): string
    {
        return match ($this) {
            self::AboutHero => 'Tentang Kami – Hero',
            self::AboutWhoWeAre => 'Tentang Kami – Siapa Kami',
            self::AboutVision => 'Tentang Kami – Visi',
            self::ContactInfo => 'Kontak – Info Kontak',
            self::ProductsHero => 'Produk',
            self::CareerHero => 'Karir',
            self::ArticlesHero => 'Artikel',
            self::FaqHero => 'FAQ',
            self::ContactHero => 'Kontak',
            self::PortfolioHero => 'Portfolio',
        };
    }

    /**
     * Label di menu Banner Halaman Lain (nama halamannya saja).
     */
    public function bannerLabel(): string
    {
        return $this === self::AboutHero ? 'Tentang Kami' : $this->label();
    }

    /**
     * Banner (hero) halaman selain Beranda — dikelola dari menu Banner, bukan Blok Halaman.
     */
    public function isPageBanner(): bool
    {
        return in_array($this, self::pageBanners(), true);
    }

    /**
     * @return list<self>
     */
    public static function pageBanners(): array
    {
        return [
            self::ProductsHero,
            self::AboutHero,
            self::CareerHero,
            self::ArticlesHero,
            self::PortfolioHero,
            self::FaqHero,
            self::ContactHero,
        ];
    }

    /**
     * @return list<string>
     */
    public static function pageBannerValues(): array
    {
        return array_map(fn (self $type): string => $type->value, self::pageBanners());
    }

    public function defaultImagePath(): ?string
    {
        return match ($this) {
            self::AboutHero, self::ProductsHero => 'images/mockup/produk-1.jpg',
            self::AboutWhoWeAre => 'images/mockup/tentang-kami-2.jpg',
            self::CareerHero => 'images/mockup/home-3.jpg',
            self::ArticlesHero => 'images/mockup/artikel-3.jpg',
            self::PortfolioHero => 'images/mockup/artikel-1.jpg',
            default => null,
        };
    }

    /**
     * Lebar maksimum gambar yang diunggah (px) untuk konversi WebP.
     */
    public function imageMaxWidth(): ?int
    {
        return match ($this) {
            self::AboutHero, self::ProductsHero, self::CareerHero, self::ArticlesHero, self::PortfolioHero => 1920,
            self::AboutWhoWeAre => 1000,
            default => null,
        };
    }
}
