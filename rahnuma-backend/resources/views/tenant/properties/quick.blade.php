@php
    $cover=$property->coverMedia;
    $amount=$property->transaction_type==='rent'?$property->monthly_rent:$property->price;
@endphp
<div class="quick-view-content">
    <div class="quick-view-hero quick-view-hero--property">
        @if($cover)<img src="{{ asset('storage/'.($cover->thumbnail_path ?: $cover->path)) }}" alt="{{ $property->title }}" decoding="async">@else<div class="quick-view-placeholder">{{ strtoupper(substr($property->property_type ?: 'P',0,1)) }}</div>@endif
        <div class="quick-view-hero__badges"><span class="badge {{ in_array($property->status,['available','active'])?'success':'neutral' }}">{{ $property->status }}</span><span class="badge neutral">{{ $property->visibility }}</span></div>
    </div>
    <div class="quick-view-body">
        <div class="quick-view-eyebrow">{{ $property->code }} · {{ $property->transaction_type==='bay_wafa'?'Mortgage':ucfirst($property->transaction_type) }}</div>
        <h2>{{ $property->title }}</h2>
        <p class="quick-view-location">{{ $property->address ?: ($property->area?->name_en ?: $property->district?->name_en ?: $property->province?->name_en ?: 'Location not completed') }}</p>
        <div class="quick-view-price">{{ number_format((float)$amount) }} <small>{{ $property->currency }}{{ $property->transaction_type==='rent'?' / month':'' }}</small></div>
        <div class="quick-view-facts">
            <div><span>Type</span><b>{{ ucfirst($property->property_type) }}</b></div>
            <div><span>Bedrooms</span><b>{{ $property->bedrooms ?? '—' }}</b></div>
            <div><span>Bathrooms</span><b>{{ $property->bathrooms ?? '—' }}</b></div>
            <div><span>Area</span><b>{{ $property->total_area ? number_format((float)$property->total_area).' '.strtoupper($property->area_unit) : '—' }}</b></div>
        </div>
        <div class="quick-view-section">
            <span>Primary owner</span>
            <div class="quick-view-person"><div class="mini-avatar">{{ strtoupper(mb_substr($property->owner?->name ?: '?',0,1)) }}</div><div><b>{{ $property->owner?->name ?: 'Not assigned' }}</b><small>{{ $property->owner?->phone ?: 'No phone on record' }}</small></div></div>
        </div>
        @if($property->description)<div class="quick-view-section"><span>Description</span><p>{{ \Illuminate\Support\Str::limit($property->description,280) }}</p></div>@endif
    </div>
    <div class="quick-view-actions">
        <a class="btn secondary" href="{{ route('tenant.properties.matches',$property) }}">Customer matches</a>
        <a class="btn" href="{{ route('tenant.properties.edit',$property) }}">Open Property Studio</a>
    </div>
</div>
