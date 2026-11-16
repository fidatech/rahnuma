<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizationService
{
    /**
     * Make an uploaded photo lighter without making it smaller: the picture keeps every pixel
     * of its width and height, only the encoding changes. JPEG and WebP are re-encoded as
     * WebP at a high visual quality, PNG as lossless WebP, camera metadata is dropped and a
     * phone photo is turned upright first. The new file replaces the upload only when it is
     * really lighter. Returns the path to keep (the new one, or the original untouched).
     */
    public function optimize(string $relativePath, int $quality = 86): string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($relativePath)) return $relativePath;

        $absolute = $disk->path($relativePath);
        $lighter = preg_replace('/\.[^.\/]+$/', '', $relativePath).'-'.Str::lower(Str::random(6)).'.webp';

        if ($this->compressFile($absolute, $disk->path($lighter), $quality) === null) {
            $this->runBinaryOptimizers($absolute);

            return $relativePath;
        }

        $disk->delete($relativePath);

        return $lighter;
    }

    /**
     * The work of optimize() on plain files. Returns $target when a lighter copy with the same
     * pixel size was written there, null when the source should be kept as it is.
     */
    public function compressFile(string $source, string $target, int $quality = 86): ?string
    {
        if (! function_exists('imagewebp')) return null;

        $info = @getimagesize($source);
        if (! $info || empty($info[0]) || empty($info[1])) return null;
        // very large pictures need more memory than a web request should take
        if ($info[0] * $info[1] > 40_000_000) return null;

        $image = match ($info['mime'] ?? '') {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => @imagecreatefromwebp($source),
            default => false,
        };
        if (! $image) return null;

        $lossless = $info['mime'] === 'image/png';
        if (! imageistruecolor($image)) imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        // a phone stores the photo sideways and notes the turn in the metadata; apply it
        if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $turn = match ((int) (@exif_read_data($source)['Orientation'] ?? 1)) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
            if ($turn && ($turned = imagerotate($image, $turn, 0))) {
                imagedestroy($image);
                $image = $turned;
            }
        }

        $ok = @imagewebp($image, $target, $lossless && defined('IMG_WEBP_LOSSLESS') ? IMG_WEBP_LOSSLESS : $quality);
        imagedestroy($image);

        clearstatcache();
        if (! $ok || ! is_file($target) || filesize($target) === 0 || filesize($target) >= filesize($source)) {
            @unlink($target);

            return null;
        }

        return $target;
    }

    /**
     * When the package spatie/image-optimizer and its command-line tools (jpegoptim, optipng,
     * pngquant, cwebp) are installed, let them squeeze the file further. Without them: nothing.
     */
    private function runBinaryOptimizers(string $absolute): void
    {
        if (! class_exists(\Spatie\ImageOptimizer\OptimizerChainFactory::class)) return;

        try {
            \Spatie\ImageOptimizer\OptimizerChainFactory::create()->optimize($absolute);
        } catch (\Throwable) {
            // the picture stays as uploaded
        }
    }

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
