<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('brand.secondary_color', null);
        $this->migrator->add('brand.font_heading', null);
        $this->migrator->add('brand.font_body', null);
    }
};
