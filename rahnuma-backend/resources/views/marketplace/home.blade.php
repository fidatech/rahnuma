@extends('layouts.app')
@section('title', __('Afghanistan Property Marketplace'))
@section('content')
@php
    $spot = $sponsored->first() ?? $featured->first() ?? $latest->first();
    $spotImg = $spot?->coverMedia;
    $spotAmount = $spot ? ($spot->transaction_type === 'rent' ? $spot->monthly_rent : $spot->price) : null;
@endphp
<section class="rh-hero">
    <div class="rh-hero-main">
        <div class="rh-hero-text">
            <span class="rh-hero-kicker"><x-icon name="landmark" /> {{ __('Online property services · :count provinces', ['count' => number_format($provinces->count())]) }}</span>
            <h1>{{ __('Find a property.') }}<br><span>{{ __('Know the Rahnama behind it.') }}</span></h1>
            <p>{{ __('Search Rahnama profiles and live property inventory across Afghanistan: houses, apartments, shops, land and more.') }}</p>
            <form class="rh-search" method="get" action="{{ route('marketplace.properties') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md"><label class="form-label">{{ __('Location') }}</label><select class="form-select" name="province_id" data-placeholder="{{ __('All Afghanistan') }}"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}">{{ loc_name($p) }}</option>@endforeach</select></div>
                    <div class="col-6 col-md"><label class="form-label">{{ __('Looking for') }}</label><select class="form-select" name="property_type" data-placeholder="{{ __('Any property') }}"><option value=""></option>@foreach(config('rahnuma.property_types') as $t)<option value="{{ $t }}">{{ enum_label($t) }}</option>@endforeach</select></div>
                    <div class="col-6 col-md"><label class="form-label">{{ __('Deal type') }}</label><select class="form-select" name="transaction_type"><option value="rent">{{ __('Rent') }}</option><option value="sale">{{ __('Buy') }}</option><option value="bay_wafa">{{ __('Mortgage / Bay-e-Wafa') }}</option></select></div>
                    <div class="col-12 col-md-auto"><button class="btn btn-gold w-100"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button></div>
                </div>
            </form>
            <div class="rh-hero-chips">
                <span>{{ __('Popular') }}:</span>
                <a href="{{ route('marketplace.properties',['property_type'=>'apartment']) }}">{{ __('Apartments') }}</a>
                <a href="{{ route('marketplace.properties',['property_type'=>'house']) }}">{{ __('Houses') }}</a>
                <a href="{{ route('marketplace.properties',['property_type'=>'shop']) }}">{{ __('Shops') }}</a>
                <a href="{{ route('marketplace.properties',['transaction_type'=>'rent']) }}">{{ __('For rent') }}</a>
            </div>
        </div>
        <div class="rh-hero-arch">
            @if ($spot)
                <a class="rh-arch" href="{{ route('marketplace.property', $spot) }}">
                    @if ($spotImg)<img src="{{ asset('storage/'.$spotImg->path) }}" alt="{{ $spot->title }}" decoding="async">@else<span class="rh-arch-empty"><x-icon name="building" /></span>@endif
                    <span class="rh-arch-card">
                        <span class="min-w-0"><strong class="d-block text-truncate">{{ $spot->title }}</strong><small class="d-block text-truncate">{{ loc_name($spot->area) ?: loc_name($spot->district) ?: loc_name($spot->province) ?: __('Afghanistan') }}</small></span>
                        <span class="rh-arch-price" dir="ltr">{{ number_format((float) $spotAmount) }} <small>{{ $spot->currency }}</small></span>
                    </span>
                </a>
            @else
                <span class="rh-arch"><span class="rh-arch-empty"><x-icon name="building" /></span></span>
            @endif
        </div>
    </div>
    <div class="rh-hero-stats">
        <div><strong>{{ number_format($marketStats['properties']) }}</strong><span>{{ __('Live properties') }}</span></div>
        <div><strong>{{ number_format($marketStats['agencies']) }}</strong><span>{{ __('Rahnama offices') }}</span></div>
        <div><strong>{{ number_format($marketStats['provinces']) }}</strong><span>{{ __('Active provinces') }}</span></div>
    </div>
</section>

