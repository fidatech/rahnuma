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

@php
    $filterKeys = ['min_price','max_price','area_id','currency','bedrooms','featured','sponsored'];
    $activeCount = collect($filterKeys)->filter(fn ($k) => request()->filled($k))->count();
    // one removable chip for every filter in use
    $chips = [];
    if (request()->filled('q')) $chips['q'] = request('q');
    if (request()->filled('province_id')) $chips['province_id'] = loc_name($provinces->firstWhere('id', (int) request('province_id'))) ?: __('Province');
    if (request()->filled('district_id')) $chips['district_id'] = loc_name($districts->firstWhere('id', (int) request('district_id'))) ?: __('District');
    if (request()->filled('area_id')) $chips['area_id'] = loc_name($areas->firstWhere('id', (int) request('area_id'))) ?: __('Area / neighborhood');
    if (request()->filled('property_type')) $chips['property_type'] = enum_label(request('property_type'));
    if (request()->filled('transaction_type')) $chips['transaction_type'] = deal_label(request('transaction_type'), true);
    if (request()->filled('min_price')) $chips['min_price'] = __('Min').' '.number_format((float) request('min_price'));
    if (request()->filled('max_price')) $chips['max_price'] = __('Max').' '.number_format((float) request('max_price'));
    if (request()->filled('currency')) $chips['currency'] = request('currency');
    if (request()->filled('bedrooms')) $chips['bedrooms'] = request('bedrooms').'+ '.__('Bedrooms');
    if (request()->boolean('featured')) $chips['featured'] = __('Featured only');
    if (request()->boolean('sponsored')) $chips['sponsored'] = __('Sponsored only');
