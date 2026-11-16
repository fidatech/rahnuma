<?php

namespace App\Console\Commands;

use App\Models\PropertyMedia;
use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;

class OptimizePropertyImages extends Command
{
    protected $signature = 'rahnuma:optimize-images {--force : Rebuild thumbnails that already exist} {--originals : Also make the stored photos lighter, keeping their pixel size}';
    protected $description = 'Generate lightweight WebP thumbnails for property listing images.';

    public function handle(ImageOptimizationService $optimizer): int
    {
        if ($this->option('originals')) $this->compressOriginals($optimizer);

        $query = PropertyMedia::withoutGlobalScopes()->where('type', 'image');
        if (!$this->option('force')) $query->whereNull('thumbnail_path');

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No property images require optimization.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();
        $optimized = 0;

        $query->orderBy('id')->chunkById(100, function ($media) use ($optimizer, &$optimized, $bar) {
            foreach ($media as $item) {
                if ($thumb = $optimizer->thumbnail($item->path)) {
                    $item->forceFill(['thumbnail_path' => $thumb])->saveQuietly();
                    $optimized++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Optimized {$optimized} of {$total} property images.");
        if (!function_exists('imagewebp')) {
            $this->warn('GD/WebP is not available in this PHP build; thumbnails could not be generated.');
        }

        return self::SUCCESS;
    }

    /** Re-encode photos uploaded before compression existed. Thumbnails and demo copies are left alone. */
    private function compressOriginals(ImageOptimizationService $optimizer): void
    {
        $saved = 0;
        $count = 0;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        PropertyMedia::withoutGlobalScopes()->where('type', 'image')->where('path', 'not like', '%.webp')->orderBy('id')
            ->chunkById(100, function ($media) use ($optimizer, $disk, &$saved, &$count) {
                foreach ($media as $item) {
                    if (! $disk->exists($item->path)) continue;
                    $before = $disk->size($item->path);
                    $path = $optimizer->optimize($item->path);
                    if ($path !== $item->path) {
                        $item->forceFill(['path' => $path])->saveQuietly();
                        $saved += $before - $disk->size($path);
                        $count++;
                    }
                }
            });

        $this->info(sprintf('Made %d photos lighter, saving %.1f MB. Pixel sizes are unchanged.', $count, $saved / 1048576));
    }
}
