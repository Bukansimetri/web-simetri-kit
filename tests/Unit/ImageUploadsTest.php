<?php

namespace Tests\Unit;

use App\Support\ImageUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadsTest extends TestCase
{
    /**
     * @return array{0: int, 1: int}
     */
    private function storedDimensions(string $path): array
    {
        $info = getimagesizefromstring(Storage::disk('public')->get($path));

        return [$info[0], $info[1]];
    }

    public function test_downscales_wide_image_to_max_width_and_converts_to_webp(): void
    {
        Storage::fake('public');

        $path = ImageUploads::storeAsWebp(
            UploadedFile::fake()->image('wide.jpg', 2000, 1000),
            'portfolio',
            maxWidth: 1200,
        );

        $this->assertSame('webp', pathinfo($path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($path);
        $this->assertSame([1200, 600], $this->storedDimensions($path));
    }

    public function test_does_not_upscale_smaller_image(): void
    {
        Storage::fake('public');

        $path = ImageUploads::storeAsWebp(
            UploadedFile::fake()->image('small.jpg', 800, 600),
            'portfolio',
            maxWidth: 1200,
        );

        $this->assertSame([800, 600], $this->storedDimensions($path));
    }

    public function test_without_max_width_keeps_original_dimensions(): void
    {
        Storage::fake('public');

        $path = ImageUploads::storeAsWebp(
            UploadedFile::fake()->image('orig.jpg', 1600, 900),
            'articles',
        );

        $this->assertSame([1600, 900], $this->storedDimensions($path));
    }

    public function test_throws_friendly_error_for_unsupported_or_invalid_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('photo.avif', 10, 'image/avif');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File yang diupload bukan gambar yang didukung. Gunakan format JPG, PNG, atau WebP (AVIF belum didukung server ini).');

        ImageUploads::storeAsWebp($file, 'about-page');
    }
}
