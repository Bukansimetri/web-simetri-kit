<?php

namespace App\Enums;

/**
 * Preset posisi blok konten (badge/judul/CTA/trust bar) di dalam slide hero
 * (research.md R2/R3). Kelas Tailwind literal, tidak dirangkai secara dinamis.
 */
enum BannerTextPosition: string
{
    case Left = 'left';
    case Center = 'center';
    case Right = 'right';

    /**
     * Label berbahasa Indonesia untuk dropdown Filament.
     */
    public function label(): string
    {
        return match ($this) {
            self::Left => 'Kiri',
            self::Center => 'Tengah',
            self::Right => 'Kanan',
        };
    }

    /**
     * Kelas perataan & lebar blok konten slide.
     */
    public function containerClasses(): string
    {
        return match ($this) {
            self::Left => 'max-w-2xl mx-0 text-left items-start',
            self::Center => 'max-w-2xl mx-auto text-center items-center',
            self::Right => 'max-w-2xl ml-auto mr-0 text-right items-end',
        };
    }
}
