<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Hanya properti yang BENAR-BENAR BARU (spec 023-site-settings).
     * `default_meta_description` dipindahkan dari `brand.meta_description`
     * lewat migration terpisah (research.md R2).
     */
    public function up(): void
    {
        $this->migrator->add('seo.title_separator', '|');
        $this->migrator->add('seo.default_title_format', '{page_title} {separator} {site_name}');
        $this->migrator->add('seo.page_title_formats', []);
        $this->migrator->add('seo.meta_keywords', []);
        $this->migrator->add('seo.default_canonical_url', null);
        $this->migrator->add('seo.allow_indexing', true);
        $this->migrator->add('seo.allow_following', true);
        $this->migrator->add('seo.twitter_handle', null);
        $this->migrator->add('seo.additional_head_meta', null);
        $this->migrator->add('seo.verification_google', null);
        $this->migrator->add('seo.verification_bing', null);
        $this->migrator->add('seo.verification_yandex', null);
        $this->migrator->add('seo.verification_baidu', null);
        $this->migrator->add('seo.robots_txt_content', null);
        $this->migrator->add('seo.sitemap_enabled', true);
        $this->migrator->add('seo.sitemap_include_pages', true);
        $this->migrator->add('seo.sitemap_include_articles', true);
        $this->migrator->add('seo.sitemap_include_products', true);
        $this->migrator->add('seo.sitemap_include_portfolio', true);
        $this->migrator->add('seo.sitemap_changefreq', 'weekly');
        $this->migrator->add('seo.sitemap_priority', '0.8');
    }
};
