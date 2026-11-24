<?php

namespace App\Services;

use App\Enums\MediaKind;
use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores admin uploads under public/uploads with random names.
 * SVG is accepted as a plain image file: it is never parsed here and is only ever shown through <img>.
 */
final class MediaUploader
{
    private const PDF_MAX_BYTES = 20 * 1024 * 1024;

    private const SVG_MIMES = ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml', 'text/plain'];

    public function __construct(private readonly ImageStore $images) {}

    public function store(UploadedFile $file, ?int $languageId, ?string $alt, ?string $title): MediaAsset
    {
        $mime = ImageStore::detectMime($file);
        $isSvg = strtolower($file->getClientOriginalExtension()) === 'svg' && in_array($mime, self::SVG_MIMES, true);
        $width = $height = null;

        if ($isSvg) {
            $this->limit($file, ImageStore::MAX_BYTES, 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.');
            [$ext, $kind, $mime, $binary] = ['svg', MediaKind::Image, 'image/svg+xml', null];
        } elseif ($mime === 'application/pdf') {
            $this->limit($file, self::PDF_MAX_BYTES, 'حجم PDF نباید بیشتر از ۲۰ مگابایت باشد.');
            [$ext, $kind, $binary] = ['pdf', MediaKind::Document, null];
        } else {
            [$binary, $ext, $width, $height] = $this->images->reencode($file);
            $kind = MediaKind::Image;
        }

        $path = now()->format('Y/m').'/'.bin2hex(random_bytes(16)).'.'.$ext;
        $destination = public_path('uploads/'.$path);
        File::ensureDirectoryExists(dirname($destination));
        $binary !== null ? File::put($destination, $binary) : File::copy($file->getRealPath(), $destination);

        return MediaAsset::create([
            'language_id' => $languageId,
            'kind' => $kind,
            'path' => $path,
            'original_name' => Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', $file->getClientOriginalName()), 191, ''),
            'mime' => $mime,
            'size' => $binary !== null ? strlen($binary) : (int) $file->getSize(),
            'width' => $width,
            'height' => $height,
            'alt' => $alt,
            'title' => $title,
            'created_by' => auth()->id(),
        ]);
    }

    private function limit(UploadedFile $file, int $max, string $message): void
    {
        if ($file->getSize() > $max) {
            throw ValidationException::withMessages(['file' => $message]);
        }
    }
}
