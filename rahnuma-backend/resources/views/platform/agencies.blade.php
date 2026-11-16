@extends('layouts.workspace')
@section('title', __('Rahnama offices'))
@section('workspace')
<x-page-header :title="__('Rahnama offices')" icon="building-user" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Rahnama offices')]]" :subtitle="__('Every office on the system: open one to see and change everything about it.')">
    <button class="btn btn-primary" type="button" data-modal-open="office-create"><x-icon name="plus" class="me-1" /> {{ __('Add office') }}</button>
</x-page-header>

<div class="seg mb-3">
    @foreach (['' => ['All', 'all'], 'active' => ['Active', 'active'], 'verified' => ['Verified', 'verified'], 'disabled' => ['Disabled', 'disabled']] as $value => [$label, $key])
        <a class="{{ (string) request('status') === (string) $value ? 'active' : '' }}" href="{{ route('platform.agencies', array_filter(['status' => $value, 'q' => request('q'), 'plan_id' => request('plan_id')])) }}">{{ __($label) }} <span class="badge-count">{{ number_format($counts[$key]) }}</span></a>
    @endforeach
</div>

<form class="card" method="get">
    <div class="card-body"><div class="row g-2 align-items-end">
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <div class="col-12 col-md"><label class="form-label">{{ __('Search') }}</label><div class="input-icon"><x-icon name="magnifying-glass" /><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Name, license number, phone or email') }}"></div></div>
        <div class="col-12 col-md-3"><label class="form-label">{{ __('Plan') }}</label><select class="form-select" name="plan_id" data-placeholder="{{ __('Any plan') }}"><option value=""></option>@foreach ($plans as $p)<option value="{{ $p->id }}" @selected(request('plan_id') == $p->id)>{{ $p->name }}</option>@endforeach</select></div>
        <div class="col-12 col-md-auto"><button class="btn btn-primary w-100"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button></div>
    </div></div>
</form>

<div class="card">
    <div class="card-body">
        @if ($agencies->isEmpty())
            <x-workspace.empty icon="building-user" :title="__('No offices found.')" :description="__('Try another search, or add the first office.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Office') }}</th><th>{{ __('Plan') }}</th><th>{{ __('Users') }}</th><th>{{ __('Properties') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach ($agencies as $a)
                <tr>
                    <td><a class="d-flex align-items-center gap-2 text-decoration-none" href="{{ route('platform.agencies.show', $a) }}"><span class="avatar avatar-sm rounded-3 avatar-soft-primary">@if ($a->logo_path)<img src="{{ asset('storage/'.$a->logo_path) }}" alt="">@else{{ initial($a->name) }}@endif</span><span class="min-w-0"><strong class="d-block text-heading">{{ $a->name }} @if ($a->is_verified)<x-icon name="circle-check" class="text-success" />@endif</strong><span class="small text-muted">{{ loc_name($a->province) ?: '—' }}@if ($a->license_number) · <span dir="ltr">{{ $a->license_number }}</span>@endif</span></span></a></td>
                    <td>{{ $a->plan?->name ?: '—' }}</td>
                    <td>{{ number_format($a->users_count) }}</td>
                    <td>{{ number_format($a->properties_count) }}</td>
                    <td><span class="badge-soft {{ $a->is_active ? 'success' : 'danger' }}">{{ $a->is_active ? __('Active') : __('Disabled') }}</span></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-soft-primary" href="{{ route('platform.agencies.show', $a) }}">{{ __('View') }}</a>
                        <form method="post" action="{{ route('platform.agencies.toggle', $a) }}" class="d-inline">@csrf<button class="btn btn-sm btn-light-secondary">{{ $a->is_active ? __('Disable') : __('Enable') }}</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $agencies->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
@push('modals')
<x-workspace.drawer id="office-create" :title="__('Add office')" :description="__('Creates the office, the sign-in of its owner and a trial period.')" icon="building-user" wide>
<form method="post" action="{{ route('platform.agencies.store') }}">@csrf
<div class="modal-body"><div class="row g-3">
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Rahnama name') }} <span class="req">*</span></label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('License number') }}</label><input class="form-control" name="license_number" value="{{ old('license_number') }}" dir="ltr"></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Owner name') }} <span class="req">*</span></label><input class="form-control" name="owner_name" value="{{ old('owner_name') }}" required></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Phone') }}</label><input class="form-control" name="phone" value="{{ old('phone') }}" dir="ltr"></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Email') }} <span class="req">*</span></label><input class="form-control" type="email" name="email" value="{{ old('email') }}" dir="ltr" required></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Temporary password') }} <span class="req">*</span></label><input class="form-control" type="password" name="password" autocomplete="new-password" minlength="8" required></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Subscription plan') }}</label><select class="form-select" name="plan_id" data-placeholder="{{ __('No plan yet') }}"><option value=""></option>@foreach ($plans as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Trial days') }}</label><input class="form-control" type="number" name="trial_days" value="30" min="0" max="365" dir="ltr"></div>
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Create office') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
