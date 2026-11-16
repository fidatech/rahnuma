@extends('layouts.workspace')
@section('workspace')
<div class="smart-page-head">
    <div><div class="eyebrow">Demand intelligence</div><h1>Requirements & Matching</h1><p>Capture what customers actually need, then let Rahnuma compare demand against your live inventory.</p></div>
    <button class="btn" type="button" data-modal-open="requirement-drawer">New requirement</button>
</div>

<div class="pulse-strip pulse-strip--3">
    <div class="pulse-item"><span>Active requirements</span><strong>{{ number_format($requirements->total()) }}</strong><small>Customer demand</small></div>
    <div class="pulse-item"><span>Customers</span><strong>{{ number_format($customerCount) }}</strong><small>Available for matching</small></div>
    <div class="pulse-item pulse-item--quiet"><span>Matching engine</span><strong>Live</strong><small>Budget + type + location</small></div>
</div>

<div class="demand-board">
    @forelse($requirements as $r)
    <article class="demand-card">
        <div class="demand-card__top">
            <div class="contact-avatar contact-avatar--customer">{{ strtoupper(mb_substr($r->customer?->name ?: '?',0,1)) }}</div>
            <div><span class="demand-card__kicker">{{ $r->transaction_type==='bay_wafa'?'Mortgage':ucfirst($r->transaction_type) }} request</span><h3>{{ $r->customer?->name ?: 'Unknown customer' }}</h3><p>{{ $r->customer?->phone ?: 'No phone' }}</p></div>
            <span class="badge {{ $r->is_active?'success':'neutral' }}">{{ $r->is_active?'Active':'Closed' }}</span>
        </div>
        <div class="demand-card__need"><span>Looking for</span><strong>{{ ucfirst($r->property_type) }}</strong></div>
        <div class="demand-card__facts">
            <div><span>Budget</span><b>{{ number_format((float)$r->min_price) }} – {{ number_format((float)$r->max_price) }} {{ $r->currency }}</b></div>
            <div><span>Bedrooms</span><b>{{ $r->min_bedrooms ?: 'Any' }}{{ $r->max_bedrooms?' – '.$r->max_bedrooms:'' }}</b></div>
            <div><span>Area</span><b>{{ $r->min_area ?: 'Any' }}{{ $r->max_area?' – '.$r->max_area:'' }}</b></div>
        </div>
        @if($r->notes)<p class="demand-card__notes">{{ \Illuminate\Support\Str::limit($r->notes,140) }}</p>@endif
        <a class="demand-card__action" href="{{ route('tenant.requirements.matches',$r) }}">Find matching properties <span>→</span></a>
    </article>
    @empty
    <div class="demand-board__empty"><x-workspace.empty title="No customer requirements" description="Capture the first customer need and Rahnuma will start matching it with inventory." /></div>
    @endforelse
</div>
<div class="pagination collection-pagination">{{ $requirements->links() }}</div>
@endsection

@push('modals')
<x-workspace.drawer id="requirement-drawer" title="New customer requirement" kicker="Demand capture" description="Describe the customer's target once. Rahnuma will reuse it when new inventory arrives." :wide="true">
<form method="post" action="{{ route('tenant.requirements.store') }}" class="drawer-form">@csrf
<div class="smart-sheet__body">
    <div class="drawer-grid">
        <div class="field drawer-span-2"><label>Customer</label><div class="inline-create-field"><select name="customer_id" id="requirement_customer_id" data-smart-resource="customer" data-remote-url="{{ route('tenant.lookup','customers') }}" data-placeholder="Search customer by name or phone" required><option value=""></option></select><button class="inline-create-link" type="button" data-modal-open="quick-customer-modal" data-quick-target="requirement_customer_id">New customer</button></div></div>
        <div class="field"><label>Transaction</label><select name="transaction_type">@foreach(config('rahnuma.transaction_types') as $x)<option value="{{ $x }}">{{ $x==='bay_wafa'?'Mortgage / بیع وفا':ucfirst($x) }}</option>@endforeach</select></div>
        <div class="field"><label>Property type</label><select name="property_type">@foreach(config('rahnuma.property_types') as $x)<option value="{{ $x }}">{{ ucfirst($x) }}</option>@endforeach</select></div>
        <div class="field"><label>Minimum budget</label><input type="number" step="any" name="min_price"></div>
        <div class="field"><label>Maximum budget</label><input type="number" step="any" name="max_price"></div>
        <div class="field"><label>Currency</label><select name="currency">@foreach(config('rahnuma.currencies') as $x)<option value="{{ $x }}">{{ $x }}</option>@endforeach</select></div>
        <div class="field"><label>Minimum bedrooms</label><input type="number" name="min_bedrooms"></div>
        <div class="field"><label>Maximum bedrooms</label><input type="number" name="max_bedrooms"></div>
        <div class="field"><label>Minimum area</label><input type="number" step="any" name="min_area"></div>
        <div class="field"><label>Maximum area</label><input type="number" step="any" name="max_area"></div>
        <div class="field drawer-span-2"><label>Notes / preferred areas</label><textarea name="notes" placeholder="Neighborhood, building preference, timing, special requirements"></textarea></div>
    </div>
</div>
<div class="smart-sheet__footer"><span class="sheet-footer-note">The requirement remains private to this Rahnama.</span><button class="btn secondary" type="button" data-modal-close>Cancel</button><button class="btn">Save requirement</button></div>
</form>
</x-workspace.drawer>
<x-quick-create.customer-modal />
@endpush
