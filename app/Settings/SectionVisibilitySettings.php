<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Daftar section publik yang disembunyikan admin. Kunci yang tidak ada di daftar berarti tampil,
 * sehingga instalasi lama dan section baru otomatis tampil.
 */
class SectionVisibilitySettings extends Settings
{
    /**
     * @var array<int, string>
     */
    public array $hidden;

    public static function group(): string
    {
        return 'section_visibility';
    }
}