<section class="rh-section rh-anchor" id="verify">
    <div class="rh-section-head"><div><h2>{{ __('Services') }}</h2></div></div>
    <div class="rh-services">
        <a class="rh-service" href="{{ route('marketplace.properties') }}">
            <span class="rh-service-icon"><x-icon name="magnifying-glass" /></span>
            <strong>{{ __('Search properties') }}</strong>
            <span>{{ __('Browse sale, rent and bay wafa listings by province and type.') }}</span>
            <em>{{ __('Open service') }} <x-icon name="arrow-right" /></em>
        </a>
        <div class="rh-service is-form">
            <span class="rh-service-icon"><x-icon name="shield-halved" /></span>
            <strong>{{ __('Verify a transaction record') }}</strong>
            <span>{{ __('Enter the verification code printed on a transaction record.') }}</span>
            <form data-native data-verify-url="{{ url('/verify') }}" onsubmit="event.preventDefault();var c=this.code.value.trim();if(c){location.href=this.dataset.verifyUrl+'/'+encodeURIComponent(c);}">
                <div class="input-group" dir="ltr"><input class="form-control" name="code" required autocomplete="off" aria-label="{{ __('Verification code') }}" placeholder="{{ __('Verification code') }}"><button class="btn btn-primary">{{ __('Verify') }}</button></div>
            </form>
        </div>
        <a class="rh-service" href="{{ route('login') }}">
            <span class="rh-service-icon"><x-icon name="briefcase" /></span>
            <strong>{{ __('Rahnama office workspace') }}</strong>
            <span>{{ __('Manage properties, customers and deals, and publish listings.') }}</span>
            <em>{{ __('Sign in') }} <x-icon name="arrow-right" /></em>
        </a>
        <a class="rh-service" href="{{ route('login') }}">
            <span class="rh-service-icon"><x-icon name="landmark" /></span>
            <strong>{{ __('Regulatory workspace') }}</strong>
            <span>{{ __('Oversight and reporting for the responsible authorities.') }}</span>
            <em>{{ __('Sign in') }} <x-icon name="arrow-right" /></em>
        </a>
    </div>
</section>

@if($sponsored->isNotEmpty())
<x-marketplace.section :kicker="__('Promoted collection')" :title="__('Sponsored properties')" :text="__('Priority placements from Rahnamas currently promoting their inventory.')" :href="route('marketplace.properties',['sponsored'=>1])" :link="__('View sponsored')">
    <div class="row g-3">@foreach($sponsored as $p)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
</x-marketplace.section>
@endif

@if($featured->isNotEmpty())
<x-marketplace.section :kicker="__('Curated inventory')" :title="__('Featured properties')" :text="__('Standout properties selected for higher marketplace visibility.')" :href="route('marketplace.properties',['featured'=>1])" :link="__('Explore featured')">
    <div class="row g-3">@foreach($featured as $p)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
</x-marketplace.section>
@endif

<x-marketplace.section :kicker="__('Explore Afghanistan')" :title="__('Browse by location')" :text="__('Jump directly into areas with active property inventory.')" :href="route('marketplace.properties')" :link="__('All locations')">
    <div class="rh-locs">
        @forelse($popularLocations as $item)
            <a class="rh-loc" href="{{ route('marketplace.properties',['province_id'=>$item['province']->id]) }}"><span class="rh-loc-code" dir="ltr">{{ $item['province']->code }}</span><strong>{{ loc_name($item['province']) }}</strong><small>{{ __(':count listings', ['count' => number_format($item['count'])]) }}</small></a>
        @empty
            @foreach($provinces->take(8) as $p)<a class="rh-loc" href="{{ route('marketplace.properties',['province_id'=>$p->id]) }}"><strong>{{ loc_name($p) }}</strong></a>@endforeach
        @endforelse
    </div>
</x-marketplace.section>

<x-marketplace.section :kicker="__('Just listed')" :title="__('New properties')" :text="__('The newest public inventory added by Rahnamas.')" :href="route('marketplace.properties',['sort'=>'newest'])" :link="__('See all new listings')">
    @if($latest->isEmpty())
        <div class="card"><div class="card-body"><x-workspace.empty icon="building" :title="__('No public listings yet.')" :description="__('Listings published by Rahnama offices will appear here.')" /></div></div>
    @else
        <div class="row g-3">@foreach($latest as $p)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
    @endif
</x-marketplace.section>

@if($apartments->isNotEmpty())
<x-marketplace.section :kicker="__('Apartment living')" :title="__('Apartments')" :text="__('Explore apartments for rent, sale and mortgage.')" :href="route('marketplace.properties',['property_type'=>'apartment'])" :link="__('All apartments')">
    <div class="row g-3">@foreach($apartments as $p)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
</x-marketplace.section>
@endif

@if($houses->isNotEmpty())
<x-marketplace.section :kicker="__('More space')" :title="__('Houses & homes')" :text="__('Family homes and independent houses across active locations.')" :href="route('marketplace.properties',['property_type'=>'house'])" :link="__('All houses')">
    <div class="row g-3">@foreach($houses as $p)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
</x-marketplace.section>
@endif

@if($featuredAgencies->isNotEmpty())
<x-marketplace.section id="rahnamas" class="rh-anchor" :kicker="__('Local experts')" :title="__('Featured Rahnamas')" :text="__('Explore agencies, their public identity and current inventory.')">
    <div class="row g-3">@foreach($featuredAgencies as $agency)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.agency-card :agency="$agency" /></div>@endforeach</div>
</x-marketplace.section>
@endif

<section class="rh-cta">
    <div><span class="rh-cta-kicker">{{ __('For Rahnama offices') }}</span><h3>{{ __('Manage your office and publish to the marketplace from one system.') }}</h3><p>{{ __('Properties entered in your workspace can become public listings without duplicate work.') }}</p></div>
    <a class="btn btn-gold btn-lg" href="{{ route('login') }}"><x-icon name="right-to-bracket" class="me-1" /> {{ __('Open Rahnuma workspace') }}</a>
</section>
@endsection
