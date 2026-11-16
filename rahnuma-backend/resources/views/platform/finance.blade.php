@extends('layouts.workspace')
@section('title', __('Money in & out'))
@section('workspace')
@php
    $sides = ['in' => ['Money in', 'circle-arrow-down', 'Record money in', 'A payment the platform received.'], 'out' => ['Money out', 'circle-arrow-up', 'Record money out', 'A cost the platform paid.']];
    $kindsOf = fn (string $side) => $kinds[$side] ?? collect();
@endphp
<x-page-header :title="__('Money in & out')" icon="sack-dollar" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Money in & out')]]" :subtitle="__('Income and costs of the platform itself. Paid subscriptions and promotion fees are added to money in by the system.')">
    <button class="btn rh-btn-out" type="button" data-modal-open="money-out"><x-icon name="circle-arrow-up" class="me-1" /> {{ __('Money out') }}</button>
    <button class="btn rh-btn-in" type="button" data-modal-open="money-in"><x-icon name="circle-arrow-down" class="me-1" /> {{ __('Money in') }}</button>
</x-page-header>

<div class="row g-3 mb-3">
    @foreach ($totals as $currency => $t)
        @php $sum = $t['in'] + $t['out']; $share = $sum > 0 ? round($t['in'] / $sum * 100) : 50; @endphp
        <div class="col-12 col-xl-6">
            <div class="card rh-money mb-0">
                <div class="card-body">
                    <div class="rh-money-head"><strong>{{ $currency }}</strong><span dir="ltr">{{ $from }} → {{ $to }}</span></div>
                    <div class="rh-money-net {{ $t['net'] >= 0 ? 'is-in' : 'is-out' }}"><span>{{ __('What is left') }}</span><strong dir="ltr">{{ $t['net'] < 0 ? '−' : '' }}{{ number_format(abs($t['net'])) }} <small>{{ $currency }}</small></strong></div>
                    <div class="rh-money-bar" role="img" aria-label="{{ __('Money in') }} {{ $share }}%"><span style="width: {{ $share }}%"></span></div>
                    <div class="rh-money-row">
                        <div class="is-in"><span><x-icon name="circle-arrow-down" /> {{ __('Money in') }}</span><strong dir="ltr">{{ number_format($t['in']) }}</strong></div>
                        <div class="is-out"><span><x-icon name="circle-arrow-up" /> {{ __('Money out') }}</span><strong dir="ltr">{{ number_format($t['out']) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Entries') }}</h5><div class="small text-muted">{{ __(':count entries', ['count' => number_format($entries->total())]) }}</div></div></div>
            <form class="rh-money-filter" method="get">
                <div><label class="form-label">{{ __('From') }}</label><input class="form-control form-control-sm" type="date" name="from" value="{{ $from }}" dir="ltr"></div>
                <div><label class="form-label">{{ __('To') }}</label><input class="form-control form-control-sm" type="date" name="to" value="{{ $to }}" dir="ltr"></div>
                <div><label class="form-label">{{ __('Side') }}</label><select class="form-select form-select-sm" name="direction" data-plain><option value="">{{ __('In and out') }}</option><option value="in" @selected(request('direction') === 'in')>{{ __('Money in') }}</option><option value="out" @selected(request('direction') === 'out')>{{ __('Money out') }}</option></select></div>
                <div><label class="form-label">{{ __('Kind') }}</label><select class="form-select form-select-sm" name="category" data-plain><option value="">{{ __('Any kind') }}</option>@foreach ($sides as $side => $meta)<optgroup label="{{ __($meta[0]) }}">@foreach ($kindsOf($side) as $k)<option value="{{ $k->name }}" @selected(request('category') === $k->name)>{{ enum_label($k->name) }}</option>@endforeach</optgroup>@endforeach</select></div>
                <div><label class="form-label">{{ __('Currency') }}</label><select class="form-select form-select-sm" name="currency" data-plain><option value="">{{ __('Any currency') }}</option>@foreach (config('rahnuma.currencies') as $c)<option value="{{ $c }}" @selected(request('currency') === $c)>{{ $c }}</option>@endforeach</select></div>
                <button class="btn btn-sm btn-primary"><x-icon name="filter" class="me-1" /> {{ __('Show') }}</button>
            </form>
            <div class="card-body">
                @if ($entries->isEmpty())
                    <x-workspace.empty icon="sack-dollar" :title="__('No entries in this period.')" :description="__('Record money in or money out with the buttons above.')" />
                @else
                <table class="table table-hover w-100 align-middle">
                    <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Kind') }}</th><th>{{ __('Office') }}</th><th>{{ __('Notes') }}</th><th class="text-end">{{ __('Amount') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($entries as $e)
                        <tr>
                            <td dir="ltr" class="text-nowrap">{{ $e->entry_date?->format('Y-m-d') }}</td>
                            <td><span class="rh-kind {{ $e->direction === 'in' ? 'is-in' : 'is-out' }}"><x-icon :name="$e->direction === 'in' ? 'circle-arrow-down' : 'circle-arrow-up'" /> {{ enum_label($e->category) }}</span></td>
                            <td>@if ($e->agency)<a href="{{ route('platform.agencies.show', $e->agency) }}">{{ $e->agency->name }}</a>@else—@endif</td>
                            <td><span class="d-block text-truncate" style="max-width:200px">{{ $e->notes ?: '—' }}</span>@if ($e->reference)<span class="small text-muted" dir="ltr">{{ $e->reference }}</span>@endif</td>
                            <td class="text-end fw-bold text-nowrap {{ $e->direction === 'in' ? 'text-success' : 'text-danger' }}" dir="ltr">{{ $e->direction === 'in' ? '+' : '−' }}{{ number_format((float) $e->amount) }} {{ $e->currency }}</td>
                            <td class="text-end"><form method="post" action="{{ route('platform.finance.destroy', $e) }}" data-confirm="{{ __('Delete this entry?') }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" title="{{ __('Delete') }}"><x-icon name="trash" /></button></form></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $entries->links('partials.pagination') }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('By kind') }}</h5><div class="small text-muted">{{ __('Totals of the period') }}</div></div></div>
            <div class="card-body"><div class="rh-list">
                @forelse ($byCategory as $row)
                    <div class="rh-list-row"><span class="rh-list-icon {{ $row->direction === 'in' ? 'is-green' : 'is-red' }}"><x-icon :name="$row->direction === 'in' ? 'circle-arrow-down' : 'circle-arrow-up'" /></span><span class="flex-fill fw-bold text-heading">{{ enum_label($row->category) }}</span><strong dir="ltr">{{ number_format((float) $row->total) }} {{ $row->currency }}</strong></div>
                @empty
                    <x-workspace.empty icon="chart-column" :title="__('Nothing to total yet.')" :description="__('Totals appear once there are entries.')" />
                @endforelse
            </div></div>
        </div>
        <div class="card mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Kinds') }}</h5><div class="small text-muted">{{ __('The list you choose from when you record an entry.') }}</div></div><button class="btn btn-sm btn-primary" type="button" data-modal-open="kind-create"><x-icon name="plus" /> {{ __('Add kind') }}</button></div>
            <div class="card-body d-grid gap-3">
                @foreach ($sides as $side => $meta)
                    <div>
                        <div class="rh-kind-title {{ $side === 'in' ? 'is-in' : 'is-out' }}"><x-icon :name="$meta[1]" /> {{ __($meta[0]) }}</div>
                        <div class="rh-list">
                            @foreach ($kindsOf($side) as $k)
                                <div class="rh-list-row"><span class="flex-fill min-w-0"><strong class="d-block text-truncate">{{ enum_label($k->name) }}</strong><small>{{ __(':count entries', ['count' => number_format($used[$side.':'.$k->name] ?? 0)]) }}</small></span>
                                    @if ($k->is_system)
                                        <span class="badge-soft secondary" title="{{ __('This kind is used by the system and cannot be changed.') }}">{{ __('System') }}</span>
                                    @else
                                        <div class="rh-actions">
                                            <button class="btn btn-sm btn-light-secondary" type="button" title="{{ __('Edit') }}" data-modal="kind-edit" data-action="{{ route('platform.finance.kinds.update', $k) }}" data-edit="{{ json_encode(['name' => $k->name]) }}"><x-icon name="pen" /></button>
                                            <form method="post" action="{{ route('platform.finance.kinds.destroy', $k) }}" data-confirm="{{ __('Delete this kind?') }}" data-confirm-text="{{ __('Entries already recorded keep its name.') }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" title="{{ __('Delete') }}"><x-icon name="trash" /></button></form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
