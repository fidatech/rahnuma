<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizationService
{
    public function thumbnail(string $relativePath, int $maxWidth = 960, int $maxHeight = 720): ?string
    {
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagewebp')) return null;

        $disk = Storage::disk('public');
        if (!$disk->exists($relativePath)) return null;
        $absolute = $disk->path($relativePath);
        $info = @getimagesize($absolute);
        if (!$info || empty($info[0]) || empty($info[1]) || empty($info['mime'])) return null;

        $source = match ($info['mime']) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($absolute) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($absolute) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolute) : false,
            default => false,
        };
        if (!$source) return null;

        [$width, $height] = [$info[0], $info[1]];
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
        $targetW = max(1, (int) round($width * $ratio));
        $targetH = max(1, (int) round($height * $ratio));
        $canvas = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetW, $targetH, $width, $height);

        $thumb = 'properties/thumbs/'.Str::uuid().'.webp';
        $thumbAbsolute = $disk->path($thumb);
        if (!is_dir(dirname($thumbAbsolute))) @mkdir(dirname($thumbAbsolute), 0775, true);
        $ok = @imagewebp($canvas, $thumbAbsolute, 78);
        imagedestroy($source);
        imagedestroy($canvas);

        return $ok ? $thumb : null;
    }
}
