<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('brand.app_name', null);
        $this->migrator->add('brand.logo_path', null);
        $this->migrator->add('brand.favicon_path', null);
        $this->migrator->add('brand.primary_color', null);
    }
};
