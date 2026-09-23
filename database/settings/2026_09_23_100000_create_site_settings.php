<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Hanya properti yang BENAR-BENAR BARU (spec 023-site-settings). Empat
     * properti lain grup `site` — site_name, whatsapp_number,
     * contact_notification_email, career_module_enabled — dipindahkan dari
     * grup `brand` lewat migration terpisah (lihat
     * ..._move_brand_settings_to_new_groups.php, research.md R2).
     */
    public function up(): void
    {
        $this->migrator->add('site.tagline', null);
        $this->migrator->add('site.site_description', null);
        $this->migrator->add('site.company_name', null);
        $this->migrator->add('site.company_email', null);
        $this->migrator->add('site.company_phone', null);
        $this->migrator->add('site.company_address', null);
        $this->migrator->add('site.default_language', 'id');
        $this->migrator->add('site.timezone', 'Asia/Jakarta');
        $this->migrator->add('site.copyright_text', null);
        $this->migrator->add('site.terms_url', null);
        $this->migrator->add('site.privacy_url', null);
        $this->migrator->add('site.cookie_policy_url', null);
        $this->migrator->add('site.error_404_message', null);
        $this->migrator->add('site.error_500_message', null);
        $this->migrator->add('site.maintenance_mode', false);
    }
};
