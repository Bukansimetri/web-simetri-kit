<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Helper konversi gambar upload ke WebP memakai GD (extension bawaan PHP),
 * TANPA dependency baru (Principle V). Secara default hanya mengonversi format;
 * bila `$maxWidth` diberikan, gambar yang lebih lebar di-downscale ke lebar
 * tsb dengan rasio dipertahankan (tidak pernah di-upscale). Tidak ada
 * penolakan berdasarkan dimensi.
 */
class ImageUploads
{
    /**
     * Konversi file upload apa pun (JPG/PNG/GIF/dll.) ke WebP dan simpan ke
     * disk yang diberikan. Bila `$maxWidth` diisi dan lebar gambar melebihi
     * nilai tsb, gambar dikecilkan ke lebar `$maxWidth` (tinggi proporsional).
     * Mengembalikan path relatif hasil penyimpanan.
     */
    public static function storeAsWebp(UploadedFile $file, string $directory, string $disk = 'public', int $quality = 80, ?int $maxWidth = null): string
    {
        $contents = file_get_contents($file->getRealPath());

        // `@` diperlukan supaya warning GD untuk format yang tidak didukung
        // build ini (mis. AVIF) tidak dikonversi Laravel jadi ErrorException
        // sebelum sempat dicek lewat `$image === false` di bawah.
        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            throw new \RuntimeException('File yang diupload bukan gambar yang didukung. Gunakan format JPG, PNG, atau WebP (AVIF belum didukung server ini).');
        }

        // Pertahankan transparansi untuk PNG/GIF supaya tidak berubah jadi
        // latar hitam setelah dikonversi ke WebP.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if ($maxWidth !== null) {
            $image = self::downscaleToWidth($image, $maxWidth);
        }

        ob_start();
        imagewebp($image, null, $quality);
        $webpContents = ob_get_clean();
        imagedestroy($image);

        $path = trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk($disk)->put($path, $webpContents);

        return $path;
    }

    /**
     * Kecilkan resource GD ke lebar `$maxWidth` bila lebih lebar; kembalikan
     * apa adanya bila sudah ≤ `$maxWidth` (tidak pernah di-upscale). Resource
     * lama di-destroy saat penggantian.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private static function downscaleToWidth($image, int $maxWidth)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth) {
            return $image;
        }

        $newHeight = (int) round($height * ($maxWidth / $width));

        $resized = imagecreatetruecolor($maxWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);

        imagedestroy($image);

        return $resized;
    }
}
