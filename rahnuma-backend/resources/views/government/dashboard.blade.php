@extends('layouts.workspace')
@section('title', __('Regulatory Dashboard'))
@section('workspace')
@php
    $meta = [
        'licensed' => ['Licensed agencies', 'id-badge', 'green'],
        'agencies' => ['Agencies', 'building-user', 'blue'],
        'setta_available' => ['Setta available', 'book', 'violet'],
        'setta_used' => ['Setta used', 'book-open', 'amber'],
        'transactions' => ['Transactions', 'handshake', 'blue'],
        'open_violations' => ['Open violations', 'triangle-exclamation', 'red'],
    ];
    $actions = [
        [route('government.licenses'), 'id-badge', 'primary', 'Agency licenses', 'Issue, verify and monitor status'],
        [route('government.setta'), 'book', 'purple', 'Setta inventory', 'Books, ranges and serial control'],
        [route('government.inspections'), 'clipboard-check', 'success', 'Inspections', 'Field visits and evidence'],
        [route('government.violations'), 'triangle-exclamation', 'danger', 'Violations', 'Compliance incidents and actions'],
    ];
@endphp
<x-page-header :title="__('National Regulatory Dashboard')" icon="landmark" :subtitle="__('License oversight, Setta inventory, transaction visibility and inspection activity.')">
    <a class="btn btn-outline-soft" href="{{ route('government.licenses') }}"><x-icon name="id-badge" class="me-1" /> {{ __('Licenses') }}</a>
    <a class="btn btn-outline-soft" href="{{ route('government.setta') }}"><x-icon name="book" class="me-1" /> {{ __('Setta Registry') }}</a>
    <a class="btn btn-primary" href="{{ route('government.inspections') }}"><x-icon name="clipboard-check" class="me-1" /> {{ __('Inspections') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
@foreach($stats as $k=>$v)
    <div class="col-6 col-xl-4"><x-stat :label="isset($meta[$k]) ? __($meta[$k][0]) : enum_label($k)" :value="number_format($v)" :icon="$meta[$k][1] ?? 'chart-column'" :tone="$meta[$k][2] ?? 'blue'" :note="__('National registry snapshot')" /></div>
@endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card h-100 mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Recent transactions') }}</h5><div class="small text-muted">{{ __('Latest regulatory transaction records across agencies') }}</div></div></div>
            <div class="card-body">
                <table class="table table-hover w-100">
                    <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Agency') }}</th><th>{{ __('Type') }}</th><th>{{ __('Setta') }}</th><th class="text-end">{{ __('Value') }}</th></tr></thead>
                    <tbody>@foreach($transactions as $t)<tr><td><span class="badge-code">{{ $t->reference_no }}</span></td><td dir="ltr">#{{ $t->agency_id }}</td><td>{{ deal_label($t->transaction_type) }}</td><td dir="ltr">{{ $t->setta_number ?: '—' }}</td><td class="text-end fw-semibold text-nowrap" dir="ltr">{{ number_format($t->amount?:$t->monthly_rent) }} {{ $t->currency }}</td></tr>@endforeach</tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Regulatory actions') }}</h5><div class="small text-muted">{{ __('Core supervision workspaces') }}</div></div></div>
            <div class="card-body">
                <ul class="list-clean">
                @foreach ($actions as [$url, $icon, $tone, $label, $text])
                    <li><span class="sc-icon icon-soft {{ $tone }} rh-choice-icon"><x-icon :name="$icon" /></span><a class="flex-fill min-w-0 text-decoration-none text-reset" href="{{ $url }}"><strong class="d-block text-heading">{{ __($label) }}</strong><span class="small text-muted">{{ __($text) }}</span></a><x-icon name="chevron-right" class="text-muted rh-flip" /></li>
                @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
