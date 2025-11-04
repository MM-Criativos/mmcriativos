<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageHelper
{
    /**
     * Converte um caminho salvo no DB para o caminho relativo do disco 'public'.
     * Aceita valores com ou sem prefixo 'storage/'.
     */
    public static function toPublicDiskPath(?string $dbPath): ?string
    {
        if (!$dbPath) return null;
        $path = ltrim($dbPath, '/');
        if (Str::startsWith($path, 'http')) {
            return null; // não é deletável pelo storage
        }
        if (Str::startsWith($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }
        return $path;
    }

    /** Apaga um arquivo no disco 'public' a partir do caminho salvo no DB. */
    public static function deletePublic(?string $dbPath): void
    {
        $diskPath = self::toPublicDiskPath($dbPath);
        if ($diskPath && Storage::disk('public')->exists($diskPath)) {
            Storage::disk('public')->delete($diskPath);
        }
    }
}

