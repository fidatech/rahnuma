@extends('layouts.workspace')
@section('title', __('Violations'))
@section('workspace')
<x-page-header :title="__('Violations')" icon="triangle-exclamation" :crumbs="[[__('Regulatory'), route('government.dashboard')], [__('Violations')]]" :subtitle="__('Document regulatory incidents, severity, status and corrective action.')" />

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Violation register') }}</h5><div class="small text-muted">{{ __('Open and resolved compliance incidents') }}</div></div></div>
            <div class="card-body">
                @if($items->isEmpty())
                    <x-workspace.empty icon="triangle-exclamation" />
                @else
                <table class="table table-hover w-100">
                    <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Type') }}</th><th>{{ __('Severity') }}</th><th>{{ __('Status') }}</th><th>{{ __('Description') }}</th></tr></thead>
                    <tbody>@foreach($items as $i)<tr><td><strong class="text-heading">{{ $i->agency?->name }}</strong></td><td>{{ $i->type }}</td><td><span class="badge-soft {{ in_array($i->severity,['high','critical']) ? 'danger' : 'warning' }}">{{ enum_label($i->severity) }}</span></td><td><span class="badge-soft secondary">{{ enum_label($i->status) }}</span></td><td>{{ $i->description }}</td></tr>@endforeach</tbody>
                </table>
                {{ $items->links('partials.pagination') }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <form class="card" method="post" action="{{ route('government.violations.store') }}">@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="plus" /></span><div><h5>{{ __('Add violation') }}</h5><small>{{ __('Compliance action') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div><label class="form-label">{{ __('Agency') }}</label><select class="form-select" name="agency_id">@foreach($agencies as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('Violation type') }} <span class="req">*</span></label><input class="form-control" name="type" required></div>
                <div><label class="form-label">{{ __('Severity') }}</label><select class="form-select" name="severity">@foreach(['low','medium','high','critical'] as $s)<option value="{{ $s }}">{{ enum_label($s) }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('Description') }} <span class="req">*</span></label><textarea class="form-control" name="description" rows="3" required></textarea></div>
                <div><label class="form-label">{{ __('Action taken') }}</label><textarea class="form-control" name="action_taken" rows="3"></textarea></div>
            </div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save violation') }}</button></div>
        </form>
    </div>
</div>
@endsection
