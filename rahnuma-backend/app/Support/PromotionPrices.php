<?php

namespace App\Support;

use App\Models\{PromotionRequest, Setting};

/**
 * What a placement costs for 7, 15 and 30 days, and where an office sends the money.
 * The administrator sets both on the Promotions page; until then the values of config/rahnuma.php apply.
 */
class PromotionPrices
{
    /** ['sponsored' => [7 => 500.0, 15 => ..., 30 => ...], 'featured' => [...]] */
    public static function all(): array
    {
        $saved = Setting::map();
        $prices = [];
        foreach (PromotionRequest::KINDS as $kind) {
            foreach (PromotionRequest::DAYS as $days) {
                $key = "promo_price_{$kind}_{$days}";
                $prices[$kind][$days] = isset($saved[$key]) && is_numeric($saved[$key])
                    ? (float) $saved[$key]
                    : (float) config("rahnuma.promotion_prices.$kind.$days", 0);
            }
        }

        return $prices;
    }

    public static function price(string $kind, int $days): float
    {
        return self::all()[$kind][$days] ?? 0.0;
    }

    public static function currency(): string
    {
        $currency = Setting::map()['promo_currency'] ?? null;

        return in_array($currency, config('rahnuma.currencies'), true) ? $currency : config('rahnuma.base_currency', 'AFN');
    }

    /** Where to pay, one text for each way of paying: an account number, a wallet, an address. */
    public static function instructions(): array
    {
        $saved = Setting::map();
        $out = [];
        foreach (PromotionRequest::METHODS as $method) $out[$method] = trim((string) ($saved["promo_pay_$method"] ?? ''));

        return $out;
    }
}
