@extends('layouts.workspace')
@section('title', $property->exists ? __('Edit property') : __('New property'))
@section('workspace')
@php
    $currencyNow = old('currency', ($property->exists ? ($property->currency ?: 'AFN') : (auth()->user()->agency->default_currency ?: 'AFN')));
    $typeNow = old('transaction_type', $property->transaction_type ?: 'sale');
    $typeHelp = ['sale' => 'Transfer ownership', 'rent' => 'Lease / tenancy', 'bay_wafa' => 'Bay-e-Wafa / Bay-e-Jayezi'];
    $typeIcon = ['sale' => 'key', 'rent' => 'house-user', 'bay_wafa' => 'file-contract'];
    $section = fn (int $n, string $title, string $text) => '<div class="set-head"><span class="set-icon">'.sprintf('%02d', $n).'</span><div><h5>'.e(__($title)).'</h5><small>'.e(__($text)).'</small></div></div>';
@endphp
<x-page-header :title="$property->exists ? __('Edit property') : __('New property')" icon="building" :crumbs="[[__('Property Inventory'), route('tenant.properties.index')], [$property->exists ? $property->code : __('Draft')]]" :subtitle="__('Build a complete property record once. Rahnuma reuses it across owners, matching, marketplace and transactions.')">
    <a class="btn btn-light-secondary" href="{{ route('tenant.properties.index') }}">{{ __('Cancel') }}</a>
    <button class="btn btn-primary" form="property-studio-form"><x-icon name="check" class="me-1" /> {{ __('Save property') }}</button>
</x-page-header>

<div class="row g-3">
<div class="col-12 col-xl-8">
<form id="property-studio-form" method="post" enctype="multipart/form-data" action="{{ $property->exists?route('tenant.properties.update',$property):route('tenant.properties.store') }}">
@csrf @if($property->exists)@method('PUT')@endif

