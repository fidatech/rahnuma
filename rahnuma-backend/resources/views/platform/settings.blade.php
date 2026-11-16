@extends('layouts.workspace')
@section('title', __('Settings'))
@section('workspace')
@php
    $value = fn (string $key, $default = null) => old($key, $saved[$key] ?? $default);
    $emblem = config('rahnuma.official_emblem');
@endphp
<x-page-header :title="__('Settings')" icon="gear" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Settings')]]" :subtitle="__('Values saved here are used by the whole system in place of the .env file.')" />

<form method="post" action="{{ route('platform.settings.save') }}" enctype="multipart/form-data" data-native>@csrf
<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="phone" /></span><div><h5>{{ __('Contact details') }}</h5><small>{{ __('Shown in the top bar and the footer of the public site. Empty ones are hidden.') }}</small></div></div></div>
            <div class="card-body"><div class="row g-3">
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Phone') }}</label><input class="form-control" name="contact_phone" value="{{ $value('contact_phone', config('rahnuma.contact.phone')) }}" dir="ltr"></div>
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Email') }}</label><input class="form-control" type="email" name="contact_email" value="{{ $value('contact_email', config('rahnuma.contact.email')) }}" dir="ltr"></div>
                <div class="col-12"><label class="form-label">{{ __('Address') }}</label><input class="form-control" name="contact_address" value="{{ $value('contact_address', config('rahnuma.contact.address')) }}"></div>
                <div class="col-12"><label class="form-label">{{ __('Working hours') }}</label><input class="form-control" name="contact_hours" value="{{ $value('contact_hours', config('rahnuma.contact.hours')) }}"></div>
            </div></div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="landmark" /></span><div><h5>{{ __('Official status') }}</h5><small>{{ __('Use these only with the written permission of the authority.') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div class="rh-switches"><label class="form-check form-switch"><span>{{ __('Government mode') }}<small class="d-block text-muted fw-normal">{{ __('Removes the “private platform” notices and makes the setta number required on deals.') }}</small></span><input class="form-check-input" type="checkbox" role="switch" name="government_mode" value="1" @checked(config('rahnuma.government_mode'))></label></div>
                <div>
                    <label class="form-label">{{ __('Official emblem') }}</label>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        @if ($emblem)<img class="rh-emblem-preview" src="{{ asset($emblem) }}" alt="">@endif
                        <input class="form-control flex-fill" type="file" name="emblem" accept=".png,.jpg,.jpeg,.webp,.svg">
                    </div>
                    @if ($emblem)<label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_emblem" value="1"><span class="form-check-label">{{ __('Remove the emblem') }}</span></label>@endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="sack-dollar" /></span><div><h5>{{ __('Commission rules') }}</h5><small>{{ __('Used when an office records a deal. Deals already recorded do not change.') }}</small></div></div></div>
            <div class="card-body"><div class="row g-3">
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Sale and bay wafa commission (% of the amount)') }}</label><div class="input-group" dir="ltr"><input class="form-control" type="number" step="0.01" min="0" max="100" name="sale_baywafa_rate" value="{{ old('sale_baywafa_rate', rtrim(rtrim(number_format((float) config('rahnuma.sale_baywafa_rate') * 100, 4, '.', ''), '0'), '.')) }}" required><span class="input-group-text">%</span></div></div>
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Rent commission (months of rent)') }}</label><input class="form-control" type="number" step="0.01" min="0" max="12" name="rent_multiplier" value="{{ old('rent_multiplier', config('rahnuma.rent_multiplier')) }}" dir="ltr" required></div>
            </div></div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save settings') }}</button></div>
        </div>
    </div>
</div>
</form>
@endsection
