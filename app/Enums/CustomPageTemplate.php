<?php

namespace App\Enums;

/**
 * Template tampilan Halaman Kustom: teks bebas (Standar) atau dokumen legal berstruktur.
 */
enum CustomPageTemplate: string
{
    case Standard = 'standar';
    case Legal = 'legal';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standar',
            self::Legal => 'Dokumen Legal',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
