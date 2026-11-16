<?php

namespace App\Console\Commands;

use App\Models\PropertyMedia;
use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;

class OptimizePropertyImages extends Command
{
    protected $signature = 'rahnuma:optimize-images {--force : Rebuild thumbnails that already exist}';
    protected $description = 'Generate lightweight WebP thumbnails for property listing images.';

    public function handle(ImageOptimizationService $optimizer): int
    {
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
}
