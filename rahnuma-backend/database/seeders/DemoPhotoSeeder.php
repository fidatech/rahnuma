<?php

namespace Database\Seeders;

use App\Models\{Property, PropertyMedia};
use App\Services\ImageOptimizationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Photos for the demo properties (code DM-…), downloaded from the internet. Part of DemoSeeder,
 * and can be run alone to add photos to demo properties that have none:
 *
 *     php artisan db:seed --class=DemoPhotoSeeder
 *
 * For each kind of property (apartment, house, shop, …) a few photos are fetched once from
 * loremflickr.com (or picsum.photos when that site cannot be reached) and kept in storage/app/public/demo-photos; each demo property then gets
 * three of them as its own files, the first as cover.
 *
 * The photos come from Flickr under Creative Commons licences and show buildings from anywhere
 * in the world. They are for a demo only: do not use them for real listings.
 *
 * Without a connection nothing is downloaded and the properties simply stay without photos.
 * It refuses to run when APP_ENV=production. Needs `php artisan storage:link` to show the photos.
 */
class DemoPhotoSeeder extends Seeder
{
    private const SOURCE = 'https://loremflickr.com/1200/800/';
    private const PER_KIND = 5;
    private const PER_PROPERTY = 3;

    /** property type => search words */
    private const WORDS = [
        'apartment' => 'apartment,building',
        'house' => 'house,home',
        'shop' => 'shop,storefront',
        'office' => 'office,interior',
        'land' => 'land,field',
        'warehouse' => 'warehouse',
        'building' => 'building,architecture',
        'garden' => 'garden,orchard',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoPhotoSeeder adds sample photos. It does not run when APP_ENV=production.');

            return;
        }

        $properties = Property::withoutGlobalScopes()->where('code', 'like', 'DM-%')->whereDoesntHave('media')->orderBy('id')->get();
        if ($properties->isEmpty()) {
            return;
        }

        $disk = Storage::disk('public');
        $optimizer = app(ImageOptimizationService::class);
        $pool = [];
        $added = 0;

        foreach ($properties as $number => $property) {
            $kind = isset(self::WORDS[$property->property_type]) ? $property->property_type : 'building';
            $pool[$kind] ??= $this->pool($kind);
            if (! $pool[$kind]) {
                continue;
            }

            for ($i = 0; $i < min(self::PER_PROPERTY, count($pool[$kind])); $i++) {
                $source = $pool[$kind][($number + $i) % count($pool[$kind])];
                $path = 'properties/demo/'.strtolower($property->code).'-'.($i + 1).'.jpg';
                if (! $disk->exists($path)) {
                    $disk->copy($source, $path);
                }

                PropertyMedia::withoutGlobalScopes()->create([
                    'agency_id' => $property->agency_id,
                    'property_id' => $property->id,
                    'type' => 'image',
                    'path' => $path,
                    'thumbnail_path' => $optimizer->thumbnail($path),
                    'sort_order' => $i,
                    'is_cover' => $i === 0,
                ]);
            }
            $added++;
        }

        Cache::forget('rahnuma:marketplace:home:v8');
        $this->command?->info($added > 0
            ? "Photos added to $added demo properties."
            : 'No photos could be downloaded. See the reason above, then run DemoPhotoSeeder again.');
    }

    /** The photos of one kind of property, downloaded once and reused on the next run. */
    private function pool(string $kind): array
    {
        $disk = Storage::disk('public');
        $files = [];

        for ($n = 1; $n <= self::PER_KIND; $n++) {
            $path = "demo-photos/$kind-$n.jpg";
            if (! $disk->exists($path)) {
                $body = $this->download($kind, $n);
                if ($body === null) {
                    continue;
                }
                $disk->put($path, $body);
            }
            $files[] = $path;
        }

        return $files;
    }

    /**
     * One photo, from the first source that answers with a real JPEG:
     *   1. loremflickr.com  a photo that matches the kind of property ("lock" keeps it the same photo)
     *   2. picsum.photos    any good photo, when the first source cannot be reached
     * The reason of every failure is printed, so a blocked site or a certificate problem is visible.
     */
    private function download(string $kind, int $n): ?string
    {
        $sources = [
            'loremflickr.com' => [self::SOURCE.self::WORDS[$kind], ['lock' => crc32($kind) % 9000 + $n]],
            'picsum.photos' => ["https://picsum.photos/seed/rahnuma-$kind-$n/1200/800.jpg", []],
        ];

        foreach ($sources as $name => [$url, $query]) {
            if (isset($this->dead[$name])) {
                continue;
            }
            try {
                $response = Http::timeout(30)->connectTimeout(10)->withHeaders(['User-Agent' => 'Mozilla/5.0 (RahnumaDemoSeeder)'])->get($url, $query);
                $body = $response->successful() ? $response->body() : '';
                $info = $body !== '' ? @getimagesizefromstring($body) : false;
                if ($info && ($info['mime'] ?? '') === 'image/jpeg') {
                    return $body;
                }
                $this->fail($name, 'answered with HTTP '.$response->status().' and '.($info['mime'] ?? 'no image'));
            } catch (\Throwable $e) {
                $this->fail($name, $e->getMessage());
            }
        }

        return null;
    }

    /** Sources that failed three times in a row are not tried again in this run. */
    private array $dead = [];
    private array $failures = [];

    private function fail(string $source, string $reason): void
    {
        $this->failures[$source] = ($this->failures[$source] ?? 0) + 1;
        if ($this->failures[$source] === 1) {
            $this->command?->warn("Photo download from $source failed: $reason");
        }
        if ($this->failures[$source] >= 3) {
            $this->dead[$source] = true;
        }
    }
}
