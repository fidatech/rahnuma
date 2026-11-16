@extends('layouts.workspace')
@section('title', __('Plans'))
@section('workspace')
<x-page-header :title="__('Plans')" icon="layer-group" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Plans')]]" :subtitle="__('What offices can subscribe to: prices and limits.')">
    <button class="btn btn-primary" type="button" data-modal-open="plan-create"><x-icon name="plus" class="me-1" /> {{ __('Add plan') }}</button>
</x-page-header>

@if ($plans->isEmpty())
    <div class="card"><div class="card-body"><x-workspace.empty icon="layer-group" :title="__('No plans yet.')" :description="__('Add the first plan offices can subscribe to.')" /></div></div>
@else
<div class="row g-3">
    @foreach ($plans as $plan)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card rh-plan h-100 mb-0 {{ $plan->is_active ? '' : 'is-off' }}">
                <div class="card-body d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-start gap-2"><div><h5 class="mb-1">{{ $plan->name }}</h5><span class="badge-code" dir="ltr">{{ $plan->code }}</span></div><span class="badge-soft {{ $plan->is_active ? 'success' : 'secondary' }}">{{ $plan->is_active ? __('On offer') : __('Switched off') }}</span></div>
                    <div class="rh-plan-price" dir="ltr"><strong>{{ number_format((float) $plan->monthly_price) }}</strong> <span>{{ config('rahnuma.base_currency') }} / {{ __('month') }}</span><small>{{ number_format((float) $plan->annual_price) }} {{ config('rahnuma.base_currency') }} / {{ __('year') }}</small></div>
                    <ul class="rh-plan-list">
                        <li><x-icon name="users" /> {{ $plan->user_limit ? __(':count users', ['count' => number_format($plan->user_limit)]) : __('Unlimited users') }}</li>
                        <li><x-icon name="building" /> {{ $plan->property_limit ? __(':count properties', ['count' => number_format($plan->property_limit)]) : __('Unlimited properties') }}</li>
                        @foreach ((array) $plan->features as $key => $feature)<li><x-icon name="circle-check" /> {{ is_string($feature) ? $feature : enum_label((string) $key) }}</li>@endforeach
                    </ul>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-2 border-top">
                        <a class="small fw-bold" href="{{ route('platform.agencies', ['plan_id' => $plan->id]) }}">{{ __(':count offices', ['count' => number_format($offices[$plan->id] ?? 0)]) }}</a>
                        <div class="rh-actions">
                            <button class="btn btn-sm btn-light-secondary" type="button" data-modal="plan-edit" data-action="{{ route('platform.plans.update', $plan) }}" data-edit="{{ json_encode(['name' => $plan->name, 'code' => $plan->code, 'monthly_price' => (float) $plan->monthly_price, 'annual_price' => (float) $plan->annual_price, 'user_limit' => $plan->user_limit, 'property_limit' => $plan->property_limit, 'is_active' => $plan->is_active, 'features' => implode("\n", array_filter((array) $plan->features, 'is_string'))]) }}"><x-icon name="pen" /> {{ __('Edit') }}</button>
                            <form method="post" action="{{ route('platform.plans.destroy', $plan) }}" data-confirm="{{ __('Delete this plan?') }}" data-confirm-text="{{ $plan->name }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" title="{{ __('Delete') }}"><x-icon name="trash" /></button></form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
@endsection
@push('modals')
@foreach (['plan-create' => [route('platform.plans.store'), 'Add plan', false], 'plan-edit' => ['#', 'Edit plan', true]] as $id => [$action, $title, $editing])
<x-workspace.drawer :id="$id" :title="__($title)" :description="__('Leave a limit empty for no limit. Prices are in afghani.')" icon="layer-group" wide>
<form method="post" action="{{ $action }}">@csrf @if ($editing) @method('PUT') @endif
<div class="modal-body"><div class="row g-3">
    <div class="col-12 col-md-7"><label class="form-label">{{ __('Plan name') }} <span class="req">*</span></label><input class="form-control" name="name" required></div>
    <div class="col-12 col-md-5"><label class="form-label">{{ __('Code') }} <span class="req">*</span></label><input class="form-control" name="code" dir="ltr" pattern="[A-Za-z0-9_-]+" required></div>
    <div class="col-6"><label class="form-label">{{ __('Monthly price') }} <span class="req">*</span></label><input class="form-control" type="number" step="0.01" min="0" name="monthly_price" dir="ltr" required></div>
    <div class="col-6"><label class="form-label">{{ __('Annual price') }} <span class="req">*</span></label><input class="form-control" type="number" step="0.01" min="0" name="annual_price" dir="ltr" required></div>
    <div class="col-6"><label class="form-label">{{ __('User limit') }}</label><input class="form-control" type="number" min="1" name="user_limit" dir="ltr" placeholder="∞"></div>
    <div class="col-6"><label class="form-label">{{ __('Property limit') }}</label><input class="form-control" type="number" min="1" name="property_limit" dir="ltr" placeholder="∞"></div>
    <div class="col-12"><label class="form-label">{{ __('What is included (one per line)') }}</label><textarea class="form-control" name="features" rows="4"></textarea></div>
    <div class="col-12"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" checked><span class="form-check-label">{{ __('Offices can subscribe to this plan') }}</span></label></div>
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save') }}</button></div>
</form>
</x-workspace.drawer>
@endforeach
@endpush
@push('scripts')
@include('platform._edit-script')
@endpush
