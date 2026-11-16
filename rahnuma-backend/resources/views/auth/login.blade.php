@extends('layouts.auth')
@section('title', __('Sign in'))

@section('content')
<div class="auth-card">
    <div class="ac-icon"><x-icon name="right-to-bracket" /></div>
    <h2>{{ __('Welcome') }}</h2>
    <p class="ac-sub">{{ __('Sign in to your Rahnama workspace, the platform administration or the regulatory module.') }}</p>

    <form method="POST" action="{{ route('login') }}" data-native>
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('Email address') }}</label>
            <div class="auth-field"><x-icon name="envelope" set="regular" /><input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="username" placeholder="you@example.com" dir="ltr" required autofocus></div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">{{ __('Password') }}</label>
            <div class="auth-field"><x-icon name="lock" /><input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required><button type="button" class="field-eye" data-toggle-password aria-label="{{ __('Show password') }}"><x-icon name="eye" set="regular" /></button></div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label small" for="remember">{{ __('Keep me signed in') }}</label>
        </div>
        <button type="submit" class="btn btn-gradient btn-lg w-100">{{ __('Sign in') }} <x-icon name="arrow-right" class="ms-1" /></button>
    </form>
    <div class="auth-languages">
        @foreach (config('app.locales') as $code => $language)
            <a href="{{ route('locale', $code) }}" lang="{{ $code }}" class="{{ app()->getLocale() === $code ? 'active' : '' }}" data-native>{{ $language }}</a>
        @endforeach
    </div>
    <p class="text-center small mt-3 mb-0"><a href="{{ route('home') }}" data-native>{{ __('Browse the property marketplace') }}</a></p>
</div>
@endsection
