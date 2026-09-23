<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppearanceSettings extends Settings
{
    /**
     * Nilai default starter kit ("Luminous Azure", desain SUOER) — dipakai sebagai
     * fallback saat admin belum mengisi Tampilan (FR-071), dan ditampilkan
     * sebagai nilai awal di form Filament supaya admin melihat tema yang sudah jadi
     * sejak instalasi pertama kali (spec 002-theme-branding-system).
     */
    public const DEFAULT_PRIMARY_COLOR = '#006397';

    public const DEFAULT_SECONDARY_COLOR = '#3a5f94';

    public const DEFAULT_FONT_HEADING = 'Manrope';

    public const DEFAULT_FONT_BODY = 'Be Vietnam Pro';

    /**
     * Daftar font kurasi untuk dropdown Tampilan (FR-004, spec
     * 002-theme-branding-system) — admin tidak bisa input nama/URL font
     * bebas, hanya boleh memilih dari daftar ini.
     *
     * @var array<int, string>
     */
    public const FONT_OPTIONS = [
        'Manrope',
        'Be Vietnam Pro',
        'Inter',
        'Poppins',
        'Plus Jakarta Sans',
        'Nunito Sans',
        'Work Sans',
        'Lato',
    ];

    public ?string $logo_path;

    public ?string $favicon_path;

    public ?string $primary_color;

    public ?string $secondary_color;

    public ?string $font_heading;

    public ?string $font_body;

    public static function group(): string
    {
        return 'appearance';
    }
}
