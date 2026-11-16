@extends('layouts.workspace')
@section('title', __('Dashboard'))
@section('workspace')
<x-page-header :title="__('Dashboard')" icon="gauge-high" :subtitle="__('Good day, :name', ['name' => auth()->user()->name]).' · '.__('Your property pipeline, customer activity and financial obligations in one view.')">
    <a class="btn btn-outline-soft" href="{{ route('tenant.transactions.create') }}"><x-icon name="handshake" class="me-1" /> {{ __('New transaction') }}</a>
    <a class="btn btn-primary" href="{{ route('tenant.properties.create') }}"><x-icon name="plus" class="me-1" /> {{ __('Add property') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3"><x-stat :label="__('Active inventory')" :value="number_format($stats['available_properties'])" icon="building" tone="blue" :note="__(':count published to marketplace', ['count' => number_format($stats['public_listings'])])" :href="route('tenant.properties.index')" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-stat :label="__('Customers')" :value="number_format($stats['customers'])" icon="users" tone="violet" :note="__(':count active opportunities', ['count' => number_format($stats['active_leads'])])" :href="route('tenant.resource.index', 'customers')" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-stat :label="__('Commission receivable')" :value="number_format($stats['commission_due'])" icon="hand-holding-dollar" tone="green" :note="__(':count unpaid commission records', ['count' => number_format($dueCommissionCount)])" /></div>
    <div class="col-12 col-sm-6 col-xl-3"><x-stat :label="__('Rent due / overdue')" :value="number_format($stats['rent_due'])" icon="calendar-days" tone="amber" :note="__(':count overdue schedules need attention', ['count' => number_format($overdueRentCount)])" /></div>
</div>

<div class="row g-3 mb-3">
    @foreach ([
        [route('tenant.properties.create'), 'building', 'primary', 'Register property', 'Add inventory, owner, pricing and visibility'],
        [route('tenant.resource.index', 'customers'), 'user-plus', 'purple', 'New customer', 'Capture demand and follow-up details'],
        [route('tenant.transactions.create'), 'handshake', 'success', 'Close a deal', 'Sale, rent or mortgage (Bay-e-Wafa)'],
        [route('tenant.accounting'), 'chart-column', 'warning', 'Financial reports', 'Trial balance, profit & loss and ledger'],
    ] as [$url, $icon, $tone, $label, $text])
        <div class="col-12 col-sm-6 col-xl-3">
            <a class="card card-hover h-100 mb-0 text-decoration-none text-reset" href="{{ $url }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="sc-icon icon-soft {{ $tone }}"><x-icon :name="$icon" /></span>
                    <span class="min-w-0"><strong class="d-block text-heading">{{ __($label) }}</strong><span class="small text-muted">{{ __($text) }}</span></span>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <div><h5 class="card-title mb-0">{{ __('Recent properties') }}</h5><div class="small text-muted">{{ __('Latest inventory registered by your team') }}</div></div>
                <a class="btn btn-sm btn-outline-soft" href="{{ route('tenant.properties.index') }}">{{ __('View all') }}</a>
            </div>
            <div class="card-body">
                @if ($recent->isEmpty())
                    <x-workspace.empty icon="building" :title="__('No properties have been registered yet.')" :description="__('Register the first property and link it to its owner.')" />
                @else
                    <table class="table table-hover w-100">
                        <thead><tr><th>{{ __('Property') }}</th><th>{{ __('Deal') }}</th><th>{{ __('Price') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @foreach ($recent as $p)
                            <tr>
                                <td><a class="fw-semibold text-heading text-decoration-none" href="{{ route('tenant.properties.edit', $p) }}">{{ $p->title }}</a><div class="small text-muted">{{ $p->code }} · {{ $p->owner?->name ?: __('No owner assigned') }}</div></td>
                                <td>{{ deal_label($p->transaction_type) }}</td>
                                <td class="text-nowrap fw-semibold" dir="ltr">{{ number_format($p->transaction_type === 'rent' ? $p->monthly_rent : $p->price) }} {{ $p->currency }}</td>
                                <td class="text-nowrap"><span class="badge-soft {{ status_tone($p->status) }}">{{ enum_label($p->status) }}</span> <span class="badge-soft secondary">{{ enum_label($p->visibility) }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Deal activity') }}</h5><div class="small text-muted">{{ __('Most recent completed or draft transactions') }}</div></div></div>
            <div class="card-body">
                <ul class="list-clean">
                    @forelse ($recentTransactions as $t)
                        <li>
                            <span class="avatar avatar-sm rounded-3 avatar-soft-{{ ['sale' => 'primary', 'rent' => 'success', 'bay_wafa' => 'purple'][$t->transaction_type] ?? 'primary' }}"><x-icon :name="['sale' => 'key', 'rent' => 'house-user', 'bay_wafa' => 'file-contract'][$t->transaction_type] ?? 'handshake'" /></span>
                            <a class="flex-fill min-w-0 text-decoration-none text-reset" href="{{ route('tenant.transactions.show', $t) }}"><strong class="d-block text-truncate text-heading">{{ $t->property?->title ?: $t->reference_no }}</strong><span class="small text-muted">{{ $t->reference_no }} · {{ deal_label($t->transaction_type) }}</span></a>
                            <span class="text-end text-nowrap"><strong class="d-block" dir="ltr">{{ number_format($t->amount ?: $t->monthly_rent) }}</strong><span class="small text-muted">{{ $t->currency }}</span></span>
                        </li>
                    @empty
                        <li class="text-muted">{{ __('No transaction activity yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Upcoming follow-ups') }}</h5><div class="small text-muted">{{ __('Customers that need attention next') }}</div></div></div>
            <div class="card-body">
                <ul class="list-clean">
                    @forelse ($followUps as $lead)
                        <li>
                            <span class="avatar avatar-sm avatar-soft-warning"><x-icon name="phone" /></span>
                            <span class="flex-fill min-w-0"><strong class="d-block text-truncate text-heading">{{ $lead->customer?->name ?: __('Customer follow-up') }}</strong><span class="small text-muted">{{ $lead->property?->title ?: enum_label($lead->status) }}</span></span>
                            @if ($lead->next_follow_up_at)<span class="badge-code" dir="ltr">{{ $lead->next_follow_up_at->format('d M') }}</span>@endif
                        </li>
                    @empty
                        <li class="text-muted">{{ __('No follow-ups scheduled.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Upcoming visits') }}</h5><div class="small text-muted">{{ __('Property viewings scheduled by the team') }}</div></div></div>
            <div class="card-body">
                <ul class="list-clean">
                    @forelse ($upcomingVisits as $visit)
                        <li>
                            <span class="avatar avatar-sm avatar-soft-info"><x-icon name="calendar-check" /></span>
                            <span class="flex-fill min-w-0"><strong class="d-block text-truncate text-heading">{{ $visit->customer?->name }}</strong><span class="small text-muted">{{ $visit->property?->title }}</span></span>
                            @if ($visit->scheduled_at)<span class="badge-code" dir="ltr">{{ $visit->scheduled_at->format('d M H:i') }}</span>@endif
                        </li>
                    @empty
                        <li class="text-muted">{{ __('No property visits scheduled.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
