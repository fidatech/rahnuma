@extends('layouts.app')
@section('title','Rahnuma — Afghanistan Property Marketplace')
@section('body-class','marketplace-page marketplace-home')
@section('content')
<section class="market-hero">
    <div class="market-hero__content">
        <div class="market-hero__eyebrow">Afghanistan property discovery</div>
        <h1>Find a property.<br><span>Know the Rahnama behind it.</span></h1>
        <p>Search verified-style Rahnama profiles and live property inventory across Afghanistan—houses, apartments, shops, land and more.</p>
        <form class="market-search-bar" method="get" action="{{ route('marketplace.properties') }}">
            <div class="market-search-bar__field market-search-bar__field--location"><label>Location</label><select name="province_id" data-placeholder="All Afghanistan"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}">{{ $p->name_en }} / {{ $p->name_fa }}</option>@endforeach</select></div>
            <div class="market-search-bar__field"><label>Looking for</label><select name="property_type" data-placeholder="Any property"><option value=""></option>@foreach(config('rahnuma.property_types') as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
            <div class="market-search-bar__field"><label>Deal type</label><select name="transaction_type"><option value="rent">Rent</option><option value="sale">Buy</option><option value="bay_wafa">Mortgage / بیع وفا</option></select></div>
            <button class="market-search-bar__button">Search <span>→</span></button>
        </form>
        <div class="market-hero__quick">
            <span>Popular:</span><a href="{{ route('marketplace.properties',['property_type'=>'apartment']) }}">Apartments</a><a href="{{ route('marketplace.properties',['property_type'=>'house']) }}">Houses</a><a href="{{ route('marketplace.properties',['property_type'=>'shop']) }}">Shops</a><a href="{{ route('marketplace.properties',['transaction_type'=>'rent']) }}">For rent</a>
        </div>
    </div>
    <div class="market-hero__visual">
        <div class="market-hero-card market-hero-card--a"><span>NEW</span><b>Premium homes</b><small>Fresh listings from active Rahnamas</small></div>
        <div class="market-hero-card market-hero-card--b"><span>⌖</span><b>Search by location</b><small>Province → district → area</small></div>
        <div class="market-hero-card market-hero-card--c"><span>✓</span><b>Know the agency</b><small>Company profile attached to every listing</small></div>
    </div>
</section>

<div class="market-home-stats">
    <div><b>{{ number_format($marketStats['properties']) }}</b><span>Live properties</span></div><i></i>
    <div><b>{{ number_format($marketStats['agencies']) }}</b><span>Rahnama offices</span></div><i></i>
    <div><b>{{ number_format($marketStats['provinces']) }}</b><span>Active provinces</span></div>
</div>

@if($sponsored->isNotEmpty())
<section class="market-section market-section--sponsored">
    <div class="market-section__head"><div><span>Promoted collection</span><h2>Sponsored properties</h2><p>Priority placements from Rahnamas currently promoting their inventory.</p></div><a href="{{ route('marketplace.properties',['sponsored'=>1]) }}">View sponsored <b>→</b></a></div>
    <div class="market-property-grid">@foreach($sponsored as $p)<x-marketplace.property-card :property="$p" />@endforeach</div>
</section>
@endif

@if($featured->isNotEmpty())
<section class="market-section">
    <div class="market-section__head"><div><span>Curated inventory</span><h2>Featured properties</h2><p>Standout properties selected for higher marketplace visibility.</p></div><a href="{{ route('marketplace.properties',['featured'=>1]) }}">Explore featured <b>→</b></a></div>
    <div class="market-property-grid">@foreach($featured as $p)<x-marketplace.property-card :property="$p" />@endforeach</div>
</section>
@endif

<section class="market-location-section">
    <div class="market-section__head"><div><span>Explore Afghanistan</span><h2>Browse by location</h2><p>Jump directly into areas with active property inventory.</p></div><a href="{{ route('marketplace.properties') }}">All locations <b>→</b></a></div>
    <div class="market-location-grid">
        @forelse($popularLocations as $item)
        <a class="market-location-card" href="{{ route('marketplace.properties',['province_id'=>$item['province']->id]) }}"><div><span>{{ $item['province']->code }}</span><h3>{{ $item['province']->name_en }}</h3><small>{{ $item['province']->name_fa }} · {{ number_format($item['count']) }} listings</small></div><b>→</b></a>
        @empty
        @foreach($provinces->take(6) as $p)<a class="market-location-card" href="{{ route('marketplace.properties',['province_id'=>$p->id]) }}"><div><span>{{ $p->code }}</span><h3>{{ $p->name_en }}</h3><small>{{ $p->name_fa }}</small></div><b>→</b></a>@endforeach
        @endforelse
    </div>
</section>

<section class="market-section">
    <div class="market-section__head"><div><span>Just listed</span><h2>New properties</h2><p>The newest public inventory added by Rahnamas.</p></div><a href="{{ route('marketplace.properties',['sort'=>'newest']) }}">See all new listings <b>→</b></a></div>
    <div class="market-property-grid">@forelse($latest as $p)<x-marketplace.property-card :property="$p" />@empty<div class="market-empty">No public listings yet.</div>@endforelse</div>
</section>

@if($apartments->isNotEmpty())
<section class="market-section market-section--soft">
    <div class="market-section__head"><div><span>Apartment living</span><h2>Apartments</h2><p>Explore apartments for rent, sale and mortgage.</p></div><a href="{{ route('marketplace.properties',['property_type'=>'apartment']) }}">All apartments <b>→</b></a></div>
    <div class="market-property-grid">@foreach($apartments as $p)<x-marketplace.property-card :property="$p" />@endforeach</div>
</section>
@endif

@if($houses->isNotEmpty())
<section class="market-section">
    <div class="market-section__head"><div><span>More space</span><h2>Houses & homes</h2><p>Family homes and independent houses across active locations.</p></div><a href="{{ route('marketplace.properties',['property_type'=>'house']) }}">All houses <b>→</b></a></div>
    <div class="market-property-grid">@foreach($houses as $p)<x-marketplace.property-card :property="$p" />@endforeach</div>
</section>
@endif

@if($featuredAgencies->isNotEmpty())
<section class="market-section market-section--agencies">
    <div class="market-section__head"><div><span>Local experts</span><h2>Featured Rahnamas</h2><p>Explore agencies, their public identity and current inventory.</p></div></div>
    <div class="market-agency-grid">@foreach($featuredAgencies as $agency)<x-marketplace.agency-card :agency="$agency" />@endforeach</div>
</section>
@endif

<section class="market-cta">
    <div><span>For Rahnama offices</span><h2>Manage your office and publish to the marketplace from one system.</h2><p>Properties entered in your workspace can become public listings without duplicate work.</p></div>
    <a class="btn" href="{{ route('login') }}">Open Rahnuma workspace</a>
</section>
@endsection
