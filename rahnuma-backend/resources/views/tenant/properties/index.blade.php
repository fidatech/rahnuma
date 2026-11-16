@extends('layouts.workspace')
@section('title', __('Property Inventory'))
@section('workspace')
@php
    $filtered = collect(['q','transaction_type','status','visibility'])->contains(fn ($key) => filled(request($key)));
@endphp
<x-page-header :title="__('Property Inventory')" icon="building" :subtitle="__('One place to manage availability, owners, marketplace exposure and deal readiness.')">
    <a class="btn btn-primary" href="{{ route('tenant.properties.create') }}"><x-icon name="plus" class="me-1" /> {{ __('Add property') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('Total inventory')" :value="number_format($stats['total'])" icon="building" tone="blue" :note="__('All property records')" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Available now')" :value="number_format($stats['available'])" icon="circle-check" tone="green" :note="__('Ready for customers')" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Marketplace')" :value="number_format($stats['public'])" icon="store" tone="violet" :note="__('Public / featured')" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Missing owner')" :value="number_format($stats['unowned'])" icon="user-slash" :tone="$stats['unowned'] ? 'amber' : 'blue'" :note="__('Needs relationship data')" /></div>
</div>

<div class="card">
    <div class="card-body">
        <form class="row g-2" method="get" data-filter-form>
            <div class="col-12 col-lg">
                <input type="search" class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Search title, code, owner, address or neighborhood') }}" aria-label="{{ __('Search') }}" data-filter-search autocomplete="off">
            </div>
            <div class="col-6 col-md col-lg-auto">
                <select class="form-select" name="transaction_type" aria-label="{{ __('Deal type') }}" data-plain onchange="this.form.requestSubmit()">
                    <option value="">{{ __('Any deal type') }}</option>
                    @foreach(config('rahnuma.transaction_types') as $s)<option value="{{ $s }}" @selected(request('transaction_type')===$s)>{{ deal_label($s, true) }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md col-lg-auto">
                <select class="form-select" name="status" aria-label="{{ __('Property status') }}" data-plain onchange="this.form.requestSubmit()">
                    <option value="">{{ __('Any status') }}</option>
                    @foreach(['available','active','rented','sold','mortgaged','inactive'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ enum_label($s) }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md col-lg-auto">
                <select class="form-select" name="visibility" aria-label="{{ __('Visibility') }}" data-plain onchange="this.form.requestSubmit()">
                    <option value="">{{ __('Any visibility') }}</option>
                    @foreach(['private','link','public','featured'] as $s)<option value="{{ $s }}" @selected(request('visibility')===$s)>{{ enum_label($s) }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-light-secondary" type="submit" aria-label="{{ __('Search') }}"><x-icon name="magnifying-glass" /></button>
                @if($filtered)<a class="btn btn-outline-soft" href="{{ route('tenant.properties.index') }}">{{ __('Clear all') }}</a>@endif
            </div>
        </form>
        <div class="small text-muted mt-2">{{ __(':count properties', ['count' => number_format($properties->total())]) }}</div>
    </div>
</div>

@if($properties->isEmpty())
    <div class="card"><div class="card-body">
        <x-workspace.empty icon="building" :title="$filtered ? __('Nothing found') : __('Your inventory is empty')" :description="$filtered ? __('Try another search or clear the filters.') : __('Register the first property and link it to its owner. It can stay private or be published to the marketplace.')" />
    </div></div>
@else
    <div class="row g-3">
        @foreach($properties as $p)
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
                <x-property-card :property="$p" :href="route('tenant.properties.edit',$p)" :quick="route('tenant.properties.quick',$p)" :quick-title="__('Property quick view')">
                    <button type="button" class="btn btn-sm btn-light-secondary" data-quick-view="{{ route('tenant.properties.quick',$p) }}" data-quick-view-title="{{ __('Property quick view') }}"><x-icon name="eye" class="me-1" /> {{ __('Quick view') }}</button>
                    <a class="btn btn-sm btn-soft-primary" href="{{ route('tenant.properties.edit',$p) }}"><x-icon name="pen" class="me-1" /> {{ __('Edit') }}</a>
                    <a class="btn btn-sm btn-outline-soft" href="{{ route('tenant.properties.matches',$p) }}"><x-icon name="bullseye" class="me-1" /> {{ __('Matches') }}</a>
                </x-property-card>
            </div>
        @endforeach
    </div>
    {{ $properties->links('partials.pagination') }}
@endif
@endsection
