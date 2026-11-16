{{--
    Public pages of the marketplace, in the formal look of a government service site: a navy
    utility bar with the languages, the header with the brand and the menu, the page, and a
    navy footer. Contact details and an official emblem show only when they are set in .env.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}" data-bs-theme="light" data-primary="blue" data-icons="{{ asset_v('icons.svg') }}">
<head>
    @include('partials.head')
</head>
<body class="public-body">
<div id="rahnuma-public">
    <div class="site-topbar">
        <div class="site-topbar-inner">
            <div class="site-contact">
                @if (config('rahnuma.contact.phone'))<a href="tel:{{ preg_replace('/[^0-9+]/', '', config('rahnuma.contact.phone')) }}" dir="ltr" data-native><x-icon name="phone" /> {{ config('rahnuma.contact.phone') }}</a>@endif
                @if (config('rahnuma.contact.email'))<a href="mailto:{{ config('rahnuma.contact.email') }}" dir="ltr" data-native><x-icon name="envelope" /> {{ config('rahnuma.contact.email') }}</a>@endif
                @if (! config('rahnuma.contact.phone') && ! config('rahnuma.contact.email'))<span>{{ __('Property marketplace and Rahnama offices') }}</span>@endif
            </div>
            <div class="site-tools">
                <nav class="site-langs" aria-label="{{ __('Language') }}">
                    @foreach (config('app.locales') as $code => $language)
                        <a class="{{ app()->getLocale() === $code ? 'active' : '' }}" href="{{ route('locale', $code) }}" lang="{{ $code }}" data-native>{{ $language }}</a>
                    @endforeach
                </nav>
                <button class="site-theme" type="button" data-theme-toggle title="{{ __('Light / dark mode') }}" aria-label="{{ __('Light / dark mode') }}"><x-icon name="moon" /></button>
            </div>
        </div>
    </div>
    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand-link" href="{{ route('home') }}">
                <x-brand-logo :size="44" />
                <span class="brand-text">{{ config('app.name') }}<small>{{ __('Property marketplace and Rahnama offices') }}</small></span>
            </a>
            <nav class="site-nav">
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('Home') }}</a>
                <a class="{{ request()->routeIs('marketplace.properties', 'marketplace.property') ? 'active' : '' }}" href="{{ route('marketplace.properties') }}">{{ __('Properties') }}</a>
                <a class="{{ request()->routeIs('marketplace.agency') ? 'active' : '' }}" href="{{ route('home') }}#rahnamas" data-native>{{ __('Rahnama offices') }}</a>
                <a class="{{ request()->routeIs('verify') ? 'active' : '' }}" href="{{ route('home') }}#verify" data-native>{{ __('Verify a record') }}</a>
                @auth
                    @if (auth()->user()->agency_id)<a href="{{ route('tenant.dashboard') }}">{{ __('Workspace') }}</a>@endif
                    @if (auth()->user()->isPlatformAdmin())<a href="{{ route('platform.dashboard') }}">{{ __('Platform') }}</a>@endif
                    @if (auth()->user()->isGovernment())<a href="{{ route('government.dashboard') }}">{{ __('Regulatory') }}</a>@endif
                @endauth
            </nav>
            <div class="topbar-right">
                @if (config('rahnuma.official_emblem'))<img class="site-emblem" src="{{ asset(config('rahnuma.official_emblem')) }}" alt="{{ __('Official emblem') }}">@endif
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
                    <a class="btn btn-primary" href="{{ route('login') }}"><x-icon name="right-to-bracket" class="me-1" /> {{ __('Sign in') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="site-footer-brand">
                <a class="brand-link" href="{{ route('home') }}"><x-brand-logo :size="40" inverse /><span class="brand-text">{{ config('app.name') }}</span></a>
                <p>{{ __('Property marketplace and Rahnama offices') }}</p>
            </div>
            <div>
                <h3>{{ __('Marketplace') }}</h3>
                <a href="{{ route('marketplace.properties') }}">{{ __('Search properties') }}</a>
                <a href="{{ route('marketplace.properties', ['transaction_type' => 'sale']) }}">{{ deal_label('sale', true) }}</a>
                <a href="{{ route('marketplace.properties', ['transaction_type' => 'rent']) }}">{{ deal_label('rent', true) }}</a>
                <a href="{{ route('marketplace.properties', ['transaction_type' => 'bay_wafa']) }}">{{ deal_label('bay_wafa', true) }}</a>
            </div>
            <div>
                <h3>{{ __('For offices and authorities') }}</h3>
                <a href="{{ route('login') }}">{{ __('Rahnama office workspace') }}</a>
                <a href="{{ route('login') }}">{{ __('Regulatory workspace') }}</a>
                <a href="{{ route('home') }}#verify" data-native>{{ __('Verify a transaction record') }}</a>
            </div>
            @if (array_filter((array) config('rahnuma.contact')))
            <div>
                <h3>{{ __('Contact') }}</h3>
                @if (config('rahnuma.contact.address'))<span><x-icon name="location-dot" /> {{ config('rahnuma.contact.address') }}</span>@endif
                @if (config('rahnuma.contact.phone'))<span dir="ltr"><x-icon name="phone" /> {{ config('rahnuma.contact.phone') }}</span>@endif
                @if (config('rahnuma.contact.email'))<span dir="ltr"><x-icon name="envelope" /> {{ config('rahnuma.contact.email') }}</span>@endif
                @if (config('rahnuma.contact.hours'))<span><x-icon name="clock" /> {{ config('rahnuma.contact.hours') }}</span>@endif
            </div>
            @endif
        </div>
        <div class="site-footer-bottom">
            <div>
                <span>© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</span>
                <span>{{ __('by') }} <a href="https://fida.af" target="_blank" rel="noopener">Fida Technologies</a></span>
            </div>
        </div>
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
