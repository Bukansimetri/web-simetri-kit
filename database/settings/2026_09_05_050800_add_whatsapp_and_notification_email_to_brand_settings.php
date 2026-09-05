<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('brand.whatsapp_number', null);
        $this->migrator->add('brand.contact_notification_email', null);
    }
};
