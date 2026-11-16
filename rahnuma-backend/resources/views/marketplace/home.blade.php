@extends('layouts.app')
@section('title', __('Afghanistan Property Marketplace'))
@section('content')
<section class="rh-hero">
    <div class="rh-hero-text">
        <span class="rh-hero-kicker"><x-icon name="location-dot" /> {{ __('Afghanistan property discovery') }}</span>
        <h1>{{ __('Find a property.') }}<br><span>{{ __('Know the Rahnama behind it.') }}</span></h1>
        <p>{{ __('Search Rahnama profiles and live property inventory across Afghanistan: houses, apartments, shops, land and more.') }}</p>
        <form class="rh-search card" method="get" action="{{ route('marketplace.properties') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md"><label class="form-label">{{ __('Location') }}</label><select class="form-select" name="province_id" data-placeholder="{{ __('All Afghanistan') }}"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}">{{ loc_name($p) }}</option>@endforeach</select></div>
                <div class="col-6 col-md"><label class="form-label">{{ __('Looking for') }}</label><select class="form-select" name="property_type" data-placeholder="{{ __('Any property') }}"><option value=""></option>@foreach(config('rahnuma.property_types') as $t)<option value="{{ $t }}">{{ enum_label($t) }}</option>@endforeach</select></div>
                <div class="col-6 col-md"><label class="form-label">{{ __('Deal type') }}</label><select class="form-select" name="transaction_type"><option value="rent">{{ __('Rent') }}</option><option value="sale">{{ __('Buy') }}</option><option value="bay_wafa">{{ __('Mortgage / Bay-e-Wafa') }}</option></select></div>
                <div class="col-12 col-md-auto"><button class="btn btn-gradient w-100"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button></div>
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
    <div class="rh-hero-side">
        <div class="rh-hero-card"><span class="sc-icon icon-soft primary"><x-icon name="house" /></span><div><strong>{{ __('Premium homes') }}</strong><small>{{ __('Fresh listings from active Rahnamas') }}</small></div></div>
        <div class="rh-hero-card"><span class="sc-icon icon-soft success"><x-icon name="map-location-dot" /></span><div><strong>{{ __('Search by location') }}</strong><small>{{ __('Province, district, area') }}</small></div></div>
        <div class="rh-hero-card"><span class="sc-icon icon-soft warning"><x-icon name="shield-halved" /></span><div><strong>{{ __('Know the agency') }}</strong><small>{{ __('Company profile attached to every listing') }}</small></div></div>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><x-stat :label="__('Live properties')" :value="number_format($marketStats['properties'])" icon="building" tone="blue" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Rahnama offices')" :value="number_format($marketStats['agencies'])" icon="briefcase" tone="violet" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Active provinces')" :value="number_format($marketStats['provinces'])" icon="map-location-dot" tone="green" /></div>
</div>

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
    <div class="row g-3">
        @forelse($popularLocations as $item)
            <div class="col-6 col-md-4 col-xl-2"><a class="card card-hover h-100 mb-0 text-decoration-none text-reset" href="{{ route('marketplace.properties',['province_id'=>$item['province']->id]) }}"><div class="card-body"><span class="badge-code mb-2">{{ $item['province']->code }}</span><h3 class="h6 mb-1">{{ loc_name($item['province']) }}</h3><span class="small text-muted">{{ __(':count listings', ['count' => number_format($item['count'])]) }}</span></div></a></div>
        @empty
            @foreach($provinces->take(6) as $p)<div class="col-6 col-md-4 col-xl-2"><a class="card card-hover h-100 mb-0 text-decoration-none text-reset" href="{{ route('marketplace.properties',['province_id'=>$p->id]) }}"><div class="card-body"><span class="badge-code mb-2">{{ $p->code }}</span><h3 class="h6 mb-0">{{ loc_name($p) }}</h3></div></a></div>@endforeach
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
<x-marketplace.section :kicker="__('Local experts')" :title="__('Featured Rahnamas')" :text="__('Explore agencies, their public identity and current inventory.')">
    <div class="row g-3">@foreach($featuredAgencies as $agency)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.agency-card :agency="$agency" /></div>@endforeach</div>
</x-marketplace.section>
@endif

<section class="card welcome-card">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="small text-uppercase fw-semibold opacity-75">{{ __('For Rahnama offices') }}</span><h3 class="mb-1">{{ __('Manage your office and publish to the marketplace from one system.') }}</h3><p class="mb-0">{{ __('Properties entered in your workspace can become public listings without duplicate work.') }}</p></div>
        <a class="btn btn-light" href="{{ route('login') }}"><x-icon name="right-to-bracket" class="me-1" /> {{ __('Open Rahnuma workspace') }}</a>
    </div>
</section>
@endsection
