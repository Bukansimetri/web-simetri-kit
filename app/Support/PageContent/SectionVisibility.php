<?php

namespace App\Support\PageContent;

use App\Enums\PublicSection;
use App\Settings\SectionVisibilitySettings;
use Illuminate\Contracts\Foundation\Application;
use Spatie\LaravelSettings\Exceptions\MissingSettings;

/**
 * Pembaca/penyimpan status tampil section publik. Bila pengaturan belum termigrasi
 * (mis. kode baru sampai lebih dulu daripada migrasi), semua section dianggap tampil
 * agar situs tidak 500 dan tampilan tidak berubah.
 */
class SectionVisibility
{
    /**
     * Memo per instance aplikasi (satu per request; di test, satu per test), supaya pembacaan
     * pengaturan terjadi sekali per request dan tidak bocor antar test.
     *
     * @var \WeakMap<Application, array<int, string>>|null
     */
    private static ?\WeakMap $memo = null;

    public static function shows(PublicSection $section): bool
    {
        return ! in_array($section->value, self::hiddenKeys(), true);
    }

    /**
     * @return list<PublicSection>
     */
    public static function hiddenSections(): array
    {
        $hidden = [];

        foreach (self::hiddenKeys() as $key) {
            $section = PublicSection::tryFrom($key);

            if ($section !== null) {
                $hidden[] = $section;
            }
        }

        return $hidden;
    }

    /**
     * @param  array<int, PublicSection>  $sections
     */
    public static function setHidden(array $sections): void
    {
        $settings = app(SectionVisibilitySettings::class);
        $settings->hidden = array_values(array_unique(array_map(fn (PublicSection $section): string => $section->value, $sections)));
        $settings->save();

        self::flush();
    }

    /**
     * Reset memo per request (dipakai setelah menyimpan dan oleh test).
     */
    public static function flush(): void
    {
        self::$memo = null;
    }

    /**
     * @return array<int, string>
     */
    private static function hiddenKeys(): array
    {
        self::$memo ??= new \WeakMap;
        $app = app();

        if (isset(self::$memo[$app])) {
            return self::$memo[$app];
        }

        try {
            $keys = array_values(array_filter(app(SectionVisibilitySettings::class)->hidden, 'is_string'));
        } catch (MissingSettings) {
            $keys = [];
        }

        return self::$memo[$app] = $keys;
    }
}
