@php
    $cover=$property->coverMedia;
    $amount=$property->transaction_type==='rent'?$property->monthly_rent:$property->price;
@endphp
<div class="quick-view-content quick-view-content--market">
    <div class="quick-view-hero quick-view-hero--property">
        @if($cover)<img src="{{ asset('storage/'.($cover->thumbnail_path ?: $cover->path)) }}" alt="{{ $property->title }}" decoding="async">@else<div class="quick-view-placeholder">R</div>@endif
        <div class="quick-view-hero__badges">@if($property->is_sponsored)<span class="market-badge market-badge--sponsored">Sponsored</span>@elseif($property->visibility==='featured')<span class="market-badge market-badge--featured">Featured</span>@endif<span class="market-badge market-badge--glass">{{ $property->transaction_type==='bay_wafa'?'Mortgage':ucfirst($property->transaction_type) }}</span></div>
    </div>
    <div class="quick-view-body">
        <div class="quick-view-eyebrow">{{ $property->area?->name_en ?: $property->district?->name_en ?: $property->province?->name_en ?: 'Afghanistan' }}</div>
        <h2>{{ $property->title }}</h2>
        <p class="quick-view-location">{{ $property->address ?: 'Location details available from the Rahnama' }}</p>
        <div class="quick-view-price">{{ number_format((float)$amount) }} <small>{{ $property->currency }}{{ $property->transaction_type==='rent'?' / month':'' }}</small></div>
        <div class="quick-view-facts">
            <div><span>Type</span><b>{{ ucfirst($property->property_type) }}</b></div>
            <div><span>Bedrooms</span><b>{{ $property->bedrooms ?? '—' }}</b></div>
            <div><span>Bathrooms</span><b>{{ $property->bathrooms ?? '—' }}</b></div>
            <div><span>Area</span><b>{{ $property->total_area ? number_format((float)$property->total_area).' '.strtoupper($property->area_unit) : '—' }}</b></div>
        </div>
        <div class="quick-view-section"><span>Listed by</span><div class="quick-view-person"><div class="market-agency-mark">@if($property->agency?->logo_path)<img src="{{ asset('storage/'.$property->agency->logo_path) }}" alt="">@else{{ strtoupper(substr($property->agency?->name ?: 'R',0,1)) }}@endif</div><div><b>{{ $property->agency?->name }}</b><small>{{ $property->agency?->is_verified?'Verified Rahnama':'Rahnama office' }}</small></div></div></div>
    </div>
    <div class="quick-view-actions"><a class="btn secondary" href="{{ route('marketplace.agency',$property->agency) }}">View Rahnama</a><a class="btn" href="{{ route('marketplace.property',$property) }}">Full property details</a></div>
</div>
