@extends('layouts.app')
@section('title','Platform Administration — Rahnuma')
@section('content')
<div class="page-header"><div class="page-header__copy"><div class="eyebrow">SaaS control center</div><h1>Platform Administration</h1><p class="page-subtitle">Manage Rahnama tenants, subscriptions, usage and marketplace growth.</p></div><div class="page-actions"><a class="btn secondary" href="{{ route('platform.marketplace') }}">Marketplace promotions</a><a class="btn" href="{{ route('platform.agencies') }}">Manage Rahnamas</a></div></div>
<div class="kpi-grid">
@foreach($stats as $k=>$v)<div class="kpi {{ $k==='mrr'?'accent':'' }}"><div class="kpi__top"><span class="kpi__label">{{ ucwords(str_replace('_',' ',$k)) }}</span><span class="kpi__tag">PLATFORM</span></div><div class="kpi__value">{{ number_format($v) }}</div><div class="kpi__foot">Current platform metric</div></div>@endforeach
</div><div style="height:20px"></div>
<div class="card"><div class="card-header"><div><h2 class="section-title">Recent Rahnamas</h2><div class="card-subtitle">Latest agencies onboarded to the platform</div></div><a class="btn secondary sm" href="{{ route('platform.agencies') }}">Open tenant manager</a></div><div class="table-wrap"><table><thead><tr><th>Agency</th><th>Plan</th><th>License</th><th>Status</th></tr></thead><tbody>@foreach($agencies as $a)<tr><td><div class="table-title">{{ $a->name }}</div></td><td>{{ $a->plan?->name ?: '—' }}</td><td>{{ $a->license_number ?: '—' }}</td><td><span class="badge {{ $a->is_active?'success':'danger' }}">{{ $a->is_active?'Active':'Disabled' }}</span></td></tr>@endforeach</tbody></table></div></div>
@endsection
