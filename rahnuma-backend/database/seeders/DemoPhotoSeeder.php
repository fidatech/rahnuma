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
 * loremflickr.com and kept in storage/app/public/demo-photos; each demo property then gets
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
            : 'No photos could be downloaded (no connection?). Run DemoPhotoSeeder again later.');
    }

    /** The photos of one kind of property, downloaded once and reused on the next run. */
    private function pool(string $kind): array
    {
        $disk = Storage::disk('public');
        $files = [];

        for ($n = 1; $n <= self::PER_KIND; $n++) {
            $path = "demo-photos/$kind-$n.jpg";
            if (! $disk->exists($path)) {
                try {
                    // "lock" asks the service for the same photo every time
                    $response = Http::timeout(40)->retry(2, 500, throw: false)->get(self::SOURCE.self::WORDS[$kind], ['lock' => crc32($kind) % 9000 + $n]);
                    $body = $response->successful() ? $response->body() : '';
                    $info = $body !== '' ? @getimagesizefromstring($body) : false;
                    if (! $info || ($info['mime'] ?? '') !== 'image/jpeg') {
                        continue;
                    }
                    $disk->put($path, $body);
                } catch (\Throwable $e) {
                    continue;
                }
            }
            $files[] = $path;
        }

        return $files;
    }
}
