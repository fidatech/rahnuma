@extends('layouts.workspace')
@section('title', __('Platform Administration'))
@section('workspace')
@php
    $tiles = [
        ['agencies', 'Rahnama offices', 'building-user', 'blue', route('platform.agencies'), __(':count active', ['count' => number_format($stats['active_agencies'])])],
        ['users', 'Users', 'users', 'violet', route('platform.users'), null],
        ['properties', 'Properties', 'building', 'amber', route('platform.properties'), null],
        ['transactions', 'Deals', 'handshake', 'green', route('platform.transactions'), null],
    ];
    $money = fn ($set) => $set->isEmpty() ? '0' : $set->map(fn ($total, $currency) => number_format((float) $total).' '.$currency)->join(' · ');
@endphp
<x-page-header :title="__('Platform Administration')" icon="server" :subtitle="__('Offices, people, money and the marketplace of the whole system.')">
    <a class="btn btn-outline-soft" href="{{ route('platform.finance') }}"><x-icon name="sack-dollar" class="me-1" /> {{ __('Money in & out') }}</a>
    <a class="btn btn-primary" href="{{ route('platform.agencies') }}"><x-icon name="building-user" class="me-1" /> {{ __('Rahnama offices') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    @foreach ($tiles as [$key, $label, $icon, $tone, $href, $note])
        <div class="col-6 col-xl-3"><x-stat :label="__($label)" :value="number_format($stats[$key])" :icon="$icon" :tone="$tone" :href="$href" :note="$note" /></div>
    @endforeach
</div>
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><x-stat :label="__('Money in this month')" :value="$money($moneyIn)" icon="circle-arrow-down" tone="green" :href="route('platform.finance', ['direction' => 'in'])" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Money out this month')" :value="$money($moneyOut)" icon="circle-arrow-up" tone="red" :href="route('platform.finance', ['direction' => 'out'])" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Active subscriptions')" :value="number_format($stats['subscriptions'])" icon="receipt" tone="blue" :href="route('platform.subscriptions', ['status' => 'active'])" /></div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Needs attention') }}</h5><div class="small text-muted">{{ __('Subscriptions and trials that end soon') }}</div></div><a class="btn btn-sm btn-outline-soft" href="{{ route('platform.subscriptions', ['status' => 'expiring']) }}">{{ __('View all') }}</a></div>
            <div class="card-body">
                @if ($expiring->isEmpty() && $trials->isEmpty())
                    <x-workspace.empty icon="circle-check" :title="__('Nothing ends in the coming weeks.')" :description="__('Subscriptions and trials close to their end will show here.')" />
                @else
                    <div class="rh-list">
                        @foreach ($expiring as $s)
                            <a class="rh-list-row" href="{{ route('platform.agencies.show', $s->agency) }}"><span class="rh-list-icon is-amber"><x-icon name="receipt" /></span><span class="min-w-0 flex-fill"><strong class="d-block text-truncate">{{ $s->agency?->name }}</strong><small>{{ $s->plan?->name }} · {{ __('Subscription') }}</small></span><span class="badge-soft warning" dir="ltr">{{ $s->ends_at?->format('Y-m-d') }}</span></a>
                        @endforeach
                        @foreach ($trials as $a)
                            <a class="rh-list-row" href="{{ route('platform.agencies.show', $a) }}"><span class="rh-list-icon is-blue"><x-icon name="clock" /></span><span class="min-w-0 flex-fill"><strong class="d-block text-truncate">{{ $a->name }}</strong><small>{{ __('Trial') }}</small></span><span class="badge-soft info" dir="ltr">{{ $a->trial_ends_at?->format('Y-m-d') }}</span></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Latest activity') }}</h5><div class="small text-muted">{{ __('What administrators did most recently') }}</div></div><a class="btn btn-sm btn-outline-soft" href="{{ route('platform.audit') }}">{{ __('Audit log') }}</a></div>
            <div class="card-body">
                @if ($activity->isEmpty())
                    <x-workspace.empty icon="clipboard-check" :title="__('No activity yet.')" :description="__('Every change made in this panel is recorded here.')" />
                @else
                    <div class="rh-list">
                        @foreach ($activity as $log)
                            <div class="rh-list-row"><span class="rh-list-icon"><x-icon name="clipboard-check" /></span><span class="min-w-0 flex-fill"><strong class="d-block text-truncate" dir="ltr">{{ $log->event }}</strong><small>{{ $log->user?->name ?: '—' }}</small></span><small class="text-muted text-nowrap" dir="ltr">{{ $log->created_at?->format('Y-m-d H:i') }}</small></div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Newest offices') }}</h5></div><a class="btn btn-sm btn-outline-soft" href="{{ route('platform.agencies') }}">{{ __('View all') }}</a></div>
            <div class="card-body">
                <table class="table table-hover w-100 align-middle">
                    <thead><tr><th>{{ __('Office') }}</th><th>{{ __('Plan') }}</th><th>{{ __('License') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created') }}</th></tr></thead>
                    <tbody>@foreach ($agencies as $a)<tr><td><a class="fw-bold text-heading text-decoration-none" href="{{ route('platform.agencies.show', $a) }}">{{ $a->name }}</a></td><td>{{ $a->plan?->name ?: '—' }}</td><td dir="ltr">{{ $a->license_number ?: '—' }}</td><td><span class="badge-soft {{ $a->is_active ? 'success' : 'danger' }}">{{ $a->is_active ? __('Active') : __('Disabled') }}</span></td><td dir="ltr">{{ $a->created_at?->format('Y-m-d') }}</td></tr>@endforeach</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
