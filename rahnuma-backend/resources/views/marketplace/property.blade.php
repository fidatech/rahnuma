@extends('layouts.app')
@section('title', $property->title)
@section('content')
@php
    $isSponsored = $property->is_sponsored && (!$property->sponsored_until || $property->sponsored_until->isFuture());
    $place = $property->address ?: (loc_name($property->area) ?: loc_name($property->province));
    $features=['parking','electricity','solar','water','well','heating','cooling','furnished','internet','security','elevator','balcony','garden','basement','storage'];
@endphp
<div class="page-header card-soft">
    <div class="page-header-main">
        <span class="page-header-icon"><x-icon name="building" /></span>
        <div class="page-header-text">
            <nav class="crumbs mb-1" aria-label="breadcrumb"><a href="{{ route('marketplace.properties') }}">{{ __('Properties') }}</a><i class="sep">/</i><span class="current">{{ $property->code }}</span></nav>
            <h1>{{ $property->title }}</h1>
            <p class="page-header-sub"><x-icon name="location-dot" class="me-1" />{{ $place }}</p>
            <div class="d-flex flex-wrap gap-2 mt-2">@if($isSponsored)<span class="badge-soft warning"><x-icon name="bullhorn" /> {{ __('Sponsored') }}</span>@endif<span class="badge-soft primary">{{ enum_label($property->property_type) }}</span><span class="badge-soft secondary">{{ deal_label($property->transaction_type, true) }}</span></div>
        </div>
    </div>
    <div class="page-header-actions text-end">
        <div><span class="small text-muted d-block">{{ __('Asking') }}</span><span class="rh-prop-price" dir="ltr">{{ number_format((float)($property->transaction_type==='rent'?$property->monthly_rent:$property->price)) }} <small>{{ $property->currency }}{{ price_period($property->transaction_type) }}</small></span></div>
    </div>
</div>

<div class="rh-gallery {{ $property->media->count()<2 ? 'is-single' : '' }}">
    @forelse($property->media->take(5) as $i=>$m)<x-marketplace.photo :class="$i===0?'is-primary':''" :src="asset('storage/'.$m->path)" :alt="$property->title" :code="$i===0?$property->code:null" :priority="$i===0" :brand="$i===0" />@empty<div class="rh-prop-empty"><x-icon name="image" /><small>{{ __('Photos coming soon') }}</small></div>@endforelse
</div>

<div class="row g-3">
    <main class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col"><div class="metric-box"><span>{{ __('Bedrooms') }}</span><strong>{{ $property->bedrooms ?: '—' }}</strong></div></div>
                    <div class="col"><div class="metric-box"><span>{{ __('Bathrooms') }}</span><strong>{{ $property->bathrooms ?: '—' }}</strong></div></div>
                    <div class="col"><div class="metric-box"><span>{{ __(strtoupper($property->area_unit ?: 'sqm')) }}</span><strong>{{ $property->total_area ? number_format((float)$property->total_area) : '—' }}</strong></div></div>
                    <div class="col"><div class="metric-box"><span>{{ __('Floor') }}</span><strong>{{ $property->floor_number ?? '—' }}</strong></div></div>
                    <div class="col-12 col-md"><div class="metric-box"><span>{{ __('Listing code') }}</span><strong dir="ltr">{{ $property->code }}</strong></div></div>
                </div>
            </div>
        </div>
        @if($property->description)
        <div class="card">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="circle-info" /></span><div><h5>{{ __('Property overview') }}</h5><small>{{ __('About the property') }}</small></div></div></div>
            <div class="card-body"><p class="mb-0" style="white-space:pre-line">{{ $property->description }}</p></div>
        </div>
        @endif
        @if(collect($features)->contains(fn($f)=>$property->$f))
        <div class="card">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="list-check" /></span><div><h5>{{ __('Amenities') }}</h5><small>{{ __('Included features') }}</small></div></div></div>
            <div class="card-body d-flex flex-wrap gap-2">@foreach($features as $f)@if($property->$f)<span class="badge-soft success"><x-icon name="check" /> {{ enum_label($f) }}</span>@endif @endforeach</div>
        </div>
        @endif
        <div class="card">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="location-dot" /></span><div><h5>{{ loc_name($property->area) ?: loc_name($property->district) ?: loc_name($property->province) ?: __('Afghanistan') }}</h5><small>{{ __('Location') }}</small></div></div></div>
            <div class="card-body"><p class="mb-0">{{ $property->address ?: __('Detailed address available from the listing Rahnama.') }}</p></div>
        </div>
    </main>
    <aside class="col-12 col-lg-4">
        <div class="rh-sticky">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar avatar-lg rounded-3 avatar-soft-primary">@if($property->agency?->logo_path)<img src="{{ asset('storage/'.$property->agency->logo_path) }}" alt="">@else{{ initial($property->agency?->name, 'R') }}@endif</span>
                        <div class="min-w-0"><span class="small text-muted">{{ __('Listed by') }}</span><h3 class="h6 mb-0">{{ $property->agency?->name }}</h3><span class="small {{ $property->agency?->is_verified ? 'text-success' : 'text-muted' }}">@if($property->agency?->is_verified)<x-icon name="circle-check" /> {{ __('Verified Rahnama') }}@else{{ __('Rahnama marketplace profile') }}@endif</span></div>
                    </div>
                    @if($property->agency?->tagline)<p class="small text-muted">{{ $property->agency->tagline }}</p>@endif
                    <div class="d-grid gap-2">
                        <a class="btn btn-outline-soft" href="{{ route('marketplace.agency',$property->agency) }}"><x-icon name="briefcase" class="me-1" /> {{ __('View company profile') }}</a>
                        @if($property->agency?->marketplace_show_whatsapp && $property->agency?->whatsapp)<a class="btn btn-primary" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/\D/','',$property->agency->whatsapp) }}"><x-icon name="comment-dots" class="me-1" /> {{ __('WhatsApp Rahnama') }}</a>@endif
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="calendar-check" /></span><div><h5>{{ __('Request a viewing') }}</h5><small>{{ __('Your request goes directly to the Rahnama managing this listing.') }}</small></div></div></div>
                <form class="card-body d-grid gap-3" method="post" action="{{ route('marketplace.inquiry',$property) }}">@csrf
                    <div><label class="form-label">{{ __('Name') }} <span class="req">*</span></label><input class="form-control" name="name" required></div>
                    <div><label class="form-label">{{ __('Phone') }} <span class="req">*</span></label><input class="form-control" name="phone" required inputmode="tel" dir="ltr"></div>
                    <div><label class="form-label">{{ __('WhatsApp') }}</label><input class="form-control" name="whatsapp" inputmode="tel" dir="ltr"></div>
                    <div><label class="form-label">{{ __('Message') }}</label><textarea class="form-control" name="message" rows="3" placeholder="{{ __('Preferred time or questions') }}"></textarea></div>
                    <button class="btn btn-primary"><x-icon name="paper-plane" class="me-1" /> {{ __('Send inquiry') }}</button>
                </form>
            </div>
        </div>
    </aside>
</div>

@if($similar->isNotEmpty())
<x-marketplace.section class="mt-4" :kicker="__('Keep exploring')" :title="__('Similar properties')" :text="__('More listings of this type in this market.')">
    <div class="row g-3">@foreach($similar as $p)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
</x-marketplace.section>
@endif
@endsection
