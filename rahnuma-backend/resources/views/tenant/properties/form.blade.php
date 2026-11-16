@extends('layouts.workspace')
@section('workspace')
<div class="deal-page-head property-studio-head">
    <div>
        <div class="deal-page-head__back"><a href="{{ route('tenant.properties.index') }}">← Property inventory</a></div>
        <div class="deal-title-row"><h1>{{ $property->exists?'Edit property':'New property' }}</h1><span class="draft-chip">{{ $property->exists?$property->code:'Draft' }}</span></div>
        <p>Build a complete property record once. Rahnuma reuses it across owners, matching, marketplace and transactions.</p>
    </div>
    <div class="deal-head-actions"><a class="btn secondary" href="{{ route('tenant.properties.index') }}">Cancel</a><button class="btn" form="property-studio-form">Save property</button></div>
</div>

<div class="property-studio-shell">
<form id="property-studio-form" class="property-studio-canvas" method="post" enctype="multipart/form-data" action="{{ $property->exists?route('tenant.properties.update',$property):route('tenant.properties.store') }}">
@csrf @if($property->exists)@method('PUT')@endif

<section class="studio-section">
    <div class="studio-section__head"><span>01</span><div><h2>Identity & offer</h2><p>What is this property and how is it being offered?</p></div></div>
    <div class="field"><label>Listing title</label><input name="title" id="studio_title" required value="{{ old('title',$property->title) }}" placeholder="Modern 3-bedroom apartment in Shahr-e-Naw"></div>
    <div class="studio-type-grid">
        @foreach(config('rahnuma.transaction_types') as $x)
        <label class="studio-choice"><input type="radio" name="transaction_type_ui" value="{{ $x }}" @checked(old('transaction_type',$property->transaction_type?:'sale')===$x)><span><b>{{ $x==='bay_wafa'?'Mortgage':ucfirst($x) }}</b><small>{{ $x==='sale'?'Transfer ownership':($x==='rent'?'Lease / tenancy':'بیع وفا / بیع جایزی') }}</small></span></label>
        @endforeach
    </div>
    <select name="transaction_type" id="property_transaction_type" class="sr-only-select" tabindex="-1" aria-hidden="true">
        @foreach(config('rahnuma.transaction_types') as $x)<option value="{{ $x }}" @selected(old('transaction_type',$property->transaction_type?:'sale')===$x)>{{ $x }}</option>@endforeach
    </select>
    <div class="studio-grid studio-grid--3">
        <div class="field"><label>Property type</label><select name="property_type" id="studio_property_type">@foreach(config('rahnuma.property_types') as $x)<option value="{{ $x }}" @selected(old('property_type',$property->property_type)===$x)>{{ ucfirst($x) }}</option>@endforeach</select></div>
        <div class="field"><label>Status</label><select name="status">@foreach(['available','active','rented','sold','mortgaged','inactive'] as $x)<option value="{{ $x }}" @selected(old('status',$property->status?:'available')===$x)>{{ ucfirst($x) }}</option>@endforeach</select></div>
        <div class="field"><label>Visibility</label><select name="visibility" id="studio_visibility">@foreach(['private','link','public','featured'] as $x)<option value="{{ $x }}" @selected(old('visibility',$property->visibility?:'private')===$x)>{{ ucfirst($x) }}</option>@endforeach</select></div>
    </div>
</section>

<section class="studio-section">
    <div class="studio-section__head"><span>02</span><div><h2>Owner relationship</h2><p>The property keeps one primary owner while supporting co-ownership in the data model.</p></div><button class="text-action" type="button" data-modal-open="quick-owner-modal" data-quick-target="owner_id">＋ New owner</button></div>
    <div class="entity-picker entity-picker--owner">
        <div class="entity-picker__visual">O</div>
        <div class="entity-picker__main"><label>Primary owner / landlord</label><select name="owner_id" id="owner_id" data-smart-resource="owner" data-remote-url="{{ route('tenant.lookup','owners') }}" data-placeholder="Search owner by name, phone or Tazkira"><option value=""></option>@if($selectedOwner)<option value="{{ $selectedOwner->id }}" selected>{{ $selectedOwner->name }}{{ $selectedOwner->phone?' · '.$selectedOwner->phone:'' }}</option>@endif</select><div class="entity-picker__meta">Type a few letters or a phone number. Rahnuma loads matching owners only when needed.</div></div>
    </div>
</section>

