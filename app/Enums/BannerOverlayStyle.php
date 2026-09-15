<?php

namespace App\Enums;

/**
 * Preset lapisan visual di atas gambar slide hero (research.md R2/R3).
 * Setiap case memetakan ke kelas Tailwind literal — TIDAK PERNAH dirangkai
 * secara dinamis, agar terdeteksi pemindai Tailwind 4 saat build.
 */
enum BannerOverlayStyle: string
{
    case Dark = 'dark';
    case Light = 'light';
    case None = 'none';

    /**
     * Label berbahasa Indonesia untuk dropdown Filament.
     */
    public function label(): string
    {
        return match ($this) {
            self::Dark => 'Lapisan gelap (untuk gambar terang)',
            self::Light => 'Lapisan terang (untuk gambar gelap)',
            self::None => 'Tanpa lapisan',
        };
    }

    /**
     * Kelas gradasi lapisan, arahnya mengikuti posisi teks (contracts/public-render.md §4).
     */
    public function overlayClasses(BannerTextPosition $position): string
    {
        return match ($this) {
            self::Dark => match ($position) {
                BannerTextPosition::Left => 'absolute inset-0 bg-gradient-to-r from-on-surface/90 via-on-surface/65 to-transparent',
                BannerTextPosition::Center => 'absolute inset-0 bg-gradient-to-t from-on-surface/90 via-on-surface/50 to-on-surface/20',
                BannerTextPosition::Right => 'absolute inset-0 bg-gradient-to-l from-on-surface/90 via-on-surface/65 to-transparent',
            },
            self::Light => match ($position) {
                BannerTextPosition::Left => 'absolute inset-0 bg-gradient-to-r from-white/90 via-white/65 to-transparent',
                BannerTextPosition::Center => 'absolute inset-0 bg-gradient-to-t from-white/90 via-white/50 to-white/20',
                BannerTextPosition::Right => 'absolute inset-0 bg-gradient-to-l from-white/90 via-white/65 to-transparent',
            },
            self::None => '',
        };
    }

    /**
     * Kelas warna teks judul & badge untuk preset ini.
     */
    public function headingClasses(): string
    {
        return match ($this) {
            self::Dark => 'text-white',
            self::Light => 'text-on-surface',
            self::None => 'text-white [text-shadow:0_2px_8px_rgba(0,0,0,0.6)]',
        };
    }

    /**
     * Kelas warna teks subjudul/badan untuk preset ini.
     */
    public function bodyClasses(): string
    {
        return match ($this) {
            self::Dark => 'text-white/90',
            self::Light => 'text-on-surface-variant',
            self::None => 'text-white/90 [text-shadow:0_2px_8px_rgba(0,0,0,0.6)]',
        };
    }
}
