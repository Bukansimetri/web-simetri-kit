<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SocialSettings extends Settings
{
    public const DEFAULT_SHARE_IMAGE_PATH = 'images/og-default.jpg';

    /**
     * Platform yang sah untuk `share_platforms` (FR-022).
     *
     * @var array<string, string>
     */
    public const SHARE_PLATFORMS = [
        'facebook' => 'Facebook',
        'twitter' => 'Twitter/X',
        'linkedin' => 'LinkedIn',
        'pinterest' => 'Pinterest',
        'reddit' => 'Reddit',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'email' => 'Email',
    ];

    public ?string $facebook_url;

    public ?string $twitter_url;

    public ?string $instagram_url;

    public ?string $linkedin_url;

    public ?string $youtube_url;

    public ?string $pinterest_url;

    public ?string $tiktok_url;

    public bool $share_buttons_enabled;

    /**
     * @var array<int, string>
     */
    public array $share_platforms;

    public ?string $default_share_image_path;

    public static function group(): string
    {
        return 'social';
    }

    /**
     * URL gambar Open Graph default halaman publik (FR-024). Fallback ke
     * asset default Luminous Azure bila admin belum mengupload gambar
     * berbagi sendiri.
     */
    public function ogImageUrl(): string
    {
        if (filled($this->default_share_image_path)) {
            return Storage::disk('public')->url($this->default_share_image_path);
        }

        return asset(self::DEFAULT_SHARE_IMAGE_PATH);
    }

    /**
     * Daftar profil sosial yang terisi, dalam bentuk sesuai kebutuhan
     * `sameAs` JSON-LD Organization (FR-032).
     *
     * @return array<int, string>
     */
    public function filledProfileUrls(): array
    {
        return collect([
            $this->facebook_url,
            $this->twitter_url,
            $this->instagram_url,
            $this->linkedin_url,
            $this->youtube_url,
            $this->pinterest_url,
            $this->tiktok_url,
        ])->filter(fn (?string $url) => filled($url))->values()->all();
    }
}