@endphp
<div class="row g-3 align-items-start">
    <aside class="col-12 col-lg-3 rh-sticky">
        <form class="rh-filter" method="get" id="marketFilters">
            @foreach(request()->except(['min_price','max_price','bedrooms','currency','sort','featured','sponsored','page','area_id']) as $k=>$v)@if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach
            <details class="rh-filter-box" open>
                <summary class="rh-filter-head">
                    <span><x-icon name="sliders" /> {{ __('Refine results') }} @if($activeCount)<b class="rh-filter-count">{{ $activeCount }}</b>@endif</span>
                    <x-icon name="chevron-down" class="rh-filter-caret" />
                </summary>
                <div class="rh-filter-body">
                    <fieldset class="rh-filter-group">
                        <legend>{{ __('Price range') }}</legend>
                        <div class="rh-range" dir="ltr">
                            <input class="form-control" type="number" min="0" inputmode="numeric" name="min_price" value="{{ request('min_price') }}" placeholder="{{ __('Min') }}" aria-label="{{ __('Min') }}">
                            <span aria-hidden="true">–</span>
                            <input class="form-control" type="number" min="0" inputmode="numeric" name="max_price" value="{{ request('max_price') }}" placeholder="{{ __('Max') }}" aria-label="{{ __('Max') }}">
                        </div>
                    </fieldset>
                    <fieldset class="rh-filter-group">
                        <legend>{{ __('Currency') }}</legend>
                        <div class="rh-chips">
                            <label class="rh-chip"><input type="radio" name="currency" value="" data-auto @checked(!request('currency'))><span>{{ __('Any') }}</span></label>
                            @foreach(config('rahnuma.currencies') as $c)<label class="rh-chip"><input type="radio" name="currency" value="{{ $c }}" data-auto @checked(request('currency')===$c)><span>{{ $c }}</span></label>@endforeach
                        </div>
                    </fieldset>
                    <fieldset class="rh-filter-group">
                        <legend>{{ __('Minimum bedrooms') }}</legend>
                        <div class="rh-chips is-even">
                            <label class="rh-chip"><input type="radio" name="bedrooms" value="" data-auto @checked(!request('bedrooms'))><span>{{ __('Any') }}</span></label>
                            @foreach([1,2,3,4,5] as $n)<label class="rh-chip"><input type="radio" name="bedrooms" value="{{ $n }}" data-auto @checked(request('bedrooms')==$n)><span dir="ltr">{{ $n }}+</span></label>@endforeach
                        </div>
                    </fieldset>
                    <fieldset class="rh-filter-group">
                        <legend>{{ __('Area / neighborhood') }}</legend>
                        <select class="form-select" name="area_id" id="market_area" data-auto data-placeholder="{{ __('Any area') }}"><option value=""></option>@foreach($areas as $a)<option value="{{ $a->id }}" @selected(request('area_id')==$a->id)>{{ loc_name($a) }}</option>@endforeach</select>
                    </fieldset>
                    <div class="rh-filter-switches">
                        <label class="form-check form-switch" for="only_featured"><span><x-icon name="star" /> {{ __('Featured only') }}</span><input class="form-check-input" type="checkbox" role="switch" id="only_featured" name="featured" value="1" data-auto @checked(request('featured'))></label>
                        <label class="form-check form-switch" for="only_sponsored"><span><x-icon name="bullhorn" /> {{ __('Sponsored only') }}</span><input class="form-check-input" type="checkbox" role="switch" id="only_sponsored" name="sponsored" value="1" data-auto @checked(request('sponsored'))></label>
                    </div>
                    <div class="rh-filter-actions">
                        <button class="btn btn-primary flex-fill"><x-icon name="filter" class="me-1" /> {{ __('Apply filters') }}</button>
                        <a class="btn btn-outline-soft" href="{{ route('marketplace.properties') }}">{{ __('Reset') }}</a>
                    </div>
                </div>
            </details>
        </form>
    </aside>
    <section class="col-12 col-lg-9">
        <div class="rh-results-bar">
            <div class="rh-results-count">{!! __(':count properties', ['count' => '<strong>'.number_format($properties->total()).'</strong>']) !!}</div>
            <form><select class="form-select form-select-sm w-auto" name="sort" data-plain aria-label="{{ __('Sort') }}" onchange="const u=new URL(location.href);u.searchParams.set('sort',this.value);location.href=u.toString();"><option value="newest">{{ __('Newest first') }}</option><option value="price_low" @selected(request('sort')==='price_low')>{{ __('Price: low to high') }}</option><option value="price_high" @selected(request('sort')==='price_high')>{{ __('Price: high to low') }}</option><option value="oldest" @selected(request('sort')==='oldest')>{{ __('Oldest first') }}</option></select></form>
        </div>
        @if($chips)
            <div class="rh-active">
                @foreach($chips as $key => $label)<a class="rh-active-chip" href="{{ request()->fullUrlWithoutQuery([$key, 'page']) }}" title="{{ __('Remove filter') }}">{{ $label }} <x-icon name="xmark" /></a>@endforeach
                @if(count($chips) > 1)<a class="rh-active-clear" href="{{ route('marketplace.properties') }}">{{ __('Clear all') }}</a>@endif
            </div>
        @endif
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
 const filters=document.getElementById('marketFilters');
 // a choice applies at once; the price waits for the button or the Enter key
 const submitFilters=e=>{ if(e.target.matches('[data-auto]')) filters.requestSubmit(); };
 filters?.addEventListener('change',submitFilters);
 if(window.jQuery && area) window.jQuery(area).on('select2:select select2:clear',()=>filters.requestSubmit());
 // on a phone the panel starts closed so the results come first; the tags above them show what is applied
 const box=filters?.querySelector('details');
 if(box && window.matchMedia('(max-width: 991.98px)').matches) box.open=false;
 province?.addEventListener('change',()=>{ reset(area,text.anyArea); fill(district,province.value?`${province.dataset.districtUrl}/${province.value}`:null,text.anyDistrict); });
 district?.addEventListener('change',()=>fill(area,district.value?`${district.dataset.areaUrl}/${district.value}`:null,text.anyArea));
})();
</script>
@endpush
@endsection
