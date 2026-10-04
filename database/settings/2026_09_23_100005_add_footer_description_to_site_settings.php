<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add(
            'site.footer_description',
            'Menginspirasi masa depan berkelanjutan melalui inovasi tenaga surya yang elegan dan presisi tinggi untuk masyarakat Indonesia.',
        );
    }

    public function down(): void
    {
        $this->migrator->delete('site.footer_description');
    }
};
