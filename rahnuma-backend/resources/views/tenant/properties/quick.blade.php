@php
    $cover=$property->coverMedia;
    $amount=$property->transaction_type==='rent'?$property->monthly_rent:$property->price;
@endphp
<div class="rh-quick-hero">
    @if($cover)<img src="{{ asset('storage/'.($cover->thumbnail_path ?: $cover->path)) }}" alt="{{ $property->title }}" decoding="async">@else<span class="rh-prop-empty"><x-icon name="building" /></span>@endif
    <span class="rh-prop-badges"><span class="badge-soft {{ status_tone($property->status) }}">{{ enum_label($property->status) }}</span><span class="badge-soft secondary">{{ enum_label($property->visibility) }}</span></span>
</div>
<div class="modal-body">
    <span class="badge-code mb-2">{{ $property->code }} · {{ deal_label($property->transaction_type) }}</span>
    <h4 class="mb-1">{{ $property->title }}</h4>
    <p class="text-muted mb-2"><x-icon name="location-dot" class="me-1" />{{ $property->address ?: (loc_name($property->area) ?: loc_name($property->district) ?: loc_name($property->province) ?: __('Location not completed')) }}</p>
    <div class="rh-prop-price mb-3" dir="ltr">{{ number_format((float)$amount) }} <small>{{ $property->currency }}{{ price_period($property->transaction_type) }}</small></div>
    <dl class="facts">
        <div><dt>{{ __('Type') }}</dt><dd>{{ enum_label($property->property_type) }}</dd></div>
        <div><dt>{{ __('Bedrooms') }}</dt><dd>{{ $property->bedrooms ?? '—' }}</dd></div>
        <div><dt>{{ __('Bathrooms') }}</dt><dd>{{ $property->bathrooms ?? '—' }}</dd></div>
        <div><dt>{{ __('Area') }}</dt><dd>{{ $property->total_area ? number_format((float)$property->total_area).' '.__(strtoupper($property->area_unit ?: 'sqm')) : '—' }}</dd></div>
    </dl>
    <h6 class="text-muted small text-uppercase mt-3 mb-2">{{ __('Primary owner') }}</h6>
    <div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm avatar-soft-primary">{{ initial($property->owner?->name) }}</span><div><strong class="d-block text-heading">{{ $property->owner?->name ?: __('Not assigned') }}</strong><span class="small text-muted" dir="ltr">{{ $property->owner?->phone ?: __('No phone on record') }}</span></div></div>
    @if($property->description)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Description') }}</h6><p class="mb-0">{{ \Illuminate\Support\Str::limit($property->description,280) }}</p>@endif
</div>
<div class="modal-footer">
    <a class="btn btn-outline-soft" href="{{ route('tenant.properties.matches',$property) }}"><x-icon name="bullseye" class="me-1" /> {{ __('Customer matches') }}</a>
    <a class="btn btn-primary" href="{{ route('tenant.properties.edit',$property) }}"><x-icon name="pen" class="me-1" /> {{ __('Open property') }}</a>
</div>
