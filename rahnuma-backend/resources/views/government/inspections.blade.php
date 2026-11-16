@extends('layouts.workspace')
@section('title', __('Inspections'))
@section('workspace')
<x-page-header :title="__('Inspections')" icon="clipboard-check" :crumbs="[[__('Regulatory'), route('government.dashboard')], [__('Inspections')]]" :subtitle="__('Record field inspections, findings and agency compliance activity.')" />

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Inspection history') }}</h5><div class="small text-muted">{{ __('Chronological supervision records') }}</div></div></div>
            <div class="card-body">
                @if($items->isEmpty())
                    <x-workspace.empty icon="clipboard-check" />
                @else
                <table class="table table-hover w-100">
                    <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th><th>{{ __('Summary') }}</th></tr></thead>
                    <tbody>@foreach($items as $i)<tr><td><strong class="text-heading">{{ $i->agency?->name }}</strong></td><td class="text-nowrap" dir="ltr">{{ $i->inspected_at }}</td><td><span class="badge-soft {{ status_tone($i->status) }}">{{ enum_label($i->status) }}</span></td><td>{{ $i->summary }}</td></tr>@endforeach</tbody>
                </table>
                {{ $items->links('partials.pagination') }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <form class="card" method="post" action="{{ route('government.inspections.store') }}">@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="plus" /></span><div><h5>{{ __('Add inspection') }}</h5><small>{{ __('Field record') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div><label class="form-label">{{ __('Agency') }}</label><select class="form-select" name="agency_id">@foreach($agencies as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('Date & time') }} <span class="req">*</span></label><input class="form-control" type="datetime-local" name="inspected_at" required></div>
                <div><label class="form-label">{{ __('Status') }}</label><input class="form-control" name="status" value="completed" dir="ltr"></div>
                <div><label class="form-label">{{ __('Inspection summary') }}</label><textarea class="form-control" name="summary" rows="3"></textarea></div>
            </div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save inspection') }}</button></div>
        </form>
    </div>
</div>
@endsection
