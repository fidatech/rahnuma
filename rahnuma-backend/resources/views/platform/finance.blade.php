@extends('layouts.workspace')
@section('title', __('Money in & out'))
@section('workspace')
@php
    $kinds = ['in' => \App\Models\PlatformEntry::IN, 'out' => \App\Models\PlatformEntry::OUT];
@endphp
<x-page-header :title="__('Money in & out')" icon="sack-dollar" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Money in & out')]]" :subtitle="__('Income and costs of the platform itself. A subscription marked as paid is added to money in by the system.')">
    <button class="btn btn-soft-danger" type="button" data-modal="money-entry" data-title="{{ __('Record money out') }}" data-edit="{{ json_encode(['direction' => 'out', 'category' => 'hosting']) }}"><x-icon name="circle-arrow-up" class="me-1" /> {{ __('Money out') }}</button>
    <button class="btn btn-primary" type="button" data-modal="money-entry" data-title="{{ __('Record money in') }}" data-edit="{{ json_encode(['direction' => 'in', 'category' => 'subscription']) }}"><x-icon name="circle-arrow-down" class="me-1" /> {{ __('Money in') }}</button>
</x-page-header>

<form class="card" method="get">
    <div class="card-body"><div class="row g-2 align-items-end">
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('From') }}</label><input class="form-control" type="date" name="from" value="{{ $from }}" dir="ltr"></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('To') }}</label><input class="form-control" type="date" name="to" value="{{ $to }}" dir="ltr"></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Side') }}</label><select class="form-select" name="direction" data-placeholder="{{ __('In and out') }}"><option value=""></option><option value="in" @selected(request('direction') === 'in')>{{ __('Money in') }}</option><option value="out" @selected(request('direction') === 'out')>{{ __('Money out') }}</option></select></div>
        <div class="col-6 col-lg-3"><label class="form-label">{{ __('Kind') }}</label><select class="form-select" name="category" data-placeholder="{{ __('Any kind') }}"><option value=""></option>@foreach (array_merge($kinds['in'], $kinds['out']) as $k)<option value="{{ $k }}" @selected(request('category') === $k)>{{ enum_label($k) }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Currency') }}</label><select class="form-select" name="currency" data-placeholder="{{ __('Any currency') }}"><option value=""></option>@foreach (config('rahnuma.currencies') as $c)<option value="{{ $c }}" @selected(request('currency') === $c)>{{ $c }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-auto"><button class="btn btn-primary w-100"><x-icon name="filter" class="me-1" /> {{ __('Show') }}</button></div>
    </div></div>
</form>

<div class="row g-3 mb-3">
    @foreach ($totals as $currency => $t)
        <div class="col-12 col-xl-6">
            <div class="card rh-money mb-0">
                <div class="card-body">
                    <div class="rh-money-head"><strong>{{ $currency }}</strong><span dir="ltr">{{ $from }} → {{ $to }}</span></div>
                    <div class="rh-money-row">
                        <div class="is-in"><span><x-icon name="circle-arrow-down" /> {{ __('Money in') }}</span><strong dir="ltr">{{ number_format($t['in']) }}</strong></div>
                        <div class="is-out"><span><x-icon name="circle-arrow-up" /> {{ __('Money out') }}</span><strong dir="ltr">{{ number_format($t['out']) }}</strong></div>
                        <div class="{{ $t['net'] >= 0 ? 'is-in' : 'is-out' }} is-net"><span><x-icon name="scale-balanced" /> {{ __('What is left') }}</span><strong dir="ltr">{{ number_format($t['net']) }}</strong></div>
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
                            <td><span class="badge-soft {{ $e->direction === 'in' ? 'success' : 'danger' }}"><x-icon :name="$e->direction === 'in' ? 'circle-arrow-down' : 'circle-arrow-up'" /> {{ enum_label($e->category) }}</span></td>
                            <td>@if ($e->agency)<a href="{{ route('platform.agencies.show', $e->agency) }}">{{ $e->agency->name }}</a>@else—@endif</td>
                            <td><span class="d-block text-truncate" style="max-width:220px">{{ $e->notes ?: '—' }}</span>@if ($e->reference)<span class="small text-muted" dir="ltr">{{ $e->reference }}</span>@endif</td>
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
    </div>
</div>
@endsection
@push('modals')
<x-workspace.drawer id="money-entry" :title="__('Money in & out')" :description="__('One entry for one payment received or one cost paid.')" icon="sack-dollar" wide>
<form method="post" action="{{ route('platform.finance.store') }}">@csrf
<div class="modal-body"><div class="row g-3">
    <div class="col-12"><p class="fw-bold text-heading mb-0" data-edit-title></p></div>
    <div class="col-6 col-md-4"><label class="form-label">{{ __('Side') }} <span class="req">*</span></label><select class="form-select" name="direction" data-plain required><option value="in">{{ __('Money in') }}</option><option value="out">{{ __('Money out') }}</option></select></div>
    <div class="col-6 col-md-4"><label class="form-label">{{ __('Kind') }} <span class="req">*</span></label><select class="form-select" name="category" data-plain required>@foreach ($kinds as $side => $list)<optgroup label="{{ $side === 'in' ? __('Money in') : __('Money out') }}">@foreach ($list as $k)<option value="{{ $k }}">{{ enum_label($k) }}</option>@endforeach</optgroup>@endforeach</select></div>
    <div class="col-12 col-md-4"><label class="form-label">{{ __('Date') }} <span class="req">*</span></label><input class="form-control" type="date" name="entry_date" value="{{ now()->format('Y-m-d') }}" dir="ltr" required></div>
    <div class="col-7 col-md-4"><label class="form-label">{{ __('Amount') }} <span class="req">*</span></label><input class="form-control" type="number" step="0.01" min="0.01" name="amount" dir="ltr" required></div>
    <div class="col-5 col-md-4"><label class="form-label">{{ __('Currency') }}</label><select class="form-select" name="currency" data-plain>@foreach (config('rahnuma.currencies') as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>
    <div class="col-12 col-md-4"><label class="form-label">{{ __('Reference') }}</label><input class="form-control" name="reference" dir="ltr" placeholder="{{ __('Receipt or invoice number') }}"></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Office') }}</label><select class="form-select" name="agency_id" data-plain><option value="">{{ __('Not about one office') }}</option>@foreach ($agencies as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Notes') }}</label><input class="form-control" name="notes"></div>
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
@push('scripts')
@include('platform._edit-script')
@endpush
