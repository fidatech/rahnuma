@props(['property','variant'=>'standard'])
@php
    $p=$property; $img=$p->coverMedia;
    $amount=$p->transaction_type==='rent'?$p->monthly_rent:$p->price;
    $isSponsored=$p->is_sponsored && (!$p->sponsored_until || $p->sponsored_until->isFuture());
    $isFeatured=$p->visibility==='featured' || $p->featured_at;
    $place=loc_name($p->area) ?: loc_name($p->district) ?: loc_name($p->province) ?: __('Afghanistan');
@endphp
<article class="rh-prop h-100 {{ $isSponsored ? 'is-sponsored' : '' }}">
    <a class="rh-prop-link" href="{{ route('marketplace.property',$p) }}" data-quick-view="{{ route('marketplace.property.quick',$p) }}" data-quick-view-title="{{ __('Property preview') }}">
        <span class="rh-prop-media">
            @if($img)
                <x-marketplace.photo :src="asset('storage/'.($img->thumbnail_path ?: $img->path))" :alt="$p->title" />
            @else
                <span class="rh-prop-empty"><x-icon name="building" /><small>{{ __('Property photo') }}</small></span>
            @endif
            <span class="rh-prop-shade" aria-hidden="true"></span>
            <span class="rh-prop-badges">
                <span class="rh-prop-deal">{{ deal_label($p->transaction_type) }}</span>
                @if($isSponsored)<span class="rh-prop-flag"><x-icon name="bullhorn" /> {{ __('Sponsored') }}</span>@elseif($isFeatured)<span class="rh-prop-flag is-featured"><x-icon name="star" /> {{ __('Featured') }}</span>@endif
            </span>
            <span class="rh-prop-tag" dir="ltr">{{ number_format((float)$amount) }} <small>{{ $p->currency }}{{ price_period($p->transaction_type) }}</small></span>
        </span>
        <span class="rh-prop-body">
            <span class="rh-prop-meta"><span class="rh-prop-kind">{{ enum_label($p->property_type) }}</span>@if($p->code)<span class="rh-prop-code" dir="ltr" title="{{ __('Property code') }}">{{ $p->code }}</span>@endif</span>
            <strong class="rh-prop-title">{{ $p->title }}</strong>
            <span class="rh-prop-place"><x-icon name="location-dot" /> <span class="text-truncate">{{ $place }}</span></span>
            <span class="rh-prop-facts">
                @if($p->bedrooms)<span><x-icon name="bed" /> <b>{{ $p->bedrooms }}</b></span>@endif
                @if($p->bathrooms)<span><x-icon name="bath" /> <b>{{ $p->bathrooms }}</b></span>@endif
                @if($p->total_area)<span><x-icon name="ruler-combined" /> <b>{{ number_format((float)$p->total_area) }}</b> {{ __(strtoupper($p->area_unit ?: 'sqm')) }}</span>@endif
                @if(!$p->bedrooms && !$p->bathrooms && !$p->total_area)<span>{{ deal_label($p->transaction_type, true) }}</span>@endif
            </span>
        </span>
        <span class="rh-prop-foot">
            <span class="avatar avatar-sm rounded-3 avatar-soft-primary">@if($p->agency?->logo_path)<img src="{{ asset('storage/'.$p->agency->logo_path) }}" alt="" loading="lazy" decoding="async">@else{{ initial($p->agency?->name, 'R') }}@endif</span>
            <span class="flex-fill min-w-0"><strong class="d-block text-truncate">{{ $p->agency?->name }}</strong><small class="{{ $p->agency?->is_verified ? 'is-verified' : '' }}">@if($p->agency?->is_verified)<x-icon name="circle-check" /> {{ __('Verified Rahnama') }}@else{{ __('Rahnama office') }}@endif</small></span>
            <span class="rh-prop-go" aria-hidden="true"><x-icon name="arrow-right" /></span>
        </span>
    </a>
</article>
