<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ScriptSettings extends Settings
{
    public const CONSENT_NONE = 'none';

    public const CONSENT_ANALYTICS = 'analytics';

    public const CONSENT_MARKETING = 'marketing';

    /**
     * @var array<string, string>
     */
    public const CONSENT_CATEGORIES = [
        self::CONSENT_NONE => 'Selalu dijalankan',
        self::CONSENT_ANALYTICS => 'Analitik',
        self::CONSENT_MARKETING => 'Pemasaran',
    ];

    /**
     * Batas ukuran per slot dalam karakter (FR-048).
     */
    public const MAX_SLOT_LENGTH = 20000;

    public ?string $head_scripts;

    public ?string $body_start_scripts;

    public ?string $body_end_scripts;

    public ?string $footer_scripts;

    public ?string $custom_css;

    public ?string $custom_js;

    public string $head_scripts_consent;

    public string $body_start_scripts_consent;

    public string $body_end_scripts_consent;

    public string $footer_scripts_consent;

    public string $custom_css_consent;

    public string $custom_js_consent;

    public bool $cookie_consent_enabled;

    public ?string $cookie_banner_message;

    public static function group(): string
    {
        return 'script';
    }
}
