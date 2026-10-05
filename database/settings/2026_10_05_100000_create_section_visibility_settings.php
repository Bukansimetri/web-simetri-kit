<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('section_visibility.hidden', []);
    }

    public function down(): void
    {
        $this->migrator->delete('section_visibility.hidden');
    }
};
