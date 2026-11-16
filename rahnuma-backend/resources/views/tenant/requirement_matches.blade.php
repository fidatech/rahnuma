@extends('layouts.workspace')
@section('title', __('Matching results'))
@section('workspace')
<x-page-header :title="__('Properties for :customer', ['customer' => $requirement->customer?->name])" icon="bullseye" :crumbs="[[__('Requirements & Matching'), route('tenant.requirements.index')], [__('Matching results')]]" :subtitle="enum_label($requirement->property_type).' · '.deal_label($requirement->transaction_type).' · '.number_format((float)$requirement->min_price).'–'.number_format((float)$requirement->max_price).' '.$requirement->currency" />

<div class="alert alert-primary d-flex justify-content-between flex-wrap gap-2"><strong>{{ __(':count possible matches', ['count' => count($properties)]) }}</strong><span>{{ __('Review fit before sharing with the customer.') }}</span></div>

@if(count($properties) === 0)
    <div class="card"><div class="card-body"><x-workspace.empty icon="building" :title="__('No close matches yet')" :description="__('Keep the requirement active. A future property may fit the budget and type of this customer.')" /></div></div>
@else
    <div class="row g-3">
    @foreach($properties as $p)
        <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
            <x-property-card :property="$p" :href="route('tenant.properties.edit',$p)" :badge="__('Match')">
                <a class="btn btn-sm btn-outline-soft" href="{{ route('tenant.properties.edit',$p) }}"><x-icon name="eye" class="me-1" /> {{ __('Open property') }}</a>
                <a class="btn btn-sm btn-primary" href="{{ route('tenant.transactions.create',['property_id'=>$p->id]) }}"><x-icon name="handshake" class="me-1" /> {{ __('Start deal') }}</a>
            </x-property-card>
        </div>
    @endforeach
    </div>
@endif
@endsection
