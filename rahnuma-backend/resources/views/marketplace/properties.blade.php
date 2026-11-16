@extends('layouts.app')
@section('title','Properties — Rahnuma')
@section('body-class','marketplace-page')
@section('content')
<div class="market-results-head"><div><span>Property marketplace</span><h1>Find your next property</h1><p>{{ number_format($properties->total()) }} active listings from participating Rahnamas.</p></div><a href="{{ route('home') }}">← Marketplace home</a></div>

<form class="market-filter-bar" method="get">
    <div class="market-filter-search"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="Search area, property or listing code"></div>
    <div class="market-filter-select"><label>Province</label><select name="province_id" id="market_province" data-placeholder="All Afghanistan" data-district-url="{{ url('/locations/districts') }}"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}" @selected(request('province_id')==$p->id)>{{ $p->name_en }} / {{ $p->name_fa }}</option>@endforeach</select></div>
    <div class="market-filter-select"><label>District</label><select name="district_id" id="market_district" data-placeholder="Any district" data-area-url="{{ url('/locations/areas') }}"><option value=""></option>@foreach($districts as $d)<option value="{{ $d->id }}" @selected(request('district_id')==$d->id)>{{ $d->name_en }} / {{ $d->name_fa }}</option>@endforeach</select></div>
    <div class="market-filter-select"><label>Type</label><select name="property_type" data-placeholder="Any property"><option value=""></option>@foreach(config('rahnuma.property_types') as $x)<option @selected(request('property_type')===$x) value="{{ $x }}">{{ ucfirst($x) }}</option>@endforeach</select></div>
    <div class="market-filter-select"><label>Deal</label><select name="transaction_type" data-placeholder="Any deal"><option value=""></option>@foreach(config('rahnuma.transaction_types') as $x)<option @selected(request('transaction_type')===$x) value="{{ $x }}">{{ $x==='bay_wafa'?'Mortgage / بیع وفا':ucfirst($x) }}</option>@endforeach</select></div>
    <button>Search</button>
</form>

<div class="market-results-layout">
    <aside class="market-refine">
        <form method="get">
            @foreach(request()->except(['min_price','max_price','bedrooms','currency','sort','featured','sponsored','page']) as $k=>$v)@if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach
            <div class="market-refine__head"><b>Refine results</b><a href="{{ route('marketplace.properties') }}">Reset</a></div>
            <div class="field"><label>Price range</label><div class="market-range"><input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"><input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"></div></div>
            <div class="field"><label>Area / neighborhood</label><select name="area_id" id="market_area" data-placeholder="Any area"><option value=""></option>@foreach($areas as $a)<option value="{{ $a->id }}" @selected(request('area_id')==$a->id)>{{ $a->name_en }} / {{ $a->name_fa }}</option>@endforeach</select></div><div class="field"><label>Currency</label><select name="currency" data-placeholder="Any currency"><option value=""></option>@foreach(config('rahnuma.currencies') as $c)<option value="{{ $c }}" @selected(request('currency')===$c)>{{ $c }}</option>@endforeach</select></div>
            <div class="field"><label>Minimum bedrooms</label><select name="bedrooms" data-placeholder="Any"><option value=""></option>@foreach([1,2,3,4,5,6] as $n)<option value="{{ $n }}" @selected(request('bedrooms')==$n)>{{ $n }}+</option>@endforeach</select></div>
            <label class="market-check"><input type="checkbox" name="featured" value="1" @checked(request('featured'))><span>Featured only</span></label>
            <label class="market-check"><input type="checkbox" name="sponsored" value="1" @checked(request('sponsored'))><span>Sponsored only</span></label>
            <button class="btn block">Apply filters</button>
        </form>
    </aside>
    <section class="market-results">
        <div class="market-results-toolbar"><div><b>{{ number_format($properties->total()) }}</b> properties</div><form><select name="sort" onchange="const u=new URL(location.href);u.searchParams.set('sort',this.value);location.href=u.toString();"><option value="newest">Newest first</option><option value="price_low" @selected(request('sort')==='price_low')>Price: low to high</option><option value="price_high" @selected(request('sort')==='price_high')>Price: high to low</option><option value="oldest" @selected(request('sort')==='oldest')>Oldest first</option></select></form></div>
        <div class="market-property-grid market-property-grid--results">@forelse($properties as $p)<x-marketplace.property-card :property="$p" />@empty<div class="market-empty">No properties match these filters. Try a wider location or price range.</div>@endforelse</div>
        <div class="pagination">{{ $properties->links() }}</div>
    </section>
</div>
@push('scripts')
<script>
(() => {
 const province=document.getElementById('market_province'), district=document.getElementById('market_district'), area=document.getElementById('market_area');
 const reset=(select,label)=>{ if(!select)return; select.innerHTML='<option value=""></option>'; select.dataset.placeholder=label; window.RahnumaSelect2?.refresh(select); };
 const fill=async(select,url,label)=>{ reset(select,label); if(!url)return; try{ const res=await fetch(url,{headers:{Accept:'application/json'}}); const rows=await res.json(); rows.forEach(row=>select.add(new Option(row.text,row.id))); window.RahnumaSelect2?.refresh(select); }catch(e){} };
 province?.addEventListener('change',()=>{ reset(area,'Any area'); fill(district,province.value?`${province.dataset.districtUrl}/${province.value}`:null,'Any district'); });
 district?.addEventListener('change',()=>fill(area,district.value?`${district.dataset.areaUrl}/${district.value}`:null,'Any area'));
})();
</script>
@endpush
@endsection
