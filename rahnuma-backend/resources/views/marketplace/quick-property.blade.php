@php
    $cover=$property->coverMedia;
    $amount=$property->transaction_type==='rent'?$property->monthly_rent:$property->price;
@endphp
<div class="rh-quick-hero">
    @if($cover)<img src="{{ asset('storage/'.($cover->thumbnail_path ?: $cover->path)) }}" alt="{{ $property->title }}" decoding="async">@else<span class="rh-prop-empty"><x-icon name="building" /></span>@endif
    <span class="rh-prop-badges">@if($property->is_sponsored)<span class="badge-soft warning"><x-icon name="bullhorn" /> {{ __('Sponsored') }}</span>@elseif($property->visibility==='featured')<span class="badge-soft primary"><x-icon name="star" /> {{ __('Featured') }}</span>@endif<span class="badge-soft secondary">{{ deal_label($property->transaction_type) }}</span></span>
</div>
<div class="modal-body">
    <span class="small text-muted"><x-icon name="location-dot" class="me-1" />{{ loc_name($property->area) ?: loc_name($property->district) ?: loc_name($property->province) ?: __('Afghanistan') }}</span>
    <h4 class="mb-1">{{ $property->title }}</h4>
    <p class="text-muted mb-2">{{ $property->address ?: __('Location details available from the Rahnama') }}</p>
    <div class="rh-prop-price mb-3" dir="ltr">{{ number_format((float)$amount) }} <small>{{ $property->currency }}{{ price_period($property->transaction_type) }}</small></div>
    <dl class="facts">
        <div><dt>{{ __('Type') }}</dt><dd>{{ enum_label($property->property_type) }}</dd></div>
        <div><dt>{{ __('Bedrooms') }}</dt><dd>{{ $property->bedrooms ?? '—' }}</dd></div>
        <div><dt>{{ __('Bathrooms') }}</dt><dd>{{ $property->bathrooms ?? '—' }}</dd></div>
        <div><dt>{{ __('Area') }}</dt><dd>{{ $property->total_area ? number_format((float)$property->total_area).' '.__(strtoupper($property->area_unit ?: 'sqm')) : '—' }}</dd></div>
    </dl>
    <h6 class="text-muted small text-uppercase mt-3 mb-2">{{ __('Listed by') }}</h6>
    <div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm rounded-3 avatar-soft-primary">@if($property->agency?->logo_path)<img src="{{ asset('storage/'.$property->agency->logo_path) }}" alt="">@else{{ initial($property->agency?->name, 'R') }}@endif</span><div><strong class="d-block text-heading">{{ $property->agency?->name }}</strong><span class="small text-muted">{{ $property->agency?->is_verified ? __('Verified Rahnama') : __('Rahnama office') }}</span></div></div>
</div>
<div class="modal-footer">
    <a class="btn btn-outline-soft" href="{{ route('marketplace.agency',$property->agency) }}">{{ __('View Rahnama') }}</a>
    <a class="btn btn-primary" href="{{ route('marketplace.property',$property) }}">{{ __('Full property details') }} <x-icon name="arrow-right" class="ms-1" /></a>
</div>
