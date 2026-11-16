@php
    $user = auth()->user();
    $pageTitle = trim($__env->yieldContent('title'));
@endphp
<header class="app-topbar" id="appTopbar">
    <button class="btn-icon btn-ghost topbar-toggle" id="sidebarToggle" type="button" aria-label="{{ __('Show or hide the menu') }}"><x-icon name="bars-staggered" /></button>
    <button class="appbar-back" type="button" id="appBack" aria-label="{{ __('Back') }}"><x-icon name="chevron-left" /></button>
    <div class="appbar-title" id="appbarTitle">{{ $pageTitle !== '' ? $pageTitle : config('app.name') }}</div>

    <button class="topbar-search" type="button" data-bs-toggle="modal" data-bs-target="#searchModal">
        <x-icon name="magnifying-glass" /><span>{{ __('Search pages…') }}</span><span class="kbd-group"><kbd>Ctrl</kbd><kbd>K</kbd></span>
    </button>

    <div class="topbar-brand" aria-hidden="true">{{ $shell === 'workspace' ? ($user->agency?->name ?: config('app.name')) : config('app.name') }}</div>

    <div class="topbar-right">
        @include('partials.language-menu')

        <button class="topbar-icon" type="button" data-theme-toggle title="{{ __('Light / dark mode') }}"><x-icon name="moon" /></button>

        <div class="dropdown">
            <button class="topbar-user" data-bs-toggle="dropdown" type="button" aria-expanded="false">
                <span class="avatar avatar-sm avatar-soft-primary">@if ($user->avatar_path)<img src="{{ asset('storage/'.$user->avatar_path) }}" alt="">@else{{ initial($user->name) }}@endif</span>
                <span class="topbar-user-meta"><strong>{{ $user->name }}</strong><small>{{ $user->job_title ?: enum_label($user->role) }}</small></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end user-menu">
                <li class="user-menu-head"><span class="avatar avatar-md avatar-soft-primary">{{ initial($user->name) }}</span><div><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div></li>
                <li><hr class="dropdown-divider"></li>
                @if ($user->agency_id)
                    <li><a class="dropdown-item" href="{{ route('tenant.dashboard') }}" @if ($shell !== 'workspace') data-native @endif><x-icon name="briefcase" /> {{ __('Workspace') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('tenant.settings.edit') }}#profile" @if ($shell !== 'workspace') data-native @endif><x-icon name="user" /> {{ __('My Profile') }}</a></li>
                    <li><a class="dropdown-item" href="{{ route('tenant.settings.edit') }}#security" @if ($shell !== 'workspace') data-native @endif><x-icon name="key" /> {{ __('Change Password') }}</a></li>
                @endif
                @if ($user->isPlatformAdmin())
                    <li><a class="dropdown-item" href="{{ route('platform.dashboard') }}" @if ($shell !== 'platform') data-native @endif><x-icon name="server" /> {{ __('Platform') }}</a></li>
                @endif
                @if ($user->isGovernment())
                    <li><a class="dropdown-item" href="{{ route('government.dashboard') }}" @if ($shell !== 'government') data-native @endif><x-icon name="landmark" /> {{ __('Regulatory') }}</a></li>
                @endif
                <li><a class="dropdown-item" href="{{ route('home') }}"><x-icon name="store" /> {{ __('Property marketplace') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item text-danger" type="submit" form="logoutForm"><x-icon name="right-from-bracket" /> {{ __('Sign out') }}</button></li>
            </ul>
        </div>
    </div>
</header>
