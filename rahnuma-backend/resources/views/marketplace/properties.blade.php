@extends('layouts.app')
@section('title', __('Properties'))
@section('content')
<x-page-header :title="__('Find your next property')" icon="magnifying-glass" :crumbs="[[__('Home'), route('home')], [__('Properties')]]" :subtitle="__(':count active listings from participating Rahnamas.', ['count' => number_format($properties->total())])" />

<form class="card" method="get">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-3"><label class="form-label">{{ __('Search') }}</label><div class="input-icon"><x-icon name="magnifying-glass" /><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Search area, property or listing code') }}"></div></div>
            <div class="col-6 col-lg"><label class="form-label">{{ __('Province') }}</label><select class="form-select" name="province_id" id="market_province" data-placeholder="{{ __('All Afghanistan') }}" data-district-url="{{ url('/locations/districts') }}"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}" @selected(request('province_id')==$p->id)>{{ loc_name($p) }}</option>@endforeach</select></div>
            <div class="col-6 col-lg"><label class="form-label">{{ __('District') }}</label><select class="form-select" name="district_id" id="market_district" data-placeholder="{{ __('Any district') }}" data-area-url="{{ url('/locations/areas') }}"><option value=""></option>@foreach($districts as $d)<option value="{{ $d->id }}" @selected(request('district_id')==$d->id)>{{ loc_name($d) }}</option>@endforeach</select></div>
            <div class="col-6 col-lg"><label class="form-label">{{ __('Type') }}</label><select class="form-select" name="property_type" data-placeholder="{{ __('Any property') }}"><option value=""></option>@foreach(config('rahnuma.property_types') as $x)<option @selected(request('property_type')===$x) value="{{ $x }}">{{ enum_label($x) }}</option>@endforeach</select></div>
            <div class="col-6 col-lg"><label class="form-label">{{ __('Deal') }}</label><select class="form-select" name="transaction_type" data-placeholder="{{ __('Any deal') }}"><option value=""></option>@foreach(config('rahnuma.transaction_types') as $x)<option @selected(request('transaction_type')===$x) value="{{ $x }}">{{ deal_label($x, true) }}</option>@endforeach</select></div>
            <div class="col-12 col-lg-auto"><button class="btn btn-primary w-100"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button></div>
        </div>
    </div>
</form>

<div class="row g-3">
    <aside class="col-12 col-lg-3">
        <form class="card rh-sticky" method="get">
            @foreach(request()->except(['min_price','max_price','bedrooms','currency','sort','featured','sponsored','page','area_id']) as $k=>$v)@if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach
            <div class="card-header"><h5 class="card-title mb-0">{{ __('Refine results') }}</h5><a class="small" href="{{ route('marketplace.properties') }}">{{ __('Reset') }}</a></div>
            <div class="card-body d-grid gap-3">
                <div><label class="form-label">{{ __('Price range') }}</label><div class="input-group" dir="ltr"><input class="form-control" type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ __('Min') }}"><input class="form-control" type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ __('Max') }}"></div></div>
                <div><label class="form-label">{{ __('Area / neighborhood') }}</label><select class="form-select" name="area_id" id="market_area" data-placeholder="{{ __('Any area') }}"><option value=""></option>@foreach($areas as $a)<option value="{{ $a->id }}" @selected(request('area_id')==$a->id)>{{ loc_name($a) }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('Currency') }}</label><select class="form-select" name="currency" data-placeholder="{{ __('Any currency') }}"><option value=""></option>@foreach(config('rahnuma.currencies') as $c)<option value="{{ $c }}" @selected(request('currency')===$c)>{{ $c }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('Minimum bedrooms') }}</label><select class="form-select" name="bedrooms" data-placeholder="{{ __('Any') }}"><option value=""></option>@foreach([1,2,3,4,5,6] as $n)<option value="{{ $n }}" @selected(request('bedrooms')==$n)>{{ $n }}+</option>@endforeach</select></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="only_featured" name="featured" value="1" @checked(request('featured'))><label class="form-check-label" for="only_featured">{{ __('Featured only') }}</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="only_sponsored" name="sponsored" value="1" @checked(request('sponsored'))><label class="form-check-label" for="only_sponsored">{{ __('Sponsored only') }}</label></div>
                <button class="btn btn-primary w-100"><x-icon name="filter" class="me-1" /> {{ __('Apply filters') }}</button>
            </div>
        </form>
    </aside>
    <section class="col-12 col-lg-9">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <div class="text-muted">{{ __(':count properties', ['count' => number_format($properties->total())]) }}</div>
            <form><select class="form-select form-select-sm w-auto" name="sort" data-plain aria-label="{{ __('Sort') }}" onchange="const u=new URL(location.href);u.searchParams.set('sort',this.value);location.href=u.toString();"><option value="newest">{{ __('Newest first') }}</option><option value="price_low" @selected(request('sort')==='price_low')>{{ __('Price: low to high') }}</option><option value="price_high" @selected(request('sort')==='price_high')>{{ __('Price: high to low') }}</option><option value="oldest" @selected(request('sort')==='oldest')>{{ __('Oldest first') }}</option></select></form>
        </div>
        @if($properties->isEmpty())
            <div class="card"><div class="card-body"><x-workspace.empty icon="magnifying-glass" :title="__('No properties match these filters.')" :description="__('Try a wider location or price range.')" /></div></div>
        @else
            <div class="row g-3">@foreach($properties as $p)<div class="col-12 col-sm-6 col-xl-4"><x-marketplace.property-card :property="$p" /></div>@endforeach</div>
            {{ $properties->links('partials.pagination') }}
        @endif
    </section>
</div>
@push('scripts')
<script>
(() => {
 const text={{ Illuminate\Support\Js::from(['anyArea'=>__('Any area'),'anyDistrict'=>__('Any district')]) }};
 const province=document.getElementById('market_province'), district=document.getElementById('market_district'), area=document.getElementById('market_area');
 const reset=(select,label)=>{ if(!select)return; select.innerHTML='<option value=""></option>'; select.dataset.placeholder=label; window.RahnumaSelect2?.refresh(select); };
 const fill=async(select,url,label)=>{ reset(select,label); if(!url)return; try{ const res=await fetch(url,{headers:{Accept:'application/json'}}); const rows=await res.json(); rows.forEach(row=>select.add(new Option(row.text,row.id))); window.RahnumaSelect2?.refresh(select); }catch(e){} };
 province?.addEventListener('change',()=>{ reset(area,text.anyArea); fill(district,province.value?`${province.dataset.districtUrl}/${province.value}`:null,text.anyDistrict); });
 district?.addEventListener('change',()=>fill(area,district.value?`${district.dataset.areaUrl}/${district.value}`:null,text.anyArea));
})();
</script>
@endpush
@endsection
