@extends('layouts.workspace')
@section('title', __('Platform Administration'))
@section('workspace')
@php
    $icons = ['agencies' => 'building-user', 'active_agencies' => 'circle-check', 'users' => 'users', 'properties' => 'building', 'transactions' => 'handshake', 'mrr' => 'sack-dollar', 'subscriptions' => 'receipt'];
    $tones = ['blue', 'green', 'violet', 'amber', 'red'];
    $labels = ['agencies' => 'Agencies', 'active_agencies' => 'Active agencies', 'users' => 'Users', 'properties' => 'Properties', 'transactions' => 'Transactions', 'mrr' => 'Monthly recurring revenue'];
@endphp
<x-page-header :title="__('Platform Administration')" icon="server" :subtitle="__('Manage Rahnama tenants, subscriptions, usage and marketplace growth.')">
    <a class="btn btn-outline-soft" href="{{ route('platform.marketplace') }}"><x-icon name="bullhorn" class="me-1" /> {{ __('Marketplace promotions') }}</a>
    <a class="btn btn-primary" href="{{ route('platform.agencies') }}"><x-icon name="building-user" class="me-1" /> {{ __('Manage Rahnamas') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
@foreach($stats as $k=>$v)
    <div class="col-6 col-xl-3"><x-stat :label="isset($labels[$k]) ? __($labels[$k]) : enum_label($k)" :value="number_format($v)" :icon="$icons[$k] ?? 'chart-column'" :tone="$tones[$loop->index % count($tones)]" :note="__('Current platform metric')" /></div>
@endforeach
</div>

<div class="card">
    <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Recent Rahnamas') }}</h5><div class="small text-muted">{{ __('Latest agencies onboarded to the platform') }}</div></div><a class="btn btn-sm btn-outline-soft" href="{{ route('platform.agencies') }}">{{ __('Open tenant manager') }}</a></div>
    <div class="card-body">
        <table class="table table-hover w-100">
            <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Plan') }}</th><th>{{ __('License') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>@foreach($agencies as $a)<tr><td><strong class="text-heading">{{ $a->name }}</strong></td><td>{{ $a->plan?->name ?: '—' }}</td><td dir="ltr">{{ $a->license_number ?: '—' }}</td><td><span class="badge-soft {{ $a->is_active ? 'success' : 'danger' }}">{{ $a->is_active ? __('Active') : __('Disabled') }}</span></td></tr>@endforeach</tbody>
        </table>
    </div>
</div>
@endsection