<div class="card">
    <div class="card-header">{!! $section(1, 'Identity & offer', 'What is this property and how is it being offered?') !!}</div>
    <div class="card-body">
        <div class="mb-3"><label class="form-label" for="studio_title">{{ __('Listing title') }} <span class="req">*</span></label><input class="form-control" name="title" id="studio_title" required value="{{ old('title',$property->title) }}" placeholder="{{ __('Modern 3-bedroom apartment in Shahr-e-Naw') }}"></div>
        <div class="row g-2 mb-3">
            @foreach(config('rahnuma.transaction_types') as $x)
            <div class="col-12 col-md-4">
                <label class="form-check-card h-100"><input class="form-check-input" type="radio" name="transaction_type_ui" value="{{ $x }}" @checked($typeNow===$x)><span class="d-flex align-items-center gap-2"><span class="sc-icon icon-soft primary rh-choice-icon"><x-icon :name="$typeIcon[$x] ?? 'handshake'" /></span><span><strong class="d-block text-heading">{{ deal_label($x) }}</strong><small class="text-muted">{{ __($typeHelp[$x] ?? '') }}</small></span></span></label>
            </div>
            @endforeach
        </div>
        <select name="transaction_type" id="property_transaction_type" class="sr-only-select" tabindex="-1" aria-hidden="true">
            @foreach(config('rahnuma.transaction_types') as $x)<option value="{{ $x }}" @selected($typeNow===$x)>{{ $x }}</option>@endforeach
        </select>
        <div class="row g-3">
            <div class="col-12 col-md-4"><label class="form-label" for="studio_property_type">{{ __('Property type') }}</label><select class="form-select" name="property_type" id="studio_property_type">@foreach(config('rahnuma.property_types') as $x)<option value="{{ $x }}" data-label="{{ enum_label($x) }}" @selected(old('property_type',$property->property_type)===$x)>{{ enum_label($x) }}</option>@endforeach</select></div>
            <div class="col-12 col-md-4"><label class="form-label">{{ __('Status') }}</label><select class="form-select" name="status">@foreach(['available','active','rented','sold','mortgaged','inactive'] as $x)<option value="{{ $x }}" @selected(old('status',$property->status?:'available')===$x)>{{ enum_label($x) }}</option>@endforeach</select></div>
            <div class="col-12 col-md-4"><label class="form-label" for="studio_visibility">{{ __('Visibility') }}</label><select class="form-select" name="visibility" id="studio_visibility">@foreach(['private','link','public','featured'] as $x)<option value="{{ $x }}" @selected(old('visibility',$property->visibility?:'private')===$x)>{{ enum_label($x) }}</option>@endforeach</select></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">{!! $section(2, 'Owner relationship', 'The property keeps one primary owner while supporting co-ownership in the data model.') !!}<button class="btn btn-sm btn-soft-primary" type="button" data-modal-open="quick-owner-modal" data-quick-target="owner_id"><x-icon name="plus" class="me-1" /> {{ __('New owner') }}</button></div>
    <div class="card-body">
        <label class="form-label" for="owner_id">{{ __('Primary owner / landlord') }}</label>
        <select class="form-select" name="owner_id" id="owner_id" data-smart-resource="owner" data-remote-url="{{ route('tenant.lookup','owners') }}" data-placeholder="{{ __('Search owner by name, phone or Tazkira') }}"><option value=""></option>@if($selectedOwner)<option value="{{ $selectedOwner->id }}" selected>{{ $selectedOwner->name }}{{ $selectedOwner->phone?' · '.$selectedOwner->phone:'' }}</option>@endif</select>
        <div class="form-text">{{ __('Type a few letters or a phone number. Rahnuma loads matching owners only when needed.') }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">{!! $section(3, 'Commercial terms', 'Only the fields relevant to the selected offer type stay visible.') !!}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-3" data-property-field="capital"><label class="form-label" for="studio_price">{{ __('Sale / mortgage amount') }}</label><div class="input-group" dir="ltr"><span class="input-group-text" data-currency-label>{{ $currencyNow }}</span><input class="form-control" id="studio_price" type="number" step="any" name="price" value="{{ old('price',$property->price) }}"></div></div>
            <div class="col-12 col-md-6 col-lg-3" data-property-field="rent"><label class="form-label" for="studio_rent">{{ __('Monthly rent') }}</label><div class="input-group" dir="ltr"><span class="input-group-text" data-currency-label>{{ $currencyNow }}</span><input class="form-control" id="studio_rent" type="number" step="any" name="monthly_rent" value="{{ old('monthly_rent',$property->monthly_rent) }}"></div></div>
            <div class="col-12 col-md-6 col-lg-3" data-property-field="rent"><label class="form-label">{{ __('Deposit') }}</label><div class="input-group" dir="ltr"><span class="input-group-text" data-currency-label>{{ $currencyNow }}</span><input class="form-control" type="number" step="any" name="deposit_amount" value="{{ old('deposit_amount',$property->deposit_amount) }}"></div></div>
            <div class="col-12 col-md-6 col-lg-3"><label class="form-label" for="studio_currency">{{ __('Currency') }}</label><select class="form-select" name="currency" id="studio_currency">@foreach(config('rahnuma.currencies') as $x)<option value="{{ $x }}" @selected($currencyNow===$x)>{{ $x }}</option>@endforeach</select></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">{!! $section(4, 'Space & specifications', 'Capture only what helps customers compare the property.') !!}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-lg-3"><label class="form-label" for="studio_beds">{{ __('Bedrooms') }}</label><input class="form-control" type="number" name="bedrooms" id="studio_beds" value="{{ old('bedrooms',$property->bedrooms) }}" dir="ltr"></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="studio_baths">{{ __('Bathrooms') }}</label><input class="form-control" type="number" name="bathrooms" id="studio_baths" value="{{ old('bathrooms',$property->bathrooms) }}" dir="ltr"></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="studio_area">{{ __('Total area') }}</label><input class="form-control" type="number" step="any" name="total_area" id="studio_area" value="{{ old('total_area',$property->total_area) }}" dir="ltr"></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="studio_area_unit">{{ __('Area unit') }}</label><select class="form-select" name="area_unit" id="studio_area_unit">@foreach(['sqm','jerib','biswa'] as $x)<option value="{{ $x }}" data-label="{{ __(strtoupper($x)) }}" @selected(old('area_unit',$property->area_unit?:'sqm')===$x)>{{ __(strtoupper($x)) }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-3"><label class="form-label">{{ __('Kitchens') }}</label><input class="form-control" type="number" name="kitchens" value="{{ old('kitchens',$property->kitchens) }}" dir="ltr"></div>
            <div class="col-6 col-lg-3"><label class="form-label">{{ __('Building floors') }}</label><input class="form-control" type="number" name="floors" value="{{ old('floors',$property->floors) }}" dir="ltr"></div>
            <div class="col-6 col-lg-3"><label class="form-label">{{ __('Floor number') }}</label><input class="form-control" type="number" name="floor_number" value="{{ old('floor_number',$property->floor_number) }}" dir="ltr"></div>
            <div class="col-6 col-lg-3"><label class="form-label">{{ __('Property age') }}</label><input class="form-control" type="number" name="property_age" value="{{ old('property_age',$property->property_age) }}" placeholder="{{ __('Years') }}" dir="ltr"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">{!! $section(5, 'Location', 'Structured location powers search; the address helps humans find the property.') !!}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-5"><label class="form-label" for="studio_province">{{ __('Province') }}</label><select class="form-select" name="province_id" id="studio_province" data-placeholder="{{ __('Select province') }}"><option value="">{{ __('Select province') }}</option>@foreach($provinces as $x)<option value="{{ $x->id }}" @selected(old('province_id',$property->province_id)==$x->id)>{{ loc_name($x) }}</option>@endforeach</select></div>
            <div class="col-12 col-md-7"><label class="form-label" for="studio_address">{{ __('Full address') }}</label><input class="form-control" name="address" id="studio_address" value="{{ old('address',$property->address) }}" placeholder="{{ __('District, neighborhood, street and landmark') }}"></div>
        </div>
        <details class="mt-3"><summary class="small fw-semibold text-primary cursor-pointer">{{ __('Precise map coordinates') }}</summary><div class="row g-3 mt-0"><div class="col-6"><label class="form-label">{{ __('Latitude') }}</label><input class="form-control" name="latitude" value="{{ old('latitude',$property->latitude) }}" dir="ltr"></div><div class="col-6"><label class="form-label">{{ __('Longitude') }}</label><input class="form-control" name="longitude" value="{{ old('longitude',$property->longitude) }}" dir="ltr"></div></div></details>
    </div>
</div>

<div class="card">
    <div class="card-header">{!! $section(6, 'Media & amenities', 'Add strong photos and only the amenities customers care about.') !!}</div>
    <div class="card-body">
        <label class="img-drop mb-3"><input type="file" name="photos[]" multiple accept="image/*" hidden data-file-count="#studio_photo_count"><x-icon name="cloud-arrow-up" class="fs-3 d-block mx-auto mb-2" /><strong class="d-block text-heading">{{ __('Add property photos') }}</strong><span>{{ __('Exterior, rooms, access and important details. Multiple images supported.') }}</span><span class="d-block fw-semibold text-primary mt-1" id="studio_photo_count"></span></label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(['parking','electricity','solar','water','well','heating','cooling','furnished','internet','security','elevator','balcony','garden','basement','storage'] as $b)
                <input type="checkbox" class="btn-check" id="amenity_{{ $b }}" name="{{ $b }}" value="1" autocomplete="off" @checked(old($b,$property->$b))><label class="btn btn-sm btn-outline-primary" for="amenity_{{ $b }}">{{ enum_label($b) }}</label>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">{!! $section(7, 'Story & notes', 'Describe access, condition and owner requirements in plain language.') !!}</div>
    <div class="card-body">
        <textarea class="form-control" name="description" rows="6" placeholder="{{ __('Describe what makes this property useful, its condition, access and any important terms') }}">{{ old('description',$property->description) }}</textarea>
    </div>
</div>

<div class="save-bar">
    <a class="btn btn-light-secondary" href="{{ route('tenant.properties.index') }}">{{ __('Cancel') }}</a>
    <button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save property') }}</button>
</div>
</form>
</div>

<aside class="col-12 col-xl-4">
    <div class="rh-sticky">
        <div class="card rh-prop">
            <div class="rh-prop-media"><span class="rh-prop-empty"><x-icon name="building" /><small id="studio_type_initial">{{ enum_label($property->property_type, __('Property')) }}</small></span></div>
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold mb-2">{{ __('Live property preview') }}</div>
                <span class="badge-code mb-2">{{ $property->code ?: __('New listing') }}</span>
                <h3 class="rh-prop-title" id="studio_preview_title">{{ $property->title ?: __('Untitled property') }}</h3>
                <p class="small text-muted mb-2" id="studio_preview_location">{{ $property->address ?: __('Location will appear here') }}</p>
                <div class="rh-prop-price" dir="ltr"><strong id="studio_preview_price">{{ number_format((float)($property->transaction_type==='rent'?$property->monthly_rent:$property->price)) }}</strong> <small><span id="studio_preview_currency">{{ $currencyNow }}</span> <span id="studio_preview_period">{{ $property->transaction_type==='rent' ? '/ '.__('month') : '' }}</span></small></div>
                <div class="rh-prop-facts"><span><x-icon name="bed" /> <b id="studio_preview_beds">{{ $property->bedrooms ?? '—' }}</b> {{ __('beds') }}</span><span><x-icon name="bath" /> <b id="studio_preview_baths">{{ $property->bathrooms ?? '—' }}</b> {{ __('baths') }}</span><span><x-icon name="ruler-combined" /> <b id="studio_preview_area">{{ $property->total_area ?? '—' }}</b> <i class="fst-normal" id="studio_preview_unit">{{ __(strtoupper($property->area_unit?:'sqm')) }}</i></span></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold mb-1">{{ __('Publishing') }}</div>
                <h5 id="studio_preview_visibility">{{ enum_label($property->visibility ?: 'private') }}</h5>
                <p class="small text-muted mb-0">{{ __('A private property stays inside your office. Public and featured properties can appear on the marketplace.') }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold mb-2">{{ __('Data integrity') }}</div>
                <ul class="list-unstyled small mb-0 d-grid gap-2">
                    <li><x-icon name="circle-check" class="text-success me-1" /> {{ __('Owner linked once and reused') }}</li>
                    <li><x-icon name="circle-check" class="text-success me-1" /> {{ __('Commercial terms follow offer type') }}</li>
                    <li><x-icon name="circle-check" class="text-success me-1" /> {{ __('Public visibility controlled by Rahnama') }}</li>
                </ul>
            </div>
        </div>
    </div>
</aside>
</div>
@endsection

@push('modals')<x-quick-create.owner-modal />@endpush
@push('scripts')
<script>
(() => {
 const text={{ Illuminate\Support\Js::from(['untitled'=>__('Untitled property'),'location'=>__('Location will appear here'),'month'=>'/ '.__('month'),'photos'=>__(':count photos selected')]) }};
 const typeSelect=document.getElementById('property_transaction_type');
 if(!typeSelect) return;
 const radios=[...document.querySelectorAll('[name="transaction_type_ui"]')];
 const currency=document.getElementById('studio_currency');
 const val=id=>document.getElementById(id)?.value||'';
 const chosen=id=>{const el=document.getElementById(id);const o=el?.options?.[el.selectedIndex];return o?(o.dataset.label||o.textContent.trim()):'';};
 const money=n=>{const x=parseFloat(n||0);return Number.isFinite(x)?new Intl.NumberFormat('en-US').format(x):'0'};
 const refreshType=()=>{const type=typeSelect.value;document.querySelectorAll('[data-property-field="rent"]').forEach(el=>el.classList.toggle('conditional-hidden',type!=='rent'));document.querySelectorAll('[data-property-field="capital"]').forEach(el=>el.classList.toggle('conditional-hidden',type==='rent'));document.getElementById('studio_preview_period').textContent=type==='rent'?text.month:'';refreshPreview();};
 radios.forEach(r=>r.addEventListener('change',()=>{if(r.checked){typeSelect.value=r.value;refreshType();}}));
 const refreshPreview=()=>{
   const type=typeSelect.value;
   document.getElementById('studio_preview_title').textContent=val('studio_title')||text.untitled;
   document.getElementById('studio_preview_location').textContent=val('studio_address')||text.location;
   document.getElementById('studio_preview_price').textContent=money(type==='rent'?val('studio_rent'):val('studio_price'));
   document.getElementById('studio_preview_currency').textContent=val('studio_currency')||'AFN';
   document.getElementById('studio_preview_beds').textContent=val('studio_beds')||'—';
   document.getElementById('studio_preview_baths').textContent=val('studio_baths')||'—';
   document.getElementById('studio_preview_area').textContent=val('studio_area')||'—';
   document.getElementById('studio_preview_unit').textContent=chosen('studio_area_unit');
   document.getElementById('studio_preview_visibility').textContent=chosen('studio_visibility');
   document.getElementById('studio_type_initial').textContent=chosen('studio_property_type');
   document.querySelectorAll('[data-currency-label]').forEach(x=>x.textContent=val('studio_currency')||'AFN');
 };
 ['studio_title','studio_address','studio_price','studio_rent','studio_currency','studio_beds','studio_baths','studio_area','studio_area_unit','studio_visibility','studio_property_type'].forEach(id=>{const el=document.getElementById(id);el?.addEventListener('input',refreshPreview);el?.addEventListener('change',refreshPreview);});
 currency?.addEventListener('change',refreshPreview); typeSelect?.addEventListener('change',refreshType);
 const photos=document.querySelector('[data-file-count]');
 photos?.addEventListener('change',()=>{const out=document.querySelector(photos.dataset.fileCount);if(out)out.textContent=photos.files.length?text.photos.replace(':count',photos.files.length):'';});
 refreshType(); refreshPreview();
})();
</script>
@endpush
