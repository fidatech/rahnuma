@extends('layouts.workspace')
@section('title', __('Subscriptions'))
@section('workspace')
<x-page-header :title="__('Subscriptions')" icon="receipt" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Subscriptions')]]" :subtitle="__('Which office pays for which plan, until when, and whether it is paid.')">
    <button class="btn btn-primary" type="button" data-modal-open="subscription-create"><x-icon name="plus" class="me-1" /> {{ __('Add subscription') }}</button>
</x-page-header>

<div class="seg mb-3">
    <a class="{{ request('status') ? '' : 'active' }}" href="{{ route('platform.subscriptions', array_filter(['agency_id' => request('agency_id')])) }}">{{ __('All') }}</a>
    @foreach (['active' => 'Active', 'expiring' => 'Ending soon', 'expired' => 'Expired', 'unpaid' => 'Unpaid'] as $value => $label)
        <a class="{{ request('status') === $value ? 'active' : '' }}" href="{{ route('platform.subscriptions', array_filter(['status' => $value, 'agency_id' => request('agency_id')])) }}">{{ __($label) }} <span class="badge-count">{{ number_format($counts[$value]) }}</span></a>
    @endforeach
    <a class="{{ request('status') === 'cancelled' ? 'active' : '' }}" href="{{ route('platform.subscriptions', array_filter(['status' => 'cancelled', 'agency_id' => request('agency_id')])) }}">{{ __('Cancelled') }}</a>
</div>

<div class="card">
    <div class="card-body">
        @if ($subscriptions->isEmpty())
            <x-workspace.empty icon="receipt" :title="__('No subscriptions here.')" :description="__('Add a subscription for an office to start its paid period.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Office') }}</th><th>{{ __('Plan') }}</th><th>{{ __('Period') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach ($subscriptions as $s)
                @php
                    $ended = $s->ends_at && $s->ends_at->isPast();
                    $state = $s->status === 'cancelled' ? ['Cancelled', 'danger'] : ($ended ? ['Expired', 'secondary'] : ['Active', 'success']);
                @endphp
                <tr>
                    <td>@if ($s->agency)<a class="fw-bold text-heading text-decoration-none" href="{{ route('platform.agencies.show', $s->agency) }}">{{ $s->agency->name }}</a>@else—@endif</td>
                    <td>{{ $s->plan?->name ?: '—' }} <span class="small text-muted d-block">{{ enum_label($s->billing_cycle) }}</span></td>
                    <td dir="ltr" class="text-nowrap">{{ $s->starts_at?->format('Y-m-d') }} → {{ $s->ends_at?->format('Y-m-d') ?: '—' }}</td>
                    <td dir="ltr" class="fw-bold text-nowrap">{{ number_format((float) $s->amount) }} {{ $s->currency }}</td>
                    <td><span class="badge-soft {{ $state[1] }}">{{ __($state[0]) }}</span> <span class="badge-soft {{ $s->paid_at ? 'success' : 'warning' }}">{{ $s->paid_at ? __('Paid') : __('Unpaid') }}</span></td>
                    <td class="text-end text-nowrap">
                        <div class="rh-actions">
                            @unless ($s->paid_at || $s->status === 'cancelled')<form method="post" action="{{ route('platform.subscriptions.paid', $s->id) }}">@csrf<button class="btn btn-sm btn-soft-success"><x-icon name="circle-check" /> <span>{{ __('Mark paid') }}</span></button></form>@endunless
                            @unless ($s->status === 'cancelled')<form method="post" action="{{ route('platform.subscriptions.renew', $s->id) }}">@csrf<button class="btn btn-sm btn-soft-primary" title="{{ __('Renew') }}"><x-icon name="rotate" /> <span>{{ __('Renew') }}</span></button></form>@endunless
                            @unless ($s->status === 'cancelled' || $ended)<form method="post" action="{{ route('platform.subscriptions.cancel', $s->id) }}" data-confirm="{{ __('Cancel this subscription?') }}" data-confirm-ok="{{ __('Cancel subscription') }}">@csrf<button class="btn btn-sm btn-light-secondary" title="{{ __('Cancel subscription') }}"><x-icon name="ban" /></button></form>@endunless
                            <form method="post" action="{{ route('platform.subscriptions.destroy', $s->id) }}" data-confirm="{{ __('Delete this subscription?') }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" title="{{ __('Delete') }}"><x-icon name="trash" /></button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $subscriptions->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
@push('modals')
<x-workspace.drawer id="subscription-create" :title="__('Add subscription')" :description="__('The end date is set from the start date and the cycle. Leave the amount empty to use the price of the plan.')" icon="receipt" wide>
<form method="post" action="{{ route('platform.subscriptions.store') }}">@csrf
<div class="modal-body"><div class="row g-3">
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Office') }} <span class="req">*</span></label><select class="form-select" name="agency_id" required data-placeholder="{{ __('Choose an office') }}"><option value=""></option>@foreach ($agencies as $a)<option value="{{ $a->id }}" @selected(request('agency_id') == $a->id)>{{ $a->name }}</option>@endforeach</select></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Plan') }} <span class="req">*</span></label><select class="form-select" name="plan_id" data-plain required>@foreach ($plans as $p)<option value="{{ $p->id }}">{{ $p->name }} · {{ number_format((float) $p->monthly_price) }} / {{ number_format((float) $p->annual_price) }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><label class="form-label">{{ __('Cycle') }}</label><select class="form-select" name="billing_cycle" data-plain><option value="monthly">{{ __('Monthly') }}</option><option value="annual">{{ __('Annual') }}</option></select></div>
    <div class="col-6 col-md-3"><label class="form-label">{{ __('Starts') }} <span class="req">*</span></label><input class="form-control" type="date" name="starts_at" value="{{ now()->format('Y-m-d') }}" dir="ltr" required></div>
    <div class="col-6 col-md-3"><label class="form-label">{{ __('Amount') }}</label><input class="form-control" type="number" step="0.01" min="0" name="amount" dir="ltr" placeholder="{{ __('Plan price') }}"></div>
    <div class="col-6 col-md-3"><label class="form-label">{{ __('Currency') }}</label><select class="form-select" name="currency" data-plain>@foreach (config('rahnuma.currencies') as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label">{{ __('Notes') }}</label><input class="form-control" name="notes"></div>
    <div class="col-12"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="paid" value="1"><span class="form-check-label">{{ __('Already paid: record it in money in') }}</span></label></div>
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Add subscription') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
