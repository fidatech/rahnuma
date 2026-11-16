@props(['property','variant'=>'standard'])
@php
    $p=$property; $img=$p->coverMedia;
    $amount=$p->transaction_type==='rent'?$p->monthly_rent:$p->price;
    $isSponsored=$p->is_sponsored && (!$p->sponsored_until || $p->sponsored_until->isFuture());
@endphp
<article class="card card-hover rh-prop h-100 mb-0">
    <a class="rh-prop-link" href="{{ route('marketplace.property',$p) }}" data-quick-view="{{ route('marketplace.property.quick',$p) }}" data-quick-view-title="{{ __('Property preview') }}">
        <span class="rh-prop-media">
            @if($img)<img src="{{ asset('storage/'.($img->thumbnail_path ?: $img->path)) }}" alt="{{ $p->title }}" loading="lazy" decoding="async">@else<span class="rh-prop-empty"><x-icon name="building" /><small>{{ __('Property photo') }}</small></span>@endif
            <span class="rh-prop-badges">
                @if($isSponsored)<span class="badge-soft warning"><x-icon name="bullhorn" /> {{ __('Sponsored') }}</span>@elseif($p->visibility==='featured' || $p->featured_at)<span class="badge-soft primary"><x-icon name="star" /> {{ __('Featured') }}</span>@endif
                <span class="badge-soft secondary">{{ deal_label($p->transaction_type) }}</span>
            </span>
        </span>
        <span class="card-body d-block">
            <span class="rh-prop-meta"><span class="text-truncate"><x-icon name="location-dot" class="me-1" />{{ loc_name($p->area) ?: loc_name($p->district) ?: loc_name($p->province) ?: __('Afghanistan') }}</span>@if($p->code)<span class="rh-prop-code" dir="ltr" title="{{ __('Property code') }}">{{ $p->code }}</span>@endif</span>
            <strong class="rh-prop-title d-block">{{ $p->title }}</strong>
            <span class="rh-prop-price d-block" dir="ltr">{{ number_format((float)$amount) }} <small>{{ $p->currency }}{{ price_period($p->transaction_type) }}</small></span>
            <span class="rh-prop-facts">
                <span>{{ enum_label($p->property_type) }}</span>
                @if($p->bedrooms)<span><x-icon name="bed" /> {{ $p->bedrooms }}</span>@endif
                @if($p->bathrooms)<span><x-icon name="bath" /> {{ $p->bathrooms }}</span>@endif
                @if($p->total_area)<span><x-icon name="ruler-combined" /> {{ number_format((float)$p->total_area) }} {{ __(strtoupper($p->area_unit ?: 'sqm')) }}</span>@endif
            </span>
        </span>
        <span class="card-footer d-flex align-items-center gap-2">
            <span class="avatar avatar-sm rounded-3 avatar-soft-primary">@if($p->agency?->logo_path)<img src="{{ asset('storage/'.$p->agency->logo_path) }}" alt="" loading="lazy" decoding="async">@else{{ initial($p->agency?->name, 'R') }}@endif</span>
            <span class="flex-fill min-w-0"><strong class="d-block text-truncate text-heading small">{{ $p->agency?->name }}</strong><span class="small text-muted">{{ $p->agency?->is_verified ? __('Verified Rahnama') : __('Rahnama office') }}</span></span>
            <span class="small fw-semibold text-primary text-nowrap">{{ __('View') }}</span>
        </span>
    </a>
</article>
