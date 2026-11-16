@extends('layouts.app')
@section('title', __('Rahnama offices'))
@section('content')
<x-page-header :title="__('Rahnama offices')" icon="briefcase" :crumbs="[[__('Home'), route('home')], [__('Rahnama offices')]]" :subtitle="__(':count offices on the marketplace.', ['count' => number_format($agencies->total())])" />

<form class="card" method="get">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg"><label class="form-label">{{ __('Search') }}</label><div class="input-icon"><x-icon name="magnifying-glass" /><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Office name, address or license number') }}"></div></div>
            <div class="col-12 col-sm-6 col-lg-3"><label class="form-label">{{ __('Province') }}</label><select class="form-select" name="province_id" data-placeholder="{{ __('All Afghanistan') }}"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}" @selected(request('province_id')==$p->id)>{{ loc_name($p) }}</option>@endforeach</select></div>
            <div class="col-12 col-sm-6 col-lg-auto"><label class="rh-check-pill"><input type="checkbox" name="verified" value="1" @checked(request('verified'))><span><x-icon name="circle-check" /> {{ __('Verified only') }}</span></label></div>
            <div class="col-12 col-lg-auto d-flex gap-2"><button class="btn btn-primary flex-fill"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button>@if(request()->hasAny(['q','province_id','verified']))<a class="btn btn-outline-soft" href="{{ route('marketplace.agencies') }}">{{ __('Reset') }}</a>@endif</div>
        </div>
    </div>
</form>

@if($agencies->isEmpty())
    <div class="card"><div class="card-body"><x-workspace.empty icon="briefcase" :title="__('No Rahnama offices found.')" :description="__('Try another name or province.')" /></div></div>
@else
    <div class="row g-3">@foreach($agencies as $agency)<div class="col-12 col-sm-6 col-lg-4 col-xl-3"><x-marketplace.agency-card :agency="$agency" /></div>@endforeach</div>
    {{ $agencies->links('partials.pagination') }}
@endif
@endsection
