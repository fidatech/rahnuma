@extends('layouts.app')
@section('title',$agency->name.' — Rahnuma')
@section('body-class','marketplace-page')
@section('content')
<section class="agency-public-hero">
    <div class="agency-public-hero__cover" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif></div>
    <div class="agency-public-hero__body">
        <div class="agency-public-hero__logo">@if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}" decoding="async">@else{{ strtoupper(substr($agency->name,0,1)) }}@endif</div>
        <div class="agency-public-hero__main"><div class="agency-public-hero__eyebrow">{{ $agency->is_verified?'✓ Verified Rahnama profile':'Rahnama marketplace profile' }}</div><h1>{{ $agency->name }}</h1><p>{{ $agency->tagline ?: $agency->description ?: 'Property guidance and active listings from a local Rahnama office.' }}</p>
            <div class="agency-public-hero__meta">
                @if($agency->marketplace_show_address && $agency->address)<span>⌖ {{ $agency->address }}</span>@endif
                @if($agency->marketplace_show_license && $agency->license_number)<span>License {{ $agency->license_number }}</span>@endif
                @if($agency->office_hours)<span>{{ $agency->office_hours }}</span>@endif
            </div>
        </div>
        <div class="agency-public-hero__actions">
            @if($agency->marketplace_show_phone && $agency->phone)<a href="tel:{{ $agency->phone }}" class="btn secondary">Call {{ $agency->phone }}</a>@endif
            @if($agency->marketplace_show_whatsapp && $agency->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}" target="_blank" class="btn">WhatsApp</a>@endif
        </div>
    </div>
</section>

<div class="agency-public-stats"><div><b>{{ number_format($properties->total()) }}</b><span>Active listings</span></div><div><b>{{ $agency->is_verified?'Verified':'Active' }}</b><span>Profile status</span></div><div><b>{{ $agency->province?->name_en ?: 'Afghanistan' }}</b><span>Primary market</span></div><div><b>{{ collect($agency->languages ?: [])->map(fn($x)=>strtoupper($x))->join(' · ') ?: 'FA · PS' }}</b><span>Languages</span></div></div>

@if($agency->description)<section class="agency-about"><div><span>About this Rahnama</span><h2>Local knowledge, represented clearly.</h2></div><p>{{ $agency->description }}</p></section>@endif

<section class="market-section market-section--agency-inventory">
    <div class="market-section__head"><div><span>Agency inventory</span><h2>Properties from {{ $agency->name }}</h2><p>Public listings currently managed by this Rahnama.</p></div><div class="listing-count">{{ number_format($properties->total()) }} listings</div></div>
    <div class="market-property-grid">@forelse($properties as $p)<x-marketplace.property-card :property="$p" />@empty<div class="market-empty">No public listings from this agency right now.</div>@endforelse</div>
    <div class="pagination">{{ $properties->links() }}</div>
</section>
@endsection
