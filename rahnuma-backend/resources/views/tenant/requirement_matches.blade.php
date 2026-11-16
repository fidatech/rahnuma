@extends('layouts.workspace')
@section('workspace')
<div class="smart-page-head">
    <div><div class="deal-page-head__back"><a href="{{ route('tenant.requirements.index') }}">← Requirements</a></div><div class="eyebrow">Matching results</div><h1>Properties for {{ $requirement->customer?->name }}</h1><p>{{ ucfirst($requirement->property_type) }} · {{ $requirement->transaction_type==='bay_wafa'?'Mortgage':ucfirst($requirement->transaction_type) }} · {{ number_format((float)$requirement->min_price) }}–{{ number_format((float)$requirement->max_price) }} {{ $requirement->currency }}</p></div>
</div>
<div class="match-result-head"><span>{{ count($properties) }} possible matches</span><small>Review fit before sharing with the customer.</small></div>
<div class="property-board property-board--matches">
@forelse($properties as $p)
<article class="inventory-card inventory-card--match">
    <div class="inventory-card__media"><div class="inventory-card__placeholder"><span>{{ strtoupper(substr($p->property_type?:'P',0,1)) }}</span><small>{{ ucfirst($p->property_type) }}</small></div><span class="match-score">MATCH</span></div>
    <div class="inventory-card__body"><div class="inventory-card__topline"><span>{{ $p->code }}</span><span>{{ ucfirst($p->status) }}</span></div><h3>{{ $p->title }}</h3><p class="inventory-card__location">{{ $p->address ?: 'Location not completed' }}</p><div class="inventory-card__price">{{ number_format((float)($p->transaction_type==='rent'?$p->monthly_rent:$p->price)) }} <small>{{ $p->currency }}{{ $p->transaction_type==='rent'?' / month':'' }}</small></div><div class="inventory-card__facts"><span><b>{{ $p->bedrooms??'—' }}</b> beds</span><span><b>{{ $p->bathrooms??'—' }}</b> baths</span><span><b>{{ $p->total_area??'—' }}</b> {{ strtoupper($p->area_unit?:'sqm') }}</span></div></div>
    <div class="inventory-card__actions"><a href="{{ route('tenant.properties.edit',$p) }}">Open property</a><a href="{{ route('tenant.transactions.create',['property_id'=>$p->id]) }}">Start deal</a></div>
</article>
@empty
<div class="property-board__empty"><x-workspace.empty title="No close matches yet" description="Keep the requirement active. A future property may fit this customer's budget and type." /></div>
@endforelse
</div>
@endsection
