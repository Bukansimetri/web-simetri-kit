<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    /**
     * Daftar bahasa kurasi untuk dropdown Pengaturan Umum (FR-005) — admin
     * tidak bisa input kode bahasa bebas, hanya boleh memilih dari daftar
     * ini, sejalan dengan pola App\Settings\AppearanceSettings::FONT_OPTIONS.
     *
     * @var array<string, string>
     */
    public const LANGUAGE_OPTIONS = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    /**
     * Zona waktu Indonesia yang dipakai instalasi kit ini (FR-005) — dibatasi
     * pada zona yang relevan untuk klien di Indonesia, bukan seluruh daftar
     * zona waktu dunia.
     *
     * @var array<string, string>
     */
    public const TIMEZONE_OPTIONS = [
        'Asia/Jakarta' => 'WIB — Jakarta (UTC+7)',
        'Asia/Makassar' => 'WITA — Makassar (UTC+8)',
        'Asia/Jayapura' => 'WIT — Jayapura (UTC+9)',
    ];

    public ?string $site_name;

    public ?string $tagline;

    public ?string $site_description;

    public ?string $company_name;

    public ?string $company_email;

    public ?string $company_phone;

    public ?string $company_address;

    public string $default_language;

    public string $timezone;

    public ?string $copyright_text;

    public ?string $terms_url;

    public ?string $privacy_url;

    public ?string $cookie_policy_url;

    public ?string $error_404_message;

    public ?string $error_500_message;

    public bool $maintenance_mode;

    public ?string $whatsapp_number;

    public ?string $contact_notification_email;

    public bool $career_module_enabled;

    public static function group(): string
    {
        return 'site';
    }

    /**
     * Link `wa.me` ke nomor WhatsApp bisnis instalasi ini dengan pesan
     * pre-filled (FR-012, spec 002-theme-branding-system). Null bila
     * `whatsapp_number` belum dikonfigurasi (FR-013) — pemanggil harus
     * menangani null tsb (lewati langkah buka WhatsApp, bukan error).
     */
    public function whatsappUrl(string $message): ?string
    {
        if (blank($this->whatsapp_number)) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', $this->whatsapp_number);

        return sprintf('https://wa.me/%s?text=%s', $number, rawurlencode($message));
    }
}
