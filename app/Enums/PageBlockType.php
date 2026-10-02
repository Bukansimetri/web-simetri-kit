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

    public function label(): string
    {
        return match ($this) {
            self::AboutHero => 'Tentang Kami – Hero',
            self::AboutWhoWeAre => 'Tentang Kami – Siapa Kami',
            self::AboutVision => 'Tentang Kami – Visi',
            self::ContactInfo => 'Kontak – Info Kontak',
        };
    }

    public function defaultImagePath(): ?string
    {
        return match ($this) {
            self::AboutHero => 'images/mockup/produk-1.jpg',
            self::AboutWhoWeAre => 'images/mockup/tentang-kami-2.jpg',
            default => null,
        };
    }

    /**
     * Lebar maksimum gambar yang diunggah (px) untuk konversi WebP.
     */
    public function imageMaxWidth(): ?int
    {
        return match ($this) {
            self::AboutHero => 1920,
            self::AboutWhoWeAre => 1000,
            default => null,
        };
    }
}
