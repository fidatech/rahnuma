@extends('layouts.workspace')
@section('title', __('Requirements & Matching'))
@section('workspace')
<x-page-header :title="__('Requirements & Matching')" icon="bullseye" :subtitle="__('Capture what customers actually need, then let Rahnuma compare demand against your live inventory.')">
    <button class="btn btn-primary" type="button" data-modal-open="requirement-drawer"><x-icon name="plus" class="me-1" /> {{ __('New requirement') }}</button>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><x-stat :label="__('Active requirements')" :value="number_format($requirements->total())" icon="bullseye" tone="blue" :note="__('Customer demand')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Customers')" :value="number_format($customerCount)" icon="users" tone="violet" :note="__('Available for matching')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Matching engine')" :value="__('Live')" icon="bolt" tone="green" :note="__('Budget + type + location')" /></div>
</div>

@if($requirements->isEmpty())
    <div class="card"><div class="card-body"><x-workspace.empty icon="bullseye" :title="__('No customer requirements')" :description="__('Capture the first customer need and Rahnuma will start matching it with inventory.')" /></div></div>
@else
    <div class="row g-3">
    @foreach($requirements as $r)
        <div class="col-12 col-md-6 col-xl-4">
            <article class="card h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="avatar avatar-md avatar-soft-purple">{{ initial($r->customer?->name) }}</span>
                        <div class="flex-fill min-w-0"><span class="small text-muted">{{ __(':deal request', ['deal' => deal_label($r->transaction_type)]) }}</span><h3 class="h6 mb-0 text-truncate">{{ $r->customer?->name ?: __('Unknown customer') }}</h3><span class="small text-muted" dir="ltr">{{ $r->customer?->phone ?: __('No phone') }}</span></div>
                        <span class="badge-soft {{ $r->is_active ? 'success' : 'secondary' }}">{{ $r->is_active ? __('Active') : __('Closed') }}</span>
                    </div>
                    <div class="metric-box mb-3"><span>{{ __('Looking for') }}</span><strong>{{ enum_label($r->property_type) }}</strong></div>
                    <dl class="facts">
                        <div><dt>{{ __('Budget') }}</dt><dd dir="ltr">{{ number_format((float)$r->min_price) }} – {{ number_format((float)$r->max_price) }} {{ $r->currency }}</dd></div>
                        <div><dt>{{ __('Bedrooms') }}</dt><dd>{{ $r->min_bedrooms ?: __('Any') }}{{ $r->max_bedrooms ? ' – '.$r->max_bedrooms : '' }}</dd></div>
                        <div><dt>{{ __('Area') }}</dt><dd>{{ $r->min_area ?: __('Any') }}{{ $r->max_area ? ' – '.$r->max_area : '' }}</dd></div>
                    </dl>
                    @if($r->notes)<p class="small text-muted mt-3 mb-0">{{ \Illuminate\Support\Str::limit($r->notes,140) }}</p>@endif
                </div>
                <div class="card-footer"><a class="btn btn-sm btn-soft-primary w-100" href="{{ route('tenant.requirements.matches',$r) }}"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Find matching properties') }}</a></div>
            </article>
        </div>
    @endforeach
    </div>
    {{ $requirements->links('partials.pagination') }}
@endif
@endsection

@push('modals')
<x-workspace.drawer id="requirement-drawer" :title="__('New customer requirement')" :description="__('Describe what the customer is looking for once. Rahnuma will reuse it when new inventory arrives.')" icon="bullseye" :wide="true">
<form method="post" action="{{ route('tenant.requirements.store') }}">@csrf
<div class="modal-body">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center"><label class="form-label" for="requirement_customer_id">{{ __('Customer') }} <span class="req">*</span></label><button class="btn btn-sm btn-link p-0 mb-2 text-decoration-none" type="button" data-modal-open="quick-customer-modal" data-quick-target="requirement_customer_id"><x-icon name="plus" class="small" /> {{ __('New customer') }}</button></div>
            <select class="form-select" name="customer_id" id="requirement_customer_id" data-smart-resource="customer" data-remote-url="{{ route('tenant.lookup','customers') }}" data-placeholder="{{ __('Search customer by name or phone') }}" required><option value=""></option></select>
        </div>
        <div class="col-12 col-md-6"><label class="form-label">{{ __('Transaction') }}</label><select class="form-select" name="transaction_type">@foreach(config('rahnuma.transaction_types') as $x)<option value="{{ $x }}">{{ deal_label($x, true) }}</option>@endforeach</select></div>
        <div class="col-12 col-md-6"><label class="form-label">{{ __('Property type') }}</label><select class="form-select" name="property_type">@foreach(config('rahnuma.property_types') as $x)<option value="{{ $x }}">{{ enum_label($x) }}</option>@endforeach</select></div>
        <div class="col-12 col-md-4"><label class="form-label">{{ __('Minimum budget') }}</label><input class="form-control" type="number" step="any" name="min_price" dir="ltr"></div>
        <div class="col-12 col-md-4"><label class="form-label">{{ __('Maximum budget') }}</label><input class="form-control" type="number" step="any" name="max_price" dir="ltr"></div>
        <div class="col-12 col-md-4"><label class="form-label">{{ __('Currency') }}</label><select class="form-select" name="currency">@foreach(config('rahnuma.currencies') as $x)<option value="{{ $x }}">{{ $x }}</option>@endforeach</select></div>
        <div class="col-6 col-md-3"><label class="form-label">{{ __('Minimum bedrooms') }}</label><input class="form-control" type="number" name="min_bedrooms" dir="ltr"></div>
        <div class="col-6 col-md-3"><label class="form-label">{{ __('Maximum bedrooms') }}</label><input class="form-control" type="number" name="max_bedrooms" dir="ltr"></div>
        <div class="col-6 col-md-3"><label class="form-label">{{ __('Minimum area') }}</label><input class="form-control" type="number" step="any" name="min_area" dir="ltr"></div>
        <div class="col-6 col-md-3"><label class="form-label">{{ __('Maximum area') }}</label><input class="form-control" type="number" step="any" name="max_area" dir="ltr"></div>
        <div class="col-12"><label class="form-label">{{ __('Notes / preferred areas') }}</label><textarea class="form-control" name="notes" rows="3" placeholder="{{ __('Neighborhood, building preference, timing, special requirements') }}"></textarea></div>
    </div>
</div>
<div class="modal-footer"><span class="small text-muted me-auto">{{ __('The requirement remains private to this Rahnama.') }}</span><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save requirement') }}</button></div>
</form>
</x-workspace.drawer>
<x-quick-create.customer-modal />
@endpush