<section class="studio-section">
    <div class="studio-section__head"><span>03</span><div><h2>Commercial terms</h2><p>Only the fields relevant to the selected offer type stay visible.</p></div></div>
    <div class="studio-grid studio-grid--4">
        <div class="field" data-property-field="capital"><label>Sale / mortgage amount</label><div class="money-field"><span data-currency-label>{{ old('currency',($property->exists ? ($property->currency ?: 'AFN') : (auth()->user()->agency->default_currency ?: 'AFN'))) }}</span><input id="studio_price" type="number" step="any" name="price" value="{{ old('price',$property->price) }}"></div></div>
        <div class="field" data-property-field="rent"><label>Monthly rent</label><div class="money-field"><span data-currency-label>{{ old('currency',($property->exists ? ($property->currency ?: 'AFN') : (auth()->user()->agency->default_currency ?: 'AFN'))) }}</span><input id="studio_rent" type="number" step="any" name="monthly_rent" value="{{ old('monthly_rent',$property->monthly_rent) }}"></div></div>
        <div class="field" data-property-field="rent"><label>Deposit</label><div class="money-field"><span data-currency-label>{{ old('currency',($property->exists ? ($property->currency ?: 'AFN') : (auth()->user()->agency->default_currency ?: 'AFN'))) }}</span><input type="number" step="any" name="deposit_amount" value="{{ old('deposit_amount',$property->deposit_amount) }}"></div></div>
        <div class="field"><label>Currency</label><select name="currency" id="studio_currency">@foreach(config('rahnuma.currencies') as $x)<option value="{{ $x }}" @selected(old('currency',($property->exists ? ($property->currency ?: 'AFN') : (auth()->user()->agency->default_currency ?: 'AFN')))===$x)>{{ $x }}</option>@endforeach</select></div>
    </div>
</section>

<section class="studio-section">
    <div class="studio-section__head"><span>04</span><div><h2>Space & specifications</h2><p>Capture only what helps customers compare the property.</p></div></div>
    <div class="studio-grid studio-grid--4">
        <div class="field"><label>Bedrooms</label><input type="number" name="bedrooms" id="studio_beds" value="{{ old('bedrooms',$property->bedrooms) }}"></div>
        <div class="field"><label>Bathrooms</label><input type="number" name="bathrooms" id="studio_baths" value="{{ old('bathrooms',$property->bathrooms) }}"></div>
        <div class="field"><label>Total area</label><input type="number" step="any" name="total_area" id="studio_area" value="{{ old('total_area',$property->total_area) }}"></div>
        <div class="field"><label>Area unit</label><select name="area_unit" id="studio_area_unit">@foreach(['sqm','jerib','biswa'] as $x)<option value="{{ $x }}" @selected(old('area_unit',$property->area_unit?:'sqm')===$x)>{{ strtoupper($x) }}</option>@endforeach</select></div>
        <div class="field"><label>Kitchens</label><input type="number" name="kitchens" value="{{ old('kitchens',$property->kitchens) }}"></div>
        <div class="field"><label>Building floors</label><input type="number" name="floors" value="{{ old('floors',$property->floors) }}"></div>
        <div class="field"><label>Floor number</label><input type="number" name="floor_number" value="{{ old('floor_number',$property->floor_number) }}"></div>
        <div class="field"><label>Property age</label><input type="number" name="property_age" value="{{ old('property_age',$property->property_age) }}" placeholder="Years"></div>
    </div>
</section>

<section class="studio-section">
    <div class="studio-section__head"><span>05</span><div><h2>Location</h2><p>Structured location powers search; the address helps humans find the property.</p></div></div>
    <div class="studio-grid studio-grid--2">
        <div class="field"><label>Province</label><select name="province_id" id="studio_province"><option value="">Select province</option>@foreach($provinces as $x)<option value="{{ $x->id }}" @selected(old('province_id',$property->province_id)==$x->id)>{{ $x->name_en }} / {{ $x->name_fa }}</option>@endforeach</select></div>
        <div class="field"><label>Full address</label><input name="address" id="studio_address" value="{{ old('address',$property->address) }}" placeholder="District, neighborhood, street and landmark"></div>
    </div>
    <details class="advanced-fold"><summary>Precise map coordinates</summary><div class="studio-grid studio-grid--2"><div class="field"><label>Latitude</label><input name="latitude" value="{{ old('latitude',$property->latitude) }}"></div><div class="field"><label>Longitude</label><input name="longitude" value="{{ old('longitude',$property->longitude) }}"></div></div></details>
</section>

<section class="studio-section">
    <div class="studio-section__head"><span>06</span><div><h2>Media & amenities</h2><p>Add strong photos and only the amenities customers care about.</p></div></div>
    <label class="upload-zone"><input type="file" name="photos[]" multiple accept="image/*"><span class="upload-zone__icon">＋</span><b>Add property photos</b><small>Exterior, rooms, access and important details. Multiple images supported.</small></label>
    <div class="feature-chip-grid">@foreach(['parking','electricity','solar','water','well','heating','cooling','furnished','internet','security','elevator','balcony','garden','basement','storage'] as $b)<label class="feature-chip"><input type="checkbox" name="{{ $b }}" value="1" @checked(old($b,$property->$b))><span>{{ ucfirst($b) }}</span></label>@endforeach</div>
</section>

<section class="studio-section studio-section--last">
    <div class="studio-section__head"><span>07</span><div><h2>Story & notes</h2><p>Describe access, condition and owner requirements in plain language.</p></div></div>
    <div class="field"><textarea name="description" rows="6" placeholder="Describe what makes this property useful, its condition, access and any important terms">{{ old('description',$property->description) }}</textarea></div>
