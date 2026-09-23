<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Memindahkan nilai `brand.*` ke grup barunya lewat `rename()`, yang
     * membawa payload nilai lama (bukan menimpanya dengan bawaan) — sehingga
     * instalasi yang sudah tayang tidak kehilangan konfigurasi apa pun
     * (FR-068, FR-069, research.md R2). Peta lengkap ada di
     * research.md §R2.
     *
     * Dibungkus pemeriksaan keberadaan agar idempoten: aman dijalankan pada
     * basis data yang sudah sebagian berpindah, dan tidak melempar error
     * pada instalasi yang sudah pernah menjalankan migration ini (FR-063).
     */
    public function up(): void
    {
        $moves = [
            'brand.app_name' => 'site.site_name',
            'brand.whatsapp_number' => 'site.whatsapp_number',
            'brand.contact_notification_email' => 'site.contact_notification_email',
            'brand.career_module_enabled' => 'site.career_module_enabled',
            'brand.meta_description' => 'seo.default_meta_description',
            'brand.og_image_path' => 'social.default_share_image_path',
            'brand.logo_path' => 'appearance.logo_path',
            'brand.favicon_path' => 'appearance.favicon_path',
            'brand.primary_color' => 'appearance.primary_color',
            'brand.secondary_color' => 'appearance.secondary_color',
            'brand.font_heading' => 'appearance.font_heading',
            'brand.font_body' => 'appearance.font_body',
        ];

        foreach ($moves as $from => $to) {
            if ($this->migrator->exists($from) && ! $this->migrator->exists($to)) {
                $this->migrator->rename($from, $to);
            }
        }
    }
};
