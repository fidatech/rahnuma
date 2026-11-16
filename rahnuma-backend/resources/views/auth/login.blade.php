@extends('layouts.auth')
@section('title', __('Sign in'))

@section('content')
<div class="rh-auth-card">
    <span class="rh-auth-icon"><x-icon name="right-to-bracket" /></span>
    <h2>{{ __('Sign in') }}</h2>
    <p class="rh-auth-sub">{{ __('Sign in to your Rahnama workspace, the platform administration or the regulatory module.') }}</p>

    @if ($errors->any())
        <div class="rh-auth-error" role="alert"><x-icon name="triangle-exclamation" /> <span>{{ $errors->first() }}</span></div>
    @endif

    <form method="POST" action="{{ route('login') }}" data-native id="loginForm">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('Email address') }}</label>
            <div class="auth-field"><x-icon name="envelope" set="regular" /><input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="username" inputmode="email" placeholder="you@example.com" dir="ltr" required autofocus></div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">{{ __('Password') }}</label>
            <div class="auth-field"><x-icon name="lock" /><input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" dir="ltr" required><button type="button" class="field-eye" data-toggle-password aria-label="{{ __('Show password') }}"><x-icon name="eye" set="regular" /></button></div>
            <div class="rh-auth-caps" id="capsNote" hidden><x-icon name="circle-info" /> {{ __('Caps Lock is on.') }}</div>
        </div>
        <label class="rh-auth-remember" for="remember">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <span>{{ __('Keep me signed in') }}</span>
        </label>
        <button type="submit" class="btn btn-primary btn-lg w-100 rh-auth-submit">{{ __('Sign in') }} <x-icon name="arrow-right" class="ms-1" /></button>
    </form>
    <a class="rh-auth-back" href="{{ route('home') }}" data-native><x-icon name="store" /> {{ __('Browse the property marketplace') }}</a>
</div>
@push('scripts')
<script>
(() => {
 const pass=document.getElementById('password'), note=document.getElementById('capsNote'), form=document.getElementById('loginForm');
 const caps=e=>{ if(e.getModifierState) note.hidden=!e.getModifierState('CapsLock'); };
 pass?.addEventListener('keyup',caps); pass?.addEventListener('keydown',caps); pass?.addEventListener('blur',()=>note.hidden=true);
 // one press is enough: the button waits while the page answers
 form?.addEventListener('submit',()=>{ const b=form.querySelector('.rh-auth-submit'); b.disabled=true; b.classList.add('is-busy'); });
})();
</script>
@endpush
@endsection
