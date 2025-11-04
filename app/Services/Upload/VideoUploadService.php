<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * VideoUploadService
 * Transcodifica vídeos para MP4 (H.264), remove metadados,
 * aplica faststart e gera poster JPG. Fallback para upload simples
 * caso o ffmpeg não esteja disponível.
 */
class VideoUploadService
{
    public function transcode(UploadedFile $file, string $directory, array $opts = []): array
    {
        $dir = trim($directory, '/');
        $preset = $opts['preset'] ?? env('VIDEO_PRESET', 'veryfast');
        $crf = (string)($opts['crf'] ?? env('VIDEO_CRF', 23));
        $aac = (string)($opts['aac_bitrate'] ?? env('VIDEO_AAC_BITRATE', '128k'));
        $maxW = (int)($opts['max_width'] ?? env('VIDEO_MAX_WIDTH', 1280));
        $maxH = (int)($opts['max_height'] ?? env('VIDEO_MAX_HEIGHT', 720));
        $posterTime = (string)($opts['poster_time'] ?? env('VIDEO_POSTER_TIME', '1'));
        $basename = $opts['basename'] ?? null;
        $addTimestamp = (bool)($opts['add_timestamp'] ?? true);
        $posterSuffix = $opts['poster_suffix'] ?? 'poster';

        // Encontra ffmpeg/ffprobe
        $ffmpeg = (string) env('FFMPEG_BIN', 'ffmpeg');
        $ffprobe = (string) env('FFPROBE_BIN', 'ffprobe');

        // Teste rápido: se ffmpeg falhar, cai no fallback
        if (!$this->binaryAvailable($ffmpeg)) {
            $path = $file->store($dir, 'public');
            return [
                'video' => $path,
                'poster' => null,
                'transcoded' => false,
            ];
        }

        $uuid = Str::uuid()->toString();
        $tmpIn = $file->getRealPath();
        $tmpOut = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uuid . '.mp4';
        $tmpPoster = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uuid . '.jpg';

        // Filtro de escala com múltiplos de 2 (exigência do H.264)
        $vf = "scale='min($maxW,iw)':-2";
        // Comando de transcode
        $cmd = [
            $ffmpeg,
            '-y',
            '-i', $tmpIn,
            '-map_metadata', '-1', // remove metadados
            '-movflags', '+faststart',
            '-vf', $vf,
            '-c:v', 'libx264',
            '-preset', $preset,
            '-crf', $crf,
            '-pix_fmt', 'yuv420p',
            '-c:a', 'aac',
            '-b:a', $aac,
            $tmpOut,
        ];

        $proc = new Process($cmd, null, null, null, 1200); // 20 min timeout
        $proc->run();
        if (!$proc->isSuccessful() || !is_file($tmpOut)) {
            // fallback upload simples
            $path = $file->store($dir, 'public');
            return [
                'video' => $path,
                'poster' => null,
                'transcoded' => false,
            ];
        }

        // Poster
        $posterCmd = [
            $ffmpeg,
            '-y',
            '-ss', $posterTime,
            '-i', $tmpIn,
            '-vframes', '1',
            '-q:v', '2',
            '-vf', $vf,
            $tmpPoster,
        ];
        $p2 = new Process($posterCmd, null, null, null, 300);
        $p2->run();
        $hasPoster = is_file($tmpPoster);

        // Salva no disk public
        // Define nomes legíveis com timestamp
        $base = $basename ? Str::slug($basename, '-') : $uuid;
        if ($addTimestamp) {
            $base .= '-' . date('Ymd-His');
        }
        $videoName = strtolower($base . '.mp4');
        $videoPath = $dir . '/' . $videoName;
        $stream = fopen($tmpOut, 'rb');
        Storage::disk('public')->put($videoPath, $stream);
        if (is_resource($stream)) { fclose($stream); }
        @unlink($tmpOut);

        $posterPath = null;
        if ($hasPoster) {
            $posterName = strtolower($base . '-' . $posterSuffix . '.jpg');
            $posterPath = $dir . '/' . $posterName;
            $ps = fopen($tmpPoster, 'rb');
            Storage::disk('public')->put($posterPath, $ps);
            if (is_resource($ps)) { fclose($ps); }
            @unlink($tmpPoster);
        }

        return [
            'video' => $videoPath,
            'poster' => $posterPath,
            'transcoded' => true,
        ];
    }

    private function binaryAvailable(string $bin): bool
    {
        // Tenta executar binário para validar disponibilidade
        try {
            $p = new Process([$bin, '-version']);
            $p->run();
            return $p->isSuccessful();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
