<?php

namespace App\Enums;

/**
 * Tempat tampil entri FAQ: halaman FAQ, atau bagian tanya-jawab di halaman Produk dan Kontak.
 */
enum FaqPlacement: string
{
    case Faq = 'faq';
    case Product = 'produk';
    case Contact = 'kontak';

    public function label(): string
    {
        return match ($this) {
            self::Faq => 'Halaman FAQ',
            self::Product => 'Halaman Produk',
            self::Contact => 'Halaman Kontak',
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
