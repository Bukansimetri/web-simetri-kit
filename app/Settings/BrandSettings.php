<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class BrandSettings extends Settings
{
    /**
     * Nilai default starter kit ("Luminous Azure", desain SUOER) — dipakai sebagai
     * fallback saat admin belum mengisi Theme Settings (FR-005), dan ditampilkan
     * sebagai nilai awal di form Filament supaya admin melihat tema yang sudah jadi
     * sejak instalasi pertama kali (spec.md User Story 3).
     */
    public const DEFAULT_PRIMARY_COLOR = '#006397';

    public const DEFAULT_SECONDARY_COLOR = '#3a5f94';

    public const DEFAULT_FONT_HEADING = 'Manrope';

    public const DEFAULT_FONT_BODY = 'Be Vietnam Pro';

    public const DEFAULT_OG_IMAGE_PATH = 'images/og-default.jpg';

    /**
     * Daftar font kurasi untuk dropdown Theme Settings (FR-004) — admin tidak bisa
     * input nama/URL font bebas, hanya boleh memilih dari daftar ini.
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

    public ?string $app_name;

    public ?string $logo_path;

    public ?string $favicon_path;

    public ?string $primary_color;

    public ?string $secondary_color;

    public ?string $font_heading;

    public ?string $font_body;

    public ?string $og_image_path;

    public ?string $whatsapp_number;

    public ?string $contact_notification_email;

    public bool $career_module_enabled;

    public static function group(): string
    {
        return 'brand';
    }

    /**
     * Link `wa.me` ke nomor WhatsApp bisnis instalasi ini dengan pesan
     * pre-filled (FR-012). Null bila `whatsapp_number` belum dikonfigurasi
     * (FR-013) — dipanggil pemanggil harus menangani null tsb (lewati langkah
     * buka WhatsApp, bukan error).
     */
    public function whatsappUrl(string $message): ?string
    {
        if (blank($this->whatsapp_number)) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', $this->whatsapp_number);

        return sprintf('https://wa.me/%s?text=%s', $number, rawurlencode($message));
    }

    /**
     * URL gambar Open Graph default halaman publik (FR-009, FR-010). Fallback ke
     * asset default Luminous Azure bila admin belum mengupload OG image sendiri.
     */
    public function ogImageUrl(): string
    {
        if (filled($this->og_image_path)) {
            return Storage::disk('public')->url($this->og_image_path);
        }

        return asset(self::DEFAULT_OG_IMAGE_PATH);
    }
}
