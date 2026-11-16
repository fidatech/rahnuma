@extends('layouts.workspace')
@section('workspace')
<div class="smart-page-head"><div><div class="deal-page-head__back"><a href="{{ route('tenant.properties.index') }}">← Property inventory</a></div><div class="eyebrow">Demand matching</div><h1>Customers for {{ $property->title }}</h1><p>People whose saved requirements align with this property.</p></div></div>
<div class="match-result-head"><span>{{ count($requirements) }} customer matches</span><small>Contact the strongest matches first.</small></div>
<div class="match-people-grid">
@forelse($requirements as $r)
<article class="match-person-card"><div class="contact-avatar contact-avatar--customer">{{ strtoupper(mb_substr($r->customer?->name?:'?',0,1)) }}</div><div class="match-person-card__main"><h3>{{ $r->customer?->name }}</h3><p>{{ $r->customer?->phone ?: 'No phone' }}</p><div class="match-person-card__need">{{ ucfirst($r->property_type) }} · {{ number_format((float)$r->min_price) }}–{{ number_format((float)$r->max_price) }} {{ $r->currency }}</div>@if($r->notes)<small>{{ \Illuminate\Support\Str::limit($r->notes,110) }}</small>@endif</div></article>
@empty
<div class="match-people-grid__empty"><x-workspace.empty title="No saved customer demand matches yet" description="Create customer requirements so Rahnuma can connect new inventory to real demand." /></div>
@endforelse
</div>
@endsection
