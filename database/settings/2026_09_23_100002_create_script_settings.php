<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Seluruh properti grup `script` baru — tidak ada yang dipindahkan dari
     * `brand` (spec 023-site-settings).
     */
    public function up(): void
    {
        $this->migrator->add('script.head_scripts', null);
        $this->migrator->add('script.body_start_scripts', null);
        $this->migrator->add('script.body_end_scripts', null);
        $this->migrator->add('script.footer_scripts', null);
        $this->migrator->add('script.custom_css', null);
        $this->migrator->add('script.custom_js', null);
        $this->migrator->add('script.head_scripts_consent', 'none');
        $this->migrator->add('script.body_start_scripts_consent', 'none');
        $this->migrator->add('script.body_end_scripts_consent', 'none');
        $this->migrator->add('script.footer_scripts_consent', 'none');
        $this->migrator->add('script.custom_css_consent', 'none');
        $this->migrator->add('script.custom_js_consent', 'none');
        $this->migrator->add('script.cookie_consent_enabled', false);
        $this->migrator->add('script.cookie_banner_message', null);
    }
};
