<?php

namespace App\Support;

/**
 * Daftar kurasi nama ikon Google Material Symbols (Outlined) yang relevan
 * untuk konten trust strip / nilai / fitur di halaman publik. Dipakai
 * sebagai opsi picker di form Filament supaya admin tidak perlu mengetik
 * nama ikon manual dari fonts.google.com/icons.
 */
class MaterialSymbolsIcons
{
    /**
     * @var array<int, string>
     */
    public const OPTIONS = [
        'group', 'diversity_3', 'handshake', 'support_agent', 'thumb_up', 'favorite',
        'star', 'verified', 'verified_user', 'workspace_premium', 'emoji_events', 'shield',
        'security', 'check_circle', 'trending_up', 'insights', 'timeline', 'payments',
        'savings', 'solar_power', 'bolt', 'wb_sunny', 'battery_charging_full', 'energy_savings_leaf',
        'eco', 'forest', 'recycling', 'public', 'home', 'apartment',
        'factory', 'engineering', 'construction', 'handyman', 'school', 'volunteer_activism',
        'calendar_month', 'location_on', 'phone_in_talk', 'mail',
    ];

    /**
     * Opsi untuk `Select::make(...)->options(...)->allowHtml()` — tiap opsi
     * menampilkan preview glyph ikon di samping namanya.
     *
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        return collect(self::OPTIONS)
            ->mapWithKeys(fn (string $icon) => [$icon => self::optionLabel($icon)])
            ->all();
    }

    private static function optionLabel(string $icon): string
    {
        return '<span class="material-symbols-outlined align-middle mr-2" style="font-size:1.25rem;vertical-align:middle;">'.$icon.'</span>'.$icon;
    }
}
