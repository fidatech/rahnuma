@extends('layouts.workspace')
@section('title', __('Customer matches'))
@section('workspace')
<x-page-header :title="__('Customers for :property', ['property' => $property->title])" icon="bullseye" :crumbs="[[__('Property Inventory'), route('tenant.properties.index')], [__('Customer matches')]]" :subtitle="__('People whose saved requirements align with this property.')" />

<div class="alert alert-primary d-flex justify-content-between flex-wrap gap-2"><strong>{{ __(':count customer matches', ['count' => count($requirements)]) }}</strong><span>{{ __('Contact the strongest matches first.') }}</span></div>

@if(count($requirements) === 0)
    <div class="card"><div class="card-body"><x-workspace.empty icon="bullseye" :title="__('No saved customer demand matches yet')" :description="__('Create customer requirements so Rahnuma can connect new inventory to real demand.')" /></div></div>
@else
    <div class="row g-3">
    @foreach($requirements as $r)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 mb-0"><div class="card-body d-flex gap-3">
                <span class="avatar avatar-md avatar-soft-purple">{{ initial($r->customer?->name) }}</span>
                <div class="min-w-0">
                    <h3 class="h6 mb-0">{{ $r->customer?->name }}</h3>
                    <p class="small text-muted mb-2" dir="ltr">{{ $r->customer?->phone ?: __('No phone') }}</p>
                    <span class="badge-soft primary">{{ enum_label($r->property_type) }} · <span dir="ltr">{{ number_format((float)$r->min_price) }}–{{ number_format((float)$r->max_price) }} {{ $r->currency }}</span></span>
                    @if($r->notes)<p class="small text-muted mt-2 mb-0">{{ \Illuminate\Support\Str::limit($r->notes,110) }}</p>@endif
                </div>
            </div></div>
        </div>
    @endforeach
    </div>
@endif
@endsection
