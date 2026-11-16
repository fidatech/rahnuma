@extends('layouts.workspace')
@section('title', __('Marketplace Promotions'))
@section('workspace')
<x-page-header :title="__('Marketplace Promotions')" icon="bullhorn" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Marketplace Promotions')]]" :subtitle="__('Manage sponsored property placements and future paid visibility.')">
    <a class="btn btn-outline-soft" href="{{ route('home') }}" target="_blank" rel="noopener"><x-icon name="arrow-up-right-from-square" class="me-1" /> {{ __('Open marketplace') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6"><x-stat :label="__('Sponsored now')" :value="number_format($sponsoredCount)" icon="bullhorn" tone="amber" /></div>
    <div class="col-12 col-md-6"><x-stat :label="__('Featured agencies')" :value="number_format($featuredAgencies)" icon="star" tone="violet" /></div>
</div>

<div class="card">
    <div class="card-header">
        <div><h5 class="card-title mb-0">{{ __('Public property inventory') }}</h5><div class="small text-muted">{{ __('Choose listings to promote in the Sponsored collection.') }}</div></div>
        <form method="get" class="d-flex gap-2"><input class="form-control form-control-sm" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search property code or title') }}"><button class="btn btn-sm btn-light-secondary" aria-label="{{ __('Search') }}"><x-icon name="magnifying-glass" /></button></form>
    </div>
    <div class="card-body">
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Property') }}</th><th>{{ __('Agency') }}</th><th>{{ __('Placement') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach($properties as $p)
                @php($img=$p->coverMedia)
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-md rounded-3 avatar-soft-primary">@if($img)<img src="{{ asset('storage/'.($img->thumbnail_path ?: $img->path)) }}" alt="" loading="lazy" decoding="async">@else<x-icon name="building" />@endif</span><div class="min-w-0"><strong class="d-block text-heading">{{ $p->title }}</strong><span class="small text-muted" dir="ltr">{{ $p->code }}</span></div></div></td>
                    <td>{{ $p->agency?->name }}<div class="small text-muted">{{ loc_name($p->province) }}</div></td>
                    <td><span class="badge-soft {{ $p->is_sponsored && (!$p->sponsored_until || $p->sponsored_until->isFuture()) ? 'success' : 'secondary' }}">{{ $p->is_sponsored ? __('Sponsored') : __('Organic') }}</span></td>
                    <td class="text-end text-nowrap">@if($p->is_sponsored)<form method="post" action="{{ route('platform.marketplace.unsponsor',$p) }}" class="d-inline">@csrf<button class="btn btn-sm btn-light-secondary">{{ __('Stop promotion') }}</button></form>@else<form method="post" action="{{ route('platform.marketplace.sponsor',$p) }}" class="d-inline">@csrf<input type="hidden" name="days" value="30"><button class="btn btn-sm btn-primary">{{ __('Sponsor 30 days') }}</button></form>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $properties->links('partials.pagination') }}
    </div>
</div>

<div class="card">
    <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Featured Rahnamas') }}</h5><div class="small text-muted">{{ __('Control which agencies receive priority placement in marketplace agency collections.') }}</div></div></div>
    <div class="card-body">
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Public listings') }}</th><th>{{ __('Placement') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach($agencies as $a)
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-md rounded-3 avatar-soft-primary">@if($a->logo_path)<img src="{{ asset('storage/'.$a->logo_path) }}" alt="" loading="lazy" decoding="async">@else{{ initial($a->name) }}@endif</span><div class="min-w-0"><strong class="d-block text-heading">{{ $a->name }}</strong><span class="small text-muted" dir="ltr">{{ $a->license_number ?: __('No license published') }}</span></div></div></td>
                    <td>{{ number_format($a->public_properties_count) }}</td>
                    <td><span class="badge-soft {{ $a->marketplace_featured ? 'success' : 'secondary' }}">{{ $a->marketplace_featured ? __('Featured') : __('Organic') }}</span></td>
                    <td class="text-end"><form method="post" action="{{ route('platform.marketplace.agency-feature',$a) }}" class="d-inline">@csrf<button class="btn btn-sm {{ $a->marketplace_featured ? 'btn-light-secondary' : 'btn-primary' }}">{{ $a->marketplace_featured ? __('Remove feature') : __('Feature Rahnama') }}</button></form></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
