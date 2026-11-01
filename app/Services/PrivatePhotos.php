<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Badge photos: re-encoded (EXIF removed) and kept in storage/app/private, outside the web root. */
final class PrivatePhotos
{
    public function __construct(private readonly ImageStore $images) {}

    public function store(UploadedFile $file): string
    {
        [$binary, $ext] = $this->images->reencode($file, 'photo');
        $path = 'badges/'.bin2hex(random_bytes(16)).'.'.$ext;
        Storage::disk('local')->put($path, $binary);

        return $path;
    }
}
