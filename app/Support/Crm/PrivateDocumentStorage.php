<?php

namespace App\Support\Crm;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateDocumentStorage
{
    public const DISK = 'local';

    public const LEGACY_DISK = 'public';

    public static function put(string $path, string $contents): void
    {
        Storage::disk(self::DISK)->put($path, $contents);

        if (Storage::disk(self::LEGACY_DISK)->exists($path)) {
            Storage::disk(self::LEGACY_DISK)->delete($path);
        }
    }

    public static function exists(string $path): bool
    {
        return Storage::disk(self::DISK)->exists($path)
            || Storage::disk(self::LEGACY_DISK)->exists($path);
    }

    public static function path(string $path): string
    {
        $disk = Storage::disk(self::DISK)->exists($path) ? self::DISK : self::LEGACY_DISK;

        return Storage::disk($disk)->path($path);
    }

    public static function download(string $path, string $filename): StreamedResponse
    {
        $disk = Storage::disk(self::DISK)->exists($path) ? self::DISK : self::LEGACY_DISK;

        return Storage::disk($disk)->download($path, $filename);
    }

    public static function delete(string $path): void
    {
        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    public static function migrateLegacy(): int
    {
        $moved = 0;
        $legacy = Storage::disk(self::LEGACY_DISK);

        foreach ($legacy->allFiles('crm/documents') as $path) {
            if (! str_ends_with(strtolower($path), '.pdf')) {
                continue;
            }

            if (! Storage::disk(self::DISK)->exists($path)) {
                Storage::disk(self::DISK)->put($path, $legacy->get($path));
            }

            $legacy->delete($path);
            $moved++;
        }

        return $moved;
    }
}
