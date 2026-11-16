@extends('layouts.workspace')
@section('title', __('Transactions'))
@section('workspace')
@php
    $filtered = collect(['q','transaction_type','status'])->contains(fn ($key) => filled(request($key)));
    $tone = ['sale' => 'primary', 'rent' => 'success', 'bay_wafa' => 'purple'];
    $icon = ['sale' => 'key', 'rent' => 'house-user', 'bay_wafa' => 'file-contract'];
@endphp
<x-page-header :title="__('Transactions')" icon="handshake" :subtitle="__('Every sale, rent and mortgage (Bay-e-Wafa) in one clean deal history with Setta and commission context.')">
    <a class="btn btn-primary" href="{{ route('tenant.transactions.create') }}"><x-icon name="plus" class="me-1" /> {{ __('New deal') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('All deals')" :value="number_format($stats['total'])" icon="handshake" tone="blue" :note="__('Permanent history')" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Sales')" :value="number_format($stats['sale'])" icon="key" tone="green" :note="__('Ownership transfers')" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Rentals')" :value="number_format($stats['rent'])" icon="house-user" tone="amber" :note="__('Tenancy agreements')" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Mortgage')" :value="number_format($stats['mortgage'])" icon="file-contract" tone="violet" :note="__('Bay-e-Wafa / Bay-e-Jayezi')" /></div>
</div>

<div class="card">
    <div class="card-body">
        <form class="row g-2 mb-3" method="get" data-filter-form>
            <div class="col-12 col-md">
                <input type="search" class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Search reference, Setta, property title or code') }}" aria-label="{{ __('Search') }}" data-filter-search autocomplete="off">
            </div>
            <div class="col col-md-auto">
                <select class="form-select" name="transaction_type" aria-label="{{ __('Deal type') }}" data-plain onchange="this.form.requestSubmit()">
                    <option value="">{{ __('Any deal type') }}</option>
                    @foreach(config('rahnuma.transaction_types') as $s)<option value="{{ $s }}" @selected(request('transaction_type')===$s)>{{ deal_label($s, true) }}</option>@endforeach
                </select>
            </div>
            <div class="col col-md-auto">
                <select class="form-select" name="status" aria-label="{{ __('Transaction status') }}" data-plain onchange="this.form.requestSubmit()">
                    <option value="">{{ __('Any status') }}</option>
                    @foreach(['completed','draft','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ enum_label($s) }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-light-secondary" type="submit" aria-label="{{ __('Search') }}"><x-icon name="magnifying-glass" /></button>
                @if($filtered)<a class="btn btn-outline-soft" href="{{ route('tenant.transactions.index') }}">{{ __('Clear all') }}</a>@endif
            </div>
        </form>

        @if($transactions->isEmpty())
            <x-workspace.empty icon="handshake" :title="$filtered ? __('Nothing found') : __('No transactions yet')" :description="$filtered ? __('Try another search or clear the filters.') : __('When a customer and property are ready, start a deal. Rahnuma will keep the owner, commission and verification links together.')" />
        @else
            <table class="table table-hover w-100 align-middle">
                <thead><tr><th>{{ __('Property') }}</th><th>{{ __('Deal') }}</th><th>{{ __('Setta') }}</th><th>{{ __('Value') }}</th><th>{{ __('Commission') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                @foreach($transactions as $t)
                    <tr class="cursor-pointer" data-quick-view="{{ route('tenant.transactions.quick',$t) }}" data-quick-view-title="{{ __('Deal quick view') }}">
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm rounded-3 avatar-soft-{{ $tone[$t->transaction_type] ?? 'primary' }}"><x-icon :name="$icon[$t->transaction_type] ?? 'handshake'" /></span><div class="min-w-0"><strong class="d-block text-heading">{{ $t->property?->title ?: __('Property unavailable') }}</strong><span class="small text-muted" dir="ltr">{{ $t->reference_no }} · {{ $t->property?->code ?: '—' }}</span></div></div></td>
                        <td>{{ deal_label($t->transaction_type) }}</td>
                        <td dir="ltr">{{ $t->setta_number ?: '—' }}</td>
                        <td class="text-nowrap fw-semibold" dir="ltr">{{ number_format((float)($t->amount ?: $t->monthly_rent)) }} {{ $t->currency }}</td>
                        <td class="text-nowrap" dir="ltr">{{ number_format((float)$t->commission_total) }} {{ $t->currency }}</td>
                        <td><span class="badge-soft {{ status_tone($t->status) }}">{{ enum_label($t->status) }}</span></td>
                        <td class="text-nowrap" dir="ltr">{{ $t->completed_at?->format('d M Y') ?: $t->created_at?->format('d M Y') }}</td>
                        <td class="text-end text-nowrap"><div class="act-group"><a class="btn-act is-blue" href="{{ route('tenant.transactions.show',$t) }}" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-icon name="eye" /></a></div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $transactions->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
