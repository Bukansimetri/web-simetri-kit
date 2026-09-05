<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Helper konversi gambar upload ke WebP memakai GD (extension bawaan PHP),
 * TANPA dependency baru (Principle V, research.md §6). Hanya mengonversi
 * format — TIDAK ADA validasi/penolakan berdasarkan dimensi gambar (FR-020,
 * FR-021).
 */
class ImageUploads
{
    /**
     * Konversi file upload apa pun (JPG/PNG/GIF/dll.) ke WebP dan simpan ke
     * disk yang diberikan. Mengembalikan path relatif hasil penyimpanan.
     */
    public static function storeAsWebp(UploadedFile $file, string $directory, string $disk = 'public', int $quality = 80): string
    {
        $contents = file_get_contents($file->getRealPath());

        $image = imagecreatefromstring($contents);

        if ($image === false) {
            throw new \RuntimeException('File yang diupload bukan gambar yang valid.');
        }

        // Pertahankan transparansi untuk PNG/GIF supaya tidak berubah jadi
        // latar hitam setelah dikonversi ke WebP.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, $quality);
        $webpContents = ob_get_clean();
        imagedestroy($image);

        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk($disk)->put($path, $webpContents);

        return $path;
    }
}
