<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** One setting of the platform, kept as key and value. */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public const CACHE = 'rahnuma:settings';

    /** Every setting as key => value; empty when the table does not exist yet. */
    public static function map(): array
    {
        try {
            return Cache::rememberForever(self::CACHE, fn () => static::query()->pluck('value', 'key')->all());
        } catch (\Throwable) {
            return [];
        }
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE);
    }
}