</section>

<div class="deal-mobile-actions"><a class="btn secondary" href="{{ route('tenant.properties.index') }}">Cancel</a><button class="btn">Save property</button></div>
</form>

<aside class="property-studio-rail">
    <div class="studio-preview-card">
        <div class="rail-label">Live property preview</div>
        <div class="studio-preview-media"><span id="studio_type_initial">{{ strtoupper(substr($property->property_type?:'P',0,1)) }}</span></div>
        <div class="studio-preview-code">{{ $property->code ?: 'NEW LISTING' }}</div>
        <h3 id="studio_preview_title">{{ $property->title ?: 'Untitled property' }}</h3>
        <p id="studio_preview_location">{{ $property->address ?: 'Location will appear here' }}</p>
        <div class="studio-preview-price"><strong id="studio_preview_price">{{ number_format((float)($property->transaction_type==='rent'?$property->monthly_rent:$property->price)) }}</strong> <span id="studio_preview_currency">{{ $property->exists ? ($property->currency ?: 'AFN') : (auth()->user()->agency->default_currency ?: 'AFN') }}</span><small id="studio_preview_period">{{ $property->transaction_type==='rent'?'/ month':'' }}</small></div>
        <div class="studio-preview-facts"><span><b id="studio_preview_beds">{{ $property->bedrooms ?? '—' }}</b> beds</span><span><b id="studio_preview_baths">{{ $property->bathrooms ?? '—' }}</b> baths</span><span><b id="studio_preview_area">{{ $property->total_area ?? '—' }}</b> <i id="studio_preview_unit">{{ strtoupper($property->area_unit?:'sqm') }}</i></span></div>
    </div>
    <div class="deal-rail-card deal-rail-card--quiet">
        <div class="rail-label">Publishing</div><h3 id="studio_preview_visibility">{{ ucfirst($property->visibility?:'Private') }}</h3><p>A private property stays inside your office. Public and featured properties can appear on the marketplace.</p>
    </div>
    <div class="deal-rail-card deal-rail-card--quiet"><div class="rail-label">Data integrity</div><ul class="integrity-list"><li><span>✓</span> Owner linked once and reused</li><li><span>✓</span> Commercial terms follow offer type</li><li><span>✓</span> Public visibility controlled by Rahnama</li></ul></div>
</aside>
</div>
@endsection

@push('modals')<x-quick-create.owner-modal />@endpush
@push('scripts')
<script>
(() => {
 const typeSelect=document.getElementById('property_transaction_type');
 const radios=[...document.querySelectorAll('[name="transaction_type_ui"]')];
 const currency=document.getElementById('studio_currency');
 const val=id=>document.getElementById(id)?.value||'';
 const money=n=>{const x=parseFloat(n||0);return Number.isFinite(x)?new Intl.NumberFormat().format(x):'0'};
 const refreshType=()=>{const type=typeSelect.value;document.querySelectorAll('[data-property-field="rent"]').forEach(el=>el.classList.toggle('conditional-hidden',type!=='rent'));document.querySelectorAll('[data-property-field="capital"]').forEach(el=>el.classList.toggle('conditional-hidden',type==='rent'));document.getElementById('studio_preview_period').textContent=type==='rent'?'/ month':'';refreshPreview();};
 radios.forEach(r=>r.addEventListener('change',()=>{if(r.checked){typeSelect.value=r.value;refreshType();}}));
 const refreshPreview=()=>{
   const type=typeSelect.value;
   document.getElementById('studio_preview_title').textContent=val('studio_title')||'Untitled property';
   document.getElementById('studio_preview_location').textContent=val('studio_address')||'Location will appear here';
   document.getElementById('studio_preview_price').textContent=money(type==='rent'?val('studio_rent'):val('studio_price'));
   document.getElementById('studio_preview_currency').textContent=val('studio_currency')||'AFN';
   document.getElementById('studio_preview_beds').textContent=val('studio_beds')||'—';
   document.getElementById('studio_preview_baths').textContent=val('studio_baths')||'—';
   document.getElementById('studio_preview_area').textContent=val('studio_area')||'—';
   document.getElementById('studio_preview_unit').textContent=(val('studio_area_unit')||'sqm').toUpperCase();
   document.getElementById('studio_preview_visibility').textContent=(val('studio_visibility')||'private').replace(/^./,c=>c.toUpperCase());
   const pt=val('studio_property_type')||'P'; document.getElementById('studio_type_initial').textContent=pt.charAt(0).toUpperCase();
   document.querySelectorAll('[data-currency-label]').forEach(x=>x.textContent=val('studio_currency')||'AFN');
 };
 ['studio_title','studio_address','studio_price','studio_rent','studio_currency','studio_beds','studio_baths','studio_area','studio_area_unit','studio_visibility','studio_property_type'].forEach(id=>document.getElementById(id)?.addEventListener('input',refreshPreview));
 currency?.addEventListener('change',refreshPreview); typeSelect?.addEventListener('change',refreshType); refreshType(); refreshPreview();
})();
</script>
@endpush
