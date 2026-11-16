{{--
    A property as a card: photo, badges, title, place, price and facts.
    Used by the inventory of a Rahnama (with its links in the slot) and by the lists of matches.
--}}
@props(['property', 'href' => null, 'quick' => null, 'quickTitle' => null, 'badge' => null])
@php
    $p = $property;
    $cover = $p->relationLoaded('coverMedia') ? $p->coverMedia : null;
    $amount = $p->transaction_type === 'rent' ? $p->monthly_rent : $p->price;
@endphp
<article class="card rh-prop h-100 mb-0">
    <a class="rh-prop-media" @if ($href) href="{{ $href }}" @endif @if ($quick) data-quick-view="{{ $quick }}" data-quick-view-title="{{ $quickTitle }}" @endif>
        @if ($cover)
            <img src="{{ asset('storage/'.($cover->thumbnail_path ?: $cover->path)) }}" alt="{{ $p->title }}" loading="lazy" decoding="async">
        @else
            <span class="rh-prop-empty"><x-icon name="building" /><small>{{ enum_label($p->property_type, __('Property')) }}</small></span>
        @endif
        <span class="rh-prop-badges">
            @if ($badge)<span class="badge-soft primary">{{ $badge }}</span>@endif
            <span class="badge-soft {{ status_tone($p->status) }}">{{ enum_label($p->status) }}</span>
            @if ($p->visibility)<span class="badge-soft secondary">{{ enum_label($p->visibility) }}</span>@endif
        </span>
    </a>
    <div class="card-body">
        <div class="d-flex justify-content-between gap-2 small text-muted mb-1"><span class="badge-code">{{ $p->code }}</span><span>{{ deal_label($p->transaction_type) }}</span></div>
        <h3 class="rh-prop-title">@if ($href)<a href="{{ $href }}">{{ $p->title }}</a>@else{{ $p->title }}@endif</h3>
        <p class="small text-muted mb-2"><x-icon name="location-dot" class="me-1" />{{ $p->address ?: (loc_name($p->province) ?: __('Location not completed')) }}</p>
        <div class="rh-prop-price" dir="ltr">{{ number_format((float) $amount) }} <small>{{ $p->currency }}{{ price_period($p->transaction_type) }}</small></div>
        <div class="rh-prop-facts">
            <span><x-icon name="bed" /> <b>{{ $p->bedrooms ?? '—' }}</b> {{ __('beds') }}</span>
            <span><x-icon name="bath" /> <b>{{ $p->bathrooms ?? '—' }}</b> {{ __('baths') }}</span>
            <span><x-icon name="ruler-combined" /> <b>{{ $p->total_area ?? '—' }}</b> {{ __(strtoupper($p->area_unit ?: 'sqm')) }}</span>
        </div>
        @if ($p->relationLoaded('owner'))
            <div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top">
                <span class="avatar avatar-xs avatar-soft-primary">{{ initial($p->owner?->name) }}</span>
                <span class="small min-w-0"><span class="text-muted">{{ __('Primary owner') }}:</span> <strong class="text-heading">{{ $p->owner?->name ?: __('Not assigned') }}</strong></span>
            </div>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="card-footer d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</article>
