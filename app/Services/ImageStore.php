<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Validates an uploaded image by its real content and re-encodes it with GD.
 * Re-encoding drops EXIF (GPS, camera, owner), embedded payloads and anything not pixel data.
 */
final class ImageStore
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    private const MAX_PIXELS = 36_000_000; // refuses decompression bombs (6000 x 6000)

    private const MAX_SIDE = 2400;

    public const RASTER = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /** Real MIME type from file content (never from the client-supplied name or type). */
    public static function detectMime(UploadedFile $file): string
    {
        return (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
    }

    /** @return array{0: string, 1: string, 2: int, 3: int} binary, extension, width, height */
    public function reencode(UploadedFile $file, string $field = 'file'): array
    {
        $mime = self::detectMime($file);
        $ext = self::RASTER[$mime] ?? null;
        $size = $file->getSize();

        $this->require($field, $ext !== null, 'فقط تصویر jpg، png یا webp پذیرفته می‌شود.');
        $this->require($field, $size > 0 && $size <= self::MAX_BYTES, 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.');
        $this->require($field, extension_loaded('gd'), 'افزونهٔ GD روی سرور فعال نیست؛ تصویر بدون پردازش پذیرفته نمی‌شود.');

        $info = @getimagesize($file->getRealPath());
        $this->require($field, $info !== false && $info[0] * $info[1] <= self::MAX_PIXELS, 'ابعاد تصویر معتبر نیست یا بیش از حد بزرگ است.');

        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        $this->require($field, $image !== false, 'تصویر خراب است.');

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $angle = [3 => 180, 6 => -90, 8 => 90][(int) (@exif_read_data($file->getRealPath())['Orientation'] ?? 1)] ?? 0;
            if ($angle !== 0) { // apply orientation before EXIF is dropped
                $image = imagerotate($image, $angle, 0) ?: $image;
            }
        }

        if (max(imagesx($image), imagesy($image)) > self::MAX_SIDE) {
            $image = imagescale($image, imagesx($image) >= imagesy($image) ? self::MAX_SIDE : -1, imagesy($image) > imagesx($image) ? self::MAX_SIDE : -1);
        }

        imagesavealpha($image, true);
        ob_start();
        match ($ext) {
            'jpg' => imagejpeg($image, null, 85),
            'png' => imagepng($image, null, 6),
            'webp' => imagewebp($image, null, 82),
        };
        $binary = (string) ob_get_clean();

        return [$binary, $ext, imagesx($image), imagesy($image)];
    }

    private function require(string $field, bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
