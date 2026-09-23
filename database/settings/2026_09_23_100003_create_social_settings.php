<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Hanya properti yang BENAR-BENAR BARU (spec 023-site-settings).
     * `default_share_image_path` dipindahkan dari `brand.og_image_path`
     * lewat migration terpisah (research.md R2).
     */
    public function up(): void
    {
        $this->migrator->add('social.facebook_url', null);
        $this->migrator->add('social.twitter_url', null);
        $this->migrator->add('social.instagram_url', null);
        $this->migrator->add('social.linkedin_url', null);
        $this->migrator->add('social.youtube_url', null);
        $this->migrator->add('social.pinterest_url', null);
        $this->migrator->add('social.tiktok_url', null);
        $this->migrator->add('social.share_buttons_enabled', false);
        $this->migrator->add('social.share_platforms', []);
    }
};
