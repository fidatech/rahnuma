@extends('layouts.app')
@section('title', $agency->name)
@section('content')
<section class="card overflow-hidden">
    <div class="profile-cover" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif></div>
    <div class="profile-head">
        <span class="avatar avatar-xl rounded-3 avatar-soft-primary">@if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}" alt="" decoding="async">@else{{ initial($agency->name) }}@endif</span>
        <div class="ph-meta">
            <span class="badge-soft {{ $agency->is_verified ? 'success' : 'secondary' }} mb-1">@if($agency->is_verified)<x-icon name="circle-check" /> {{ __('Verified Rahnama profile') }}@else{{ __('Rahnama marketplace profile') }}@endif</span>
            <h3>{{ $agency->name }}</h3>
            <p class="text-muted mb-2">{{ $agency->tagline ?: $agency->description ?: __('Property guidance and active listings from a local Rahnama office.') }}</p>
            <div class="d-flex flex-wrap gap-3 small text-muted">
                @if($agency->marketplace_show_address && $agency->address)<span><x-icon name="location-dot" class="me-1" />{{ $agency->address }}</span>@endif
                @if($agency->marketplace_show_license && $agency->license_number)<span><x-icon name="id-badge" class="me-1" />{{ __('License') }} <span dir="ltr">{{ $agency->license_number }}</span></span>@endif
                @if($agency->office_hours)<span><x-icon name="clock" class="me-1" />{{ $agency->office_hours }}</span>@endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 pb-2">
            @if($agency->marketplace_show_phone && $agency->phone)<a href="tel:{{ $agency->phone }}" class="btn btn-outline-soft" data-native><x-icon name="phone" class="me-1" /> <span dir="ltr">{{ $agency->phone }}</span></a>@endif
            @if($agency->marketplace_show_whatsapp && $agency->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}" target="_blank" rel="noopener" class="btn btn-primary"><x-icon name="comment-dots" class="me-1" /> WhatsApp</a>@endif
        </div>
    </div>
</section>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><x-stat :label="__('Active listings')" :value="number_format($properties->total())" icon="building" tone="blue" /></div>
    <div class="col-6 col-lg-3"><x-stat :label="__('Profile status')" :value="$agency->is_verified ? __('Verified') : __('Active')" icon="shield-halved" tone="green" /></div>
    <div class="col-6 col-lg-3"><x-stat :label="__('Primary market')" :value="loc_name($agency->province) ?: __('Afghanistan')" icon="map-location-dot" tone="violet" /></div>
    <div class="col-6 col-lg-3"><x-stat :label="__('Languages')" :value="collect($agency->languages ?: [])->map(fn($x)=>strtoupper($x))->join(' · ') ?: 'FA · PS'" icon="globe" tone="amber" /></div>
</div>

@if($agency->description)
<div class="card">
    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="circle-info" /></span><div><h5>{{ __('About this Rahnama') }}</h5><small>{{ __('Local knowledge, represented clearly.') }}</small></div></div></div>
    <div class="card-body"><p class="mb-0" style="white-space:pre-line">{{ $agency->description }}</p></div>
</div>
@endif

<x-marketplace.section :kicker="__('Agency inventory')" :title="__('Properties from :agency', ['agency' => $agency->name])" :text="__('Public listings currently managed by this Rahnama.')">
    @if($properties->isEmpty())
        <div class="card"><div class="card-body"><x-workspace.empty icon="building" :title="__('No public listings from this agency right now.')" :description="__('Listings published by this office will appear here.')" /></div></div>
    @else
        <div class="row g-3">@foreach($properties as $p)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
        {{ $properties->links('partials.pagination') }}
    @endif
</x-marketplace.section>
@endsection
