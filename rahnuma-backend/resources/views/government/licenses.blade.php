@extends('layouts.workspace')
@section('title', __('Agency Licenses'))
@section('workspace')
<x-page-header :title="__('Agency Licenses')" icon="id-badge" :crumbs="[[__('Regulatory'), route('government.dashboard')], [__('Agency Licenses')]]" :subtitle="__('Track Rahnama license holders, validity and regulatory status.')" />

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('License register') }}</h5><div class="small text-muted">{{ __('Official-style regulatory records in demonstration mode') }}</div></div></div>
            <div class="card-body">
                @if($licenses->isEmpty())
                    <x-workspace.empty icon="id-badge" />
                @else
                <table class="table table-hover w-100">
                    <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('License') }}</th><th>{{ __('Holder') }}</th><th>{{ __('Expiry') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>@foreach($licenses as $l)<tr><td><strong class="text-heading">{{ $l->agency?->name }}</strong></td><td dir="ltr">{{ $l->license_number }}</td><td>{{ $l->holder_name }}</td><td dir="ltr">{{ $l->expiry_date ?: '—' }}</td><td><span class="badge-soft {{ status_tone($l->status) }}">{{ enum_label($l->status) }}</span></td></tr>@endforeach</tbody>
                </table>
                {{ $licenses->links('partials.pagination') }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <form class="card" method="post" action="{{ route('government.licenses.store') }}">@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="plus" /></span><div><h5>{{ __('Record license') }}</h5><small>{{ __('Registry action') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div><label class="form-label">{{ __('Agency') }}</label><select class="form-select" name="agency_id">@foreach($agencies as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('License number') }} <span class="req">*</span></label><input class="form-control" name="license_number" required dir="ltr"></div>
                <div><label class="form-label">{{ __('License holder') }} <span class="req">*</span></label><input class="form-control" name="holder_name" required></div>
                <div class="row g-2"><div class="col-6"><label class="form-label">{{ __('Issue date') }}</label><input class="form-control" type="date" name="issue_date"></div><div class="col-6"><label class="form-label">{{ __('Expiry') }}</label><input class="form-control" type="date" name="expiry_date"></div></div>
                <div><label class="form-label">{{ __('Status') }}</label><select class="form-select" name="status">@foreach(['pending','active','expired','suspended','cancelled'] as $s)<option value="{{ $s }}">{{ enum_label($s) }}</option>@endforeach</select></div>
            </div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save license record') }}</button></div>
        </form>
    </div>
</div>
@endsection
