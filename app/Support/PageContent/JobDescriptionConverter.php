<?php

namespace App\Support\PageContent;

/**
 * Mengubah deskripsi lowongan teks polos (versi lama) menjadi HTML paragraf agar tampil dan terbuka di editor
 * teks berformat tanpa perubahan: baris kosong memisahkan paragraf, baris baru tunggal menjadi <br>.
 * Deskripsi yang sudah berupa HTML dikembalikan apa adanya, sehingga aman dijalankan ulang.
 */
class JobDescriptionConverter
{
    public static function toHtml(string $text): string
    {
        if (preg_match('/<(p|ul|ol|h[2-4]|blockquote|br)\b/i', $text) === 1) {
            return $text;
        }

        $normalized = trim(str_replace(["\r\n", "\r"], "\n", $text));

        if ($normalized === '') {
            return '';
        }

        $paragraphs = preg_split('/\n[ \t]*\n+/', $normalized) ?: [];

        return collect($paragraphs)
            ->map(fn (string $paragraph): string => trim($paragraph))
            ->filter(fn (string $paragraph): bool => $paragraph !== '')
            ->map(fn (string $paragraph): string => '<p>'.str_replace("\n", '<br>', e($paragraph)).'</p>')
            ->implode('');
    }
}
