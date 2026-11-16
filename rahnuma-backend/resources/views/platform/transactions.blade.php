@extends('layouts.workspace')
@section('title', __('All deals'))
@section('workspace')
<x-page-header :title="__('All deals')" icon="handshake" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('All deals')]]" :subtitle="__('Every sale, rent and bay wafa deal recorded by any office, with its verification code.')" />

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('All deals')" :value="number_format($counts['all'])" icon="handshake" tone="blue" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="deal_label('sale', true)" :value="number_format($counts['sale'])" icon="file-contract" tone="green" :href="route('platform.transactions', ['transaction_type' => 'sale'])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="deal_label('rent', true)" :value="number_format($counts['rent'])" icon="key" tone="violet" :href="route('platform.transactions', ['transaction_type' => 'rent'])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="deal_label('bay_wafa', true)" :value="number_format($counts['bay_wafa'])" icon="scale-balanced" tone="amber" :href="route('platform.transactions', ['transaction_type' => 'bay_wafa'])" /></div>
</div>

<form class="card" method="get">
    <div class="card-body"><div class="row g-2 align-items-end">
        <div class="col-12 col-lg"><label class="form-label">{{ __('Search') }}</label><div class="input-icon"><x-icon name="magnifying-glass" /><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Reference, verification code or setta number') }}" dir="auto"></div></div>
        <div class="col-6 col-lg-3"><label class="form-label">{{ __('Office') }}</label><select class="form-select" name="agency_id" data-placeholder="{{ __('Any office') }}"><option value=""></option>@foreach ($agencies as $a)<option value="{{ $a->id }}" @selected(request('agency_id') == $a->id)>{{ $a->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Deal') }}</label><select class="form-select" name="transaction_type" data-placeholder="{{ __('Any deal') }}"><option value=""></option>@foreach (config('rahnuma.transaction_types') as $t)<option value="{{ $t }}" @selected(request('transaction_type') === $t)>{{ deal_label($t, true) }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Status') }}</label><select class="form-select" name="status" data-placeholder="{{ __('Any') }}"><option value=""></option>@foreach ($statuses as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ enum_label($s) }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-auto d-flex gap-2"><button class="btn btn-primary flex-fill"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button>@if (request()->hasAny(['q', 'agency_id', 'transaction_type', 'status']))<a class="btn btn-outline-soft" href="{{ route('platform.transactions') }}">{{ __('Reset') }}</a>@endif</div>
    </div></div>
</form>

<div class="card">
    <div class="card-body">
        @if ($transactions->isEmpty())
            <x-workspace.empty icon="handshake" :title="__('No deals found.')" :description="__('Deals recorded by offices will show here.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Office') }}</th><th>{{ __('Property') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th class="text-end">{{ __('Verify') }}</th></tr></thead>
            <tbody>
            @foreach ($transactions as $t)
                <tr>
                    <td><span class="badge-code" dir="ltr">{{ $t->reference_no ?: '#'.$t->id }}</span>@if ($t->setta_number)<span class="small text-muted d-block" dir="ltr">{{ __('Setta number') }} {{ $t->setta_number }}</span>@endif</td>
                    <td>@if ($t->agency)<a href="{{ route('platform.agencies.show', $t->agency) }}">{{ $t->agency->name }}</a>@else—@endif</td>
                    <td><span class="d-block text-truncate" style="max-width:240px">{{ $t->property?->title ?: '—' }}</span><span class="small text-muted" dir="ltr">{{ $t->property?->code }}</span></td>
                    <td>{{ deal_label($t->transaction_type) }}</td>
                    <td dir="ltr" class="fw-bold text-nowrap">{{ number_format((float) ($t->amount ?: $t->monthly_rent)) }} {{ $t->currency }}</td>
                    <td><span class="badge-soft {{ status_tone($t->status) }}">{{ enum_label($t->status) }}</span></td>
                    <td dir="ltr" class="text-nowrap">{{ $t->created_at?->format('Y-m-d') }}</td>
                    <td class="text-end">@if ($t->verification_code)<a class="btn btn-sm btn-soft-primary" href="{{ route('verify', $t->verification_code) }}" target="_blank" rel="noopener" dir="ltr"><x-icon name="shield-halved" /> {{ $t->verification_code }}</a>@else—@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $transactions->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
