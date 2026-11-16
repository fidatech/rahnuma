@extends('layouts.workspace')
@section('workspace')
<div class="workspace-toolbar">
    <div>
        <div class="eyebrow">Operations overview</div>
        <h1 style="margin:0 0 5px">Good day, {{ auth()->user()->name }}</h1>
        <div class="workspace-toolbar__meta">Your property pipeline, customer activity and financial obligations in one view.</div>
    </div>
    <div class="page-actions">
        <a class="btn secondary" href="{{ route('tenant.transactions.create') }}">New transaction</a>
        <a class="btn" href="{{ route('tenant.properties.create') }}">Add property</a>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi primary"><div class="kpi__top"><span class="kpi__label">Active inventory</span><span class="kpi__tag">LIVE</span></div><div class="kpi__value">{{ number_format($stats['available_properties']) }}</div><div class="kpi__foot">{{ number_format($stats['public_listings']) }} published to marketplace</div></div>
    <div class="kpi"><div class="kpi__top"><span class="kpi__label">Customers</span><span class="kpi__tag">CRM</span></div><div class="kpi__value">{{ number_format($stats['customers']) }}</div><div class="kpi__foot">{{ number_format($stats['active_leads']) }} active opportunities</div></div>
    <div class="kpi accent"><div class="kpi__top"><span class="kpi__label">Commission receivable</span><span class="kpi__tag">DUE</span></div><div class="kpi__value">{{ number_format($stats['commission_due']) }}</div><div class="kpi__foot">{{ number_format($dueCommissionCount) }} unpaid commission records</div></div>
    <div class="kpi warning"><div class="kpi__top"><span class="kpi__label">Rent due / overdue</span><span class="kpi__tag">RENT</span></div><div class="kpi__value">{{ number_format($stats['rent_due']) }}</div><div class="kpi__foot">{{ number_format($overdueRentCount) }} overdue schedules need attention</div></div>
</div>

<div style="height:18px"></div>
<div class="quick-actions">
    <a class="quick-action" href="{{ route('tenant.properties.create') }}"><b>Register property</b><span>Add inventory, owner, pricing and visibility</span></a>
    <a class="quick-action" href="{{ route('tenant.resource.index','customers') }}"><b>New customer</b><span>Capture demand and follow-up details</span></a>
    <a class="quick-action" href="{{ route('tenant.transactions.create') }}"><b>Close a deal</b><span>Sale, rent or Mortgage / بیع وفا</span></a>
    <a class="quick-action" href="{{ route('tenant.accounting') }}"><b>Financial reports</b><span>Trial balance, profit & loss and ledger</span></a>
</div>

<div style="height:18px"></div>
<div class="grid">
    <div class="g7 card">
        <div class="card-header">
            <div><h2 class="section-title">Recent properties</h2><div class="card-subtitle">Latest inventory registered by your team</div></div>
            <a class="btn secondary sm" href="{{ route('tenant.properties.index') }}">View all</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Property</th><th>Deal</th><th>Price</th><th>Status</th><th>Visibility</th></tr></thead>
                <tbody>
                @forelse($recent as $p)
                    <tr>
                        <td><div class="table-title">{{ $p->title }}</div><div class="table-subtitle">{{ $p->code }} · {{ $p->owner?->name ?: 'No owner assigned' }}</div></td>
                        <td>{{ $p->transaction_type==='bay_wafa'?'Mortgage':ucfirst($p->transaction_type) }}</td>
                        <td class="amount">{{ number_format($p->transaction_type==='rent'?$p->monthly_rent:$p->price) }} {{ $p->currency }}</td>
                        <td><span class="badge {{ in_array($p->status,['available','active'])?'success':'neutral' }}">{{ $p->status }}</span></td>
                        <td><span class="badge neutral">{{ $p->visibility }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No properties have been registered yet.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="g5 card">
        <div class="card-header"><div><h2 class="section-title">Deal activity</h2><div class="card-subtitle">Most recent completed or draft transactions</div></div></div>
        <div class="activity-list">
            @forelse($recentTransactions as $t)
                <a class="activity-row" href="{{ route('tenant.transactions.show',$t) }}">
                    <span class="activity-icon">{{ strtoupper(substr($t->transaction_type,0,1)) }}</span>
                    <span><span class="activity-title">{{ $t->property?->title ?: $t->reference_no }}</span><span class="activity-sub">{{ $t->reference_no }} · {{ $t->transaction_type==='bay_wafa'?'Mortgage':ucfirst($t->transaction_type) }}</span></span>
                    <span class="activity-amount">{{ number_format($t->amount?:$t->monthly_rent) }}<br><small class="muted">{{ $t->currency }}</small></span>
                </a>
            @empty<div class="empty-state">No transaction activity yet.</div>@endforelse
        </div>
    </div>

    <div class="g6 card">
        <div class="card-header"><div><h2 class="section-title">Upcoming follow-ups</h2><div class="card-subtitle">Customers that need attention next</div></div></div>
        <div class="activity-list">
            @forelse($followUps as $lead)
                <div class="activity-row">
                    <span class="activity-icon">F</span>
                    <span><span class="activity-title">{{ $lead->customer?->name ?: 'Customer follow-up' }}</span><span class="activity-sub">{{ $lead->property?->title ?: ucfirst($lead->status) }}</span></span>
                    <span class="activity-amount">{{ optional($lead->next_follow_up_at)->format('d M') }}</span>
                </div>
            @empty<div class="empty-state">No follow-ups scheduled.</div>@endforelse
        </div>
    </div>

    <div class="g6 card">
        <div class="card-header"><div><h2 class="section-title">Upcoming visits</h2><div class="card-subtitle">Property viewings scheduled by the team</div></div></div>
        <div class="activity-list">
            @forelse($upcomingVisits as $visit)
                <div class="activity-row">
                    <span class="activity-icon">V</span>
                    <span><span class="activity-title">{{ $visit->customer?->name }}</span><span class="activity-sub">{{ $visit->property?->title }}</span></span>
                    <span class="activity-amount">{{ optional($visit->scheduled_at)->format('d M H:i') }}</span>
                </div>
            @empty<div class="empty-state">No property visits scheduled.</div>@endforelse
        </div>
    </div>
</div>
@endsection
