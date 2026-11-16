@extends('layouts.app')
@section('title',$property->title.' — Rahnuma')
@section('body-class','marketplace-page')
@section('content')
<div class="property-public-head">
    <div><a href="{{ route('marketplace.properties') }}">← Back to properties</a><div class="property-public-head__badges">@if($property->is_sponsored && (!$property->sponsored_until || $property->sponsored_until->isFuture()))<span class="market-badge market-badge--sponsored">Sponsored</span>@endif<span class="market-badge market-badge--plain">{{ ucfirst($property->property_type) }}</span><span class="market-badge market-badge--plain">{{ $property->transaction_type==='bay_wafa'?'Mortgage / بیع وفا':ucfirst($property->transaction_type) }}</span></div><h1>{{ $property->title }}</h1><p>⌖ {{ $property->address ?: ($property->area?->name_en ?: $property->province?->name_en) }}</p></div>
    <div class="property-public-price"><span>Asking</span><b>{{ number_format((float)($property->transaction_type==='rent'?$property->monthly_rent:$property->price)) }}</b><small>{{ $property->currency }}{{ $property->transaction_type==='rent'?' / month':'' }}</small></div>
</div>

<div class="property-public-gallery {{ $property->media->count()<2?'single':'' }}">
    @forelse($property->media->take(5) as $i=>$m)<img class="{{ $i===0?'primary':'' }}" src="{{ asset('storage/'.$m->path) }}" alt="{{ $property->title }}" decoding="async" @if($i===0) fetchpriority="high" @else loading="lazy" @endif>@empty<div class="property-public-gallery__empty"><span>R</span><b>Photos coming soon</b></div>@endforelse
</div>

<div class="property-public-layout">
    <main>
        <section class="property-public-facts"><div><b>{{ $property->bedrooms ?: '—' }}</b><span>Bedrooms</span></div><div><b>{{ $property->bathrooms ?: '—' }}</b><span>Bathrooms</span></div><div><b>{{ $property->total_area ? number_format((float)$property->total_area) : '—' }}</b><span>{{ strtoupper($property->area_unit ?: 'sqm') }}</span></div><div><b>{{ $property->floor_number ?? '—' }}</b><span>Floor</span></div><div><b>{{ $property->code }}</b><span>Listing code</span></div></section>
        @if($property->description)<section class="property-public-section"><span>About the property</span><h2>Property overview</h2><p>{{ $property->description }}</p></section>@endif
        @php($features=['parking','electricity','solar','water','well','heating','cooling','furnished','internet','security','elevator','balcony','garden','basement','storage'])
        @if(collect($features)->contains(fn($f)=>$property->$f))<section class="property-public-section"><span>Included features</span><h2>Amenities</h2><div class="public-feature-grid">@foreach($features as $f)@if($property->$f)<div><i>✓</i>{{ ucfirst($f) }}</div>@endif @endforeach</div></section>@endif
        <section class="property-public-section"><span>Location</span><h2>{{ $property->area?->name_en ?: $property->district?->name_en ?: $property->province?->name_en ?: 'Afghanistan' }}</h2><p>{{ $property->address ?: 'Detailed address available from the listing Rahnama.' }}</p></section>
    </main>
    <aside class="property-contact-rail">
        <div class="property-agency-box">
            <div class="property-agency-box__top"><div class="market-agency-mark large">@if($property->agency?->logo_path)<img src="{{ asset('storage/'.$property->agency->logo_path) }}">@else{{ strtoupper(substr($property->agency?->name ?: 'R',0,1)) }}@endif</div><div><span>Listed by</span><h3>{{ $property->agency?->name }}</h3><small>{{ $property->agency?->is_verified?'✓ Verified Rahnama':'Rahnama marketplace profile' }}</small></div></div>
            @if($property->agency?->tagline)<p>{{ $property->agency->tagline }}</p>@endif
            <a class="btn secondary block" href="{{ route('marketplace.agency',$property->agency) }}">View company profile</a>
            @if($property->agency?->marketplace_show_whatsapp && $property->agency?->whatsapp)<a class="btn block" target="_blank" href="https://wa.me/{{ preg_replace('/\D/','',$property->agency->whatsapp) }}">WhatsApp Rahnama</a>@endif
        </div>
        <div class="property-inquiry-box"><span>Interested in this property?</span><h3>Request a viewing</h3><p>Your request goes directly to the Rahnama managing this listing.</p><form method="post" action="{{ route('marketplace.inquiry',$property) }}">@csrf<div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Phone</label><input name="phone" required></div><div class="field"><label>WhatsApp</label><input name="whatsapp"></div><div class="field"><label>Message</label><textarea name="message" rows="3" placeholder="Preferred time or questions"></textarea></div><button class="btn block">Send inquiry</button></form></div>
    </aside>
</div>

@if($similar->isNotEmpty())<section class="market-section"><div class="market-section__head"><div><span>Keep exploring</span><h2>Similar properties</h2><p>More {{ $property->property_type }} listings in this market.</p></div></div><div class="market-property-grid">@foreach($similar as $p)<x-marketplace.property-card :property="$p" />@endforeach</div></section>@endif
@endsection
