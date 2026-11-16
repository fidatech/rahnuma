@props(['property','variant'=>'standard'])
@php
    $p=$property; $img=$p->coverMedia;
    $amount=$p->transaction_type==='rent'?$p->monthly_rent:$p->price;
    $isSponsored=$p->is_sponsored && (!$p->sponsored_until || $p->sponsored_until->isFuture());
@endphp
<article class="market-property-card {{ $variant==='wide'?'market-property-card--wide':'' }}">
    <a class="market-property-card__link" href="{{ route('marketplace.property',$p) }}" data-quick-view="{{ route('marketplace.property.quick',$p) }}" data-quick-view-title="Property preview">
        <div class="market-property-card__media">
            @if($img)<img src="{{ asset('storage/'.($img->thumbnail_path ?: $img->path)) }}" alt="{{ $p->title }}" loading="lazy" decoding="async">@else<div class="market-property-card__placeholder"><span>R</span><small>Property photo</small></div>@endif
            <div class="market-property-card__badges">
                @if($isSponsored)<span class="market-badge market-badge--sponsored">Sponsored</span>@elseif($p->visibility==='featured' || $p->featured_at)<span class="market-badge market-badge--featured">Featured</span>@endif
                <span class="market-badge market-badge--glass">{{ $p->transaction_type==='bay_wafa'?'Mortgage':ucfirst($p->transaction_type) }}</span>
            </div>
            <span class="market-property-card__save" aria-hidden="true">♡</span>
        </div>
        <div class="market-property-card__body">
            <div class="market-property-card__location"><span>⌖</span>{{ $p->area?->name_en ?: $p->district?->name_en ?: $p->province?->name_en ?: 'Afghanistan' }}</div>
            <h3>{{ $p->title }}</h3>
            <div class="market-property-card__price">{{ number_format((float)$amount) }} <small>{{ $p->currency }}{{ $p->transaction_type==='rent'?' / month':'' }}</small></div>
            <div class="market-property-card__facts">
                <span>{{ ucfirst($p->property_type) }}</span>
                @if($p->bedrooms)<span>{{ $p->bedrooms }} bd</span>@endif
                @if($p->bathrooms)<span>{{ $p->bathrooms }} ba</span>@endif
                @if($p->total_area)<span>{{ number_format((float)$p->total_area) }} {{ strtoupper($p->area_unit) }}</span>@endif
            </div>
        </div>
        <div class="market-property-card__agency">
            <div class="market-agency-mark">@if($p->agency?->logo_path)<img src="{{ asset('storage/'.$p->agency->logo_path) }}" alt="" loading="lazy" decoding="async">@else{{ strtoupper(substr($p->agency?->name ?: 'R',0,1)) }}@endif</div>
            <div><b>{{ $p->agency?->name }}</b><small>{{ $p->agency?->is_verified?'Verified Rahnama':'Rahnama office' }}</small></div>
            <span>View →</span>
        </div>
    </a>
</article>