@push('modals')
@foreach ($sides as $side => $meta)
<div class="rh-money-modal {{ $side === 'in' ? 'is-in' : 'is-out' }}">
<x-workspace.drawer :id="'money-'.$side" :title="__($meta[2])" :description="__($meta[3])" :icon="$meta[1]">
<form method="post" action="{{ route('platform.finance.store') }}">@csrf
<input type="hidden" name="direction" value="{{ $side }}">
<div class="modal-body d-grid gap-3">
    <fieldset class="rh-field"><legend>{{ __('Amount') }} <span class="req">*</span></legend>
        <div class="rh-amount"><input class="form-control" type="number" step="0.01" min="0.01" name="amount" dir="ltr" placeholder="0" inputmode="decimal" required><div class="rh-chips">@foreach (config('rahnuma.currencies') as $c)<label class="rh-chip"><input type="radio" name="currency" value="{{ $c }}" @checked($loop->first)><span>{{ $c }}</span></label>@endforeach</div></div>
    </fieldset>
    <fieldset class="rh-field"><legend>{{ __('Kind') }} <span class="req">*</span></legend>
        <div class="rh-chips">@foreach ($kindsOf($side) as $k)<label class="rh-chip"><input type="radio" name="category" value="{{ $k->name }}" @checked($loop->first) required><span>{{ enum_label($k->name) }}</span></label>@endforeach</div>
    </fieldset>
    <div class="row g-3">
        <div class="col-12 col-md-6"><label class="form-label">{{ __('Date') }} <span class="req">*</span></label><input class="form-control" type="date" name="entry_date" value="{{ now()->format('Y-m-d') }}" dir="ltr" required></div>
        <div class="col-12 col-md-6"><label class="form-label">{{ __('Reference') }}</label><input class="form-control" name="reference" dir="ltr" placeholder="{{ __('Receipt or invoice number') }}"></div>
        <div class="col-12"><label class="form-label">{{ __('Office') }}</label><select class="form-select" name="agency_id" data-plain><option value="">{{ __('Not about one office') }}</option>@foreach ($agencies as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label">{{ __('Notes') }}</label><input class="form-control" name="notes" maxlength="1000"></div>
    </div>
</div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn {{ $side === 'in' ? 'rh-btn-in' : 'rh-btn-out' }}" type="submit"><x-icon name="check" class="me-1" /> {{ __($meta[2]) }}</button></div>
</form>
</x-workspace.drawer>
</div>
@endforeach
<x-workspace.drawer id="kind-create" :title="__('Add kind')" :description="__('A new choice for money in or for money out.')" icon="plus">
<form method="post" action="{{ route('platform.finance.kinds.store') }}">@csrf
<div class="modal-body d-grid gap-3">
    <fieldset class="rh-field"><legend>{{ __('Side') }}</legend><div class="rh-chips">@foreach ($sides as $side => $meta)<label class="rh-chip"><input type="radio" name="direction" value="{{ $side }}" @checked($loop->first)><span>{{ __($meta[0]) }}</span></label>@endforeach</div></fieldset>
    <div><label class="form-label">{{ __('Name') }} <span class="req">*</span></label><input class="form-control" name="name" maxlength="60" required></div>
</div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Add kind') }}</button></div>
</form>
</x-workspace.drawer>
<x-workspace.drawer id="kind-edit" :title="__('Rename kind')" :description="__('Entries recorded under the old name get the new one.')" icon="pen">
<form method="post" action="#">@csrf @method('PUT')
<div class="modal-body"><label class="form-label">{{ __('Name') }} <span class="req">*</span></label><input class="form-control" name="name" maxlength="60" required></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
@push('scripts')
@include('platform._edit-script')
@endpush
