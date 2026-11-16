{{--
    Public pages of the marketplace, in the look of the template: a light top bar with the
    language list and the light / dark switch, then the page.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}" data-bs-theme="light" data-primary="blue" data-icons="{{ asset_v('icons.svg') }}">
<head>
    @include('partials.head')
</head>
<body class="public-body">
<div id="rahnuma-public">
    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-link" href="{{ route('home') }}">
                <x-brand-logo :size="34" />
                <span class="brand-text">{{ config('app.name') }}<small>{{ __('Afghanistan real estate network') }}</small></span>
            </a>
            <nav class="site-nav">
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('Home') }}</a>
                <a class="{{ request()->routeIs('marketplace.properties', 'marketplace.property') ? 'active' : '' }}" href="{{ route('marketplace.properties') }}">{{ __('Properties') }}</a>
                @auth
                    @if (auth()->user()->agency_id)<a href="{{ route('tenant.dashboard') }}">{{ __('Workspace') }}</a>@endif
                    @if (auth()->user()->isPlatformAdmin())<a href="{{ route('platform.dashboard') }}">{{ __('Platform') }}</a>@endif
                    @if (auth()->user()->isGovernment())<a href="{{ route('government.dashboard') }}">{{ __('Regulatory') }}</a>@endif
                @endauth
            </nav>
            <div class="topbar-right">
                @include('partials.language-menu')
                <button class="topbar-icon" type="button" data-theme-toggle title="{{ __('Light / dark mode') }}"><x-icon name="moon" /></button>
                @auth
                    <div class="dropdown">
                        <button class="topbar-user" data-bs-toggle="dropdown" type="button" aria-expanded="false">
                            <span class="avatar avatar-sm avatar-soft-primary">{{ initial(auth()->user()->name) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end user-menu">
                            <li class="user-menu-head"><span class="avatar avatar-md avatar-soft-primary">{{ initial(auth()->user()->name) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div></li>
                            <li><hr class="dropdown-divider"></li>
                            @if (auth()->user()->agency_id)<li><a class="dropdown-item" href="{{ route('tenant.dashboard') }}"><x-icon name="briefcase" /> {{ __('Workspace') }}</a></li>@endif
                            @if (auth()->user()->isPlatformAdmin())<li><a class="dropdown-item" href="{{ route('platform.dashboard') }}"><x-icon name="server" /> {{ __('Platform') }}</a></li>@endif
                            @if (auth()->user()->isGovernment())<li><a class="dropdown-item" href="{{ route('government.dashboard') }}"><x-icon name="landmark" /> {{ __('Regulatory') }}</a></li>@endif
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item text-danger" type="submit" form="logoutForm"><x-icon name="right-from-bracket" /> {{ __('Sign out') }}</button></li>
                        </ul>
                    </div>
                @else
                    <a class="btn btn-primary btn-sm" href="{{ route('login') }}"><x-icon name="right-to-bracket" class="me-1" /> {{ __('Sign in') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <span>© {{ date('Y') }} {{ config('app.name') }} · {{ __('Afghanistan real estate network') }}</span>
        <span class="text-muted">{{ __('by') }} <a href="https://fida.af" target="_blank" rel="noopener">Fida Technologies</a></span>
    </footer>
</div>

@auth
<form id="logoutForm" method="POST" action="{{ route('logout') }}" hidden data-native
      data-confirm="{{ __('Sign out?') }}" data-confirm-text="{{ __('You will need to sign in again to continue.') }}"
      data-confirm-ok="{{ __('Sign out') }}" data-confirm-style="primary">@csrf</form>
@endauth

@include('partials.flash')
@include('partials.scripts')
</body>
</html>
