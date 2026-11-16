<?php

/*
| Small helpers used by the views. Loaded by App\Providers\AppServiceProvider,
| so no Composer step is needed after an update.
*/

if (! function_exists('asset_v')) {
    /** URL of a file in public/ with its modification time, so browsers cache it and still see a new version. */
    function asset_v(string $path): string
    {
        static $urls = [];

        return $urls[$path] ??= asset($path).'?v='.(is_file(public_path($path)) ? filemtime(public_path($path)) : '0');
    }
}

if (! function_exists('text_dir')) {
    /** Text direction of the current language: "rtl" for Dari and Pashto. */
    function text_dir(): string
    {
        return in_array(app()->getLocale(), ['fa', 'ps', 'ar'], true) ? 'rtl' : 'ltr';
    }
}

if (! function_exists('initial')) {
    /** First letter of a name, for the round avatars. */
    function initial(?string $name, string $empty = '?'): string
    {
        $name = trim((string) $name);

        return $name === '' ? $empty : mb_strtoupper(mb_substr($name, 0, 1));
    }
}

if (! function_exists('enum_label')) {
    /** A stored value such as "agency_manager" or "available", shown in the language of the user. */
    function enum_label(?string $value, ?string $empty = null): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $empty ?? '—';
        }

        return __(ucfirst(str_replace(['_', '-'], ' ', $value)));
    }
}

if (! function_exists('deal_label')) {
    /** Name of a deal type: sale, rent or bay_wafa (mortgage / بیع وفا). */
    function deal_label(?string $type, bool $long = false): string
    {
        return match ($type) {
            'sale' => __('Sale'),
            'rent' => __('Rent'),
            'bay_wafa' => $long ? __('Mortgage / Bay-e-Wafa') : __('Mortgage'),
            default => enum_label($type),
        };
    }
}

if (! function_exists('status_tone')) {
    /** Colour of the badge of a status: success, warning, danger, info or secondary. */
    function status_tone(?string $status): string
    {
        return match ($status) {
            'available', 'active', 'completed', 'accepted', 'won', 'paid', 'public', 'featured', 'verified' => 'success',
            'pending', 'draft', 'scheduled', 'new', 'negotiation', 'contacted', 'link', 'medium' => 'warning',
            'rejected', 'lost', 'cancelled', 'suspended', 'expired', 'disabled', 'high', 'critical', 'no_show' => 'danger',
            'rented', 'sold', 'mortgaged', 'visit', 'qualified' => 'info',
            default => 'secondary',
        };
    }
}

if (! function_exists('loc_name')) {
    /** Name of a province, district or area in the language of the user. */
    function loc_name(mixed $place, ?string $empty = null): ?string
    {
        if (! $place) {
            return $empty;
        }
        $name = match (app()->getLocale()) {
            'ps' => $place->name_ps ?: $place->name_fa,
            'fa' => $place->name_fa,
            default => $place->name_en,
        };

        return $name ?: ($place->name_en ?: $empty);
    }
}

if (! function_exists('price_period')) {
    /** " / month" after a monthly rent. */
    function price_period(?string $dealType): string
    {
        return $dealType === 'rent' ? ' / '.__('month') : '';
    }
}
