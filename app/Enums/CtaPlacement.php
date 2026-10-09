<?php

namespace App\Enums;

/**
 * Penempatan CTA tetap di halaman publik. Hanya teks yang dikelola admin;
 * tujuan, ikon, dan markup tombol tetap di Blade.
 */
enum CtaPlacement: string
{
    case Home = 'beranda';
    case ProductCalculator = 'produk-kalkulator';
    case ProductClosing = 'produk-penutup';
    case ProductDetail = 'produk-detail';
    case ArticleIndex = 'artikel-daftar';
    case ArticleDetail = 'artikel-detail';
    case About = 'tentang-kami';
    case Faq = 'faq';
    case Career = 'karir';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Beranda – CTA Penutup',
            self::ProductCalculator => 'Produk – CTA Kalkulator',
            self::ProductClosing => 'Produk – CTA Penutup',
            self::ProductDetail => 'Detail Produk – Masa Depan Energi',
            self::ArticleIndex => 'Daftar Artikel – CTA',
            self::ArticleDetail => 'Detail Artikel – CTA',
            self::About => 'Tentang Kami – CTA',
            self::Faq => 'FAQ – CTA',
            self::Career => 'Karir – CTA',
        };
    }

    public function bodyLabel(): string
    {
        return match ($this) {
            self::Home, self::ProductCalculator, self::ProductClosing, self::ProductDetail, self::ArticleIndex => 'Paragraf',
            default => 'Subjudul',
        };
    }

    public function hasSecondaryButton(): bool
    {
        return $this === self::Home;
    }

    public function supportsProductToken(): bool
    {
        return $this === self::ProductDetail;
    }

    public function supportsImage(): bool
    {
        return $this === self::ProductDetail;
    }
}
