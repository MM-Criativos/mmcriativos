<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ImageUploadService
 *
 * Otimiza imagens no upload sem dependências externas.
 * - Remove metadados regravando a imagem
 * - Corrige orientação EXIF para JPEG
 * - Redimensiona para limites definidos
 * - Comprime (JPEG/WebP por qualidade; PNG por compressão)
 *
 * Caso o formato não seja suportado pelo GD (ex.: SVG, GIF animado),
 * faz fallback para o store original sem otimização.
 */
class ImageUploadService
{
    /**
     * Salva uma imagem otimizada no disco público e retorna o caminho relativo.
     *
     * @param UploadedFile $file
     * @param string $directory Ex.: 'clients/logos'
     * @param array $options ['max_width'=>int, 'max_height'=>int, 'quality'=>int, 'png_compression'=>int]
     * @return string relative path inside public disk (e.g., clients/logos/uuid.jpg)
     */
    public function store(UploadedFile $file, string $directory, array $options = []): string
    {
        $mime = (string) $file->getMimeType();

        // Apenas processa imagens suportadas; demais salva como veio
        if (!str_starts_with($mime, 'image/')) {
            return $file->store($this->normalizeDir($directory), 'public');
        }

        $supported = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $supported, true)) {
            return $file->store($this->normalizeDir($directory), 'public');
        }

        $maxWidth = (int)($options['max_width'] ?? (int) env('IMAGE_MAX_WIDTH', 1920));
        $maxHeight = (int)($options['max_height'] ?? (int) env('IMAGE_MAX_HEIGHT', 1920));
        $quality = (int)($options['quality'] ?? (int) env('IMAGE_QUAL', 82)); // JPEG/WEBP
        $pngCompression = (int)($options['png_compression'] ?? (int) env('PNG_COMPRESSION', 6)); // 0(best)–9(slowest)

        $srcPath = $file->getRealPath();
        if (!$srcPath) {
            // fallback defensivo
            return $file->store($this->normalizeDir($directory), 'public');
        }

        // Cria resource a partir do MIME
        [$image, $ext] = $this->createImageResource($srcPath, $mime);
        if (!$image) {
            return $file->store($this->normalizeDir($directory), 'public');
        }

        // Corrige orientação para JPEG com EXIF
        if ($mime === 'image/jpeg') {
            $image = $this->autoOrientJpeg($srcPath, $image);
        }

        // Redimensiona mantendo proporção se exceder limite
        $image = $this->resizeIfNeeded($image, $maxWidth, $maxHeight);

        // Gera arquivo temporário otimizado
        $tmp = $this->writeOptimizedTemp($image, $ext, $quality, $pngCompression);
        imagedestroy($image);

        if (!$tmp || !is_file($tmp)) {
            return $file->store($this->normalizeDir($directory), 'public');
        }

        // Gera nome (slug legível + timestamp) e salva no disco
        $filename = $this->buildFileName($options['basename'] ?? null, $ext, (bool)($options['add_timestamp'] ?? true));
        $dir = $this->normalizeDir($directory);
        $path = trim($dir . '/' . $filename, '/');

        $stream = fopen($tmp, 'rb');
        Storage::disk('public')->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($tmp);

        return $path;
    }

    private function buildFileName(?string $basename, string $ext, bool $withTimestamp = true): string
    {
        $name = $basename ? Str::slug($basename, '-') : Str::uuid()->toString();

        if ($withTimestamp) {
            $name .= '-' . date('Ymd-His');
        }

        // Garante unicidade mesmo quando múltiplos arquivos são enviados no mesmo segundo
        $name .= '-' . Str::lower(Str::random(6));

        return strtolower($name . '.' . $ext);
    }

    private function normalizeDir(string $dir): string
    {
        return trim($dir, '/');
    }

    /**
     * @return array{0: resource|null, 1: string} [gd resource, target extension]
     */
    private function createImageResource(string $path, string $mime): array
    {
        try {
            switch ($mime) {
                case 'image/jpeg':
                    $img = @imagecreatefromjpeg($path);
                    return [$img ?: null, 'jpg'];
                case 'image/png':
                    $img = @imagecreatefrompng($path);
                    // Garantir canal alfa preservado
                    if ($img) {
                        imagealphablending($img, false);
                        imagesavealpha($img, true);
                    }
                    return [$img ?: null, 'png'];
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $img = @imagecreatefromwebp($path);
                        return [$img ?: null, 'webp'];
                    }
                    return [null, 'webp'];
                default:
                    return [null, ''];
            }
        } catch (\Throwable $e) {
            return [null, ''];
        }
    }

    /** Corrige orientação JPEG via EXIF quando possível. */
    private function autoOrientJpeg(string $path, $image)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        try {
            $exif = @exif_read_data($path);
            $orientation = (int)($exif['Orientation'] ?? 1);
            switch ($orientation) {
                case 3:
                    $rot = imagerotate($image, 180, 0);
                    if ($rot) { imagedestroy($image); $image = $rot; }
                    break;
                case 6:
                    $rot = imagerotate($image, -90, 0);
                    if ($rot) { imagedestroy($image); $image = $rot; }
                    break;
                case 8:
                    $rot = imagerotate($image, 90, 0);
                    if ($rot) { imagedestroy($image); $image = $rot; }
                    break;
            }
        } catch (\Throwable $e) {
            // ignora erros de EXIF
        }
        return $image;
    }

    /** Redimensiona mantendo proporção se exceder limites. */
    private function resizeIfNeeded($image, int $maxW, int $maxH)
    {
        $w = imagesx($image);
        $h = imagesy($image);
        if ($w <= 0 || $h <= 0) {
            return $image;
        }
        $scale = min($maxW / $w, $maxH / $h, 1.0);
        if ($scale >= 1.0) {
            return $image; // já está dentro do limite
        }
        $newW = (int) floor($w * $scale);
        $newH = (int) floor($h * $scale);
        $dst = imagecreatetruecolor($newW, $newH);
        // preservar alpha
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $image, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($image);
        return $dst;
    }

    /** Escreve arquivo temporário otimizado e retorna seu caminho. */
    private function writeOptimizedTemp($image, string $ext, int $quality, int $pngCompression): ?string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'img');
        if ($tmp === false) {
            return null;
        }
        // Ajusta extensão para facilitar debug (opcional)
        $tmpFile = $tmp . '.' . $ext;
        @unlink($tmp);

        $ok = false;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $ok = @imagejpeg($image, $tmpFile, max(10, min(100, $quality)));
                break;
            case 'png':
                // compressão 0 (sem) a 9 (máxima). Mantemos alpha.
                imagealphablending($image, false);
                imagesavealpha($image, true);
                $ok = @imagepng($image, $tmpFile, max(0, min(9, $pngCompression)));
                break;
            case 'webp':
                if (function_exists('imagewebp')) {
                    $ok = @imagewebp($image, $tmpFile, max(10, min(100, $quality)));
                }
                break;
        }
        if (!$ok) {
            return null;
        }
        return $tmpFile;
    }
}
