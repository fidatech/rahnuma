@extends('layouts.workspace')
@section('title', __('My account'))
@section('workspace')
<x-page-header :title="__('My account')" icon="id-badge" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('My account')]]" :subtitle="__('Your own name, sign-in email, language and password.')" />

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <form class="card h-100 mb-0" method="post" action="{{ route('platform.account.save') }}" data-native>@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="id-badge" /></span><div><h5>{{ __('Profile') }}</h5><small>{{ enum_label($user->role) }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div><label class="form-label">{{ __('Full name') }} <span class="req">*</span></label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required></div>
                <div><label class="form-label">{{ __('Email') }} <span class="req">*</span></label><input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $user->email) }}" dir="ltr" required></div>
                <div><label class="form-label">{{ __('Interface language') }}</label><select class="form-select" name="locale" data-plain>@foreach (config('app.locales') as $code => $language)<option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $language }}</option>@endforeach</select></div>
            </div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save changes') }}</button></div>
        </form>
    </div>
    <div class="col-12 col-xl-6">
        <form class="card h-100 mb-0" method="post" action="{{ route('platform.account.password') }}">@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="key" /></span><div><h5>{{ __('Change password') }}</h5><small>{{ __('At least 8 characters.') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div><label class="form-label">{{ __('Current password') }} <span class="req">*</span></label><input class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" autocomplete="current-password" required>@error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                <div><label class="form-label">{{ __('New password') }} <span class="req">*</span></label><input class="form-control @error('password') is-invalid @enderror" type="password" name="password" autocomplete="new-password" minlength="8" required>@error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                <div><label class="form-label">{{ __('Repeat the new password') }} <span class="req">*</span></label><input class="form-control" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required></div>
            </div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Change password') }}</button></div>
        </form>
    </div>
</div>
@endsection
