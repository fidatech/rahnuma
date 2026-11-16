{{--
    The panel of the template: sidebar, top bar, content, and the tab bar of phones.
    Used by the Rahnama workspace, the platform administration and the regulatory workspace;
    the sidebar shows the menu of the panel the address belongs to.

    @extends('layouts.workspace')  @section('title', __('...'))  @section('workspace') ... @endsection
--}}
@php
    $shell = request()->routeIs('platform.*') ? 'platform' : (request()->routeIs('government.*') ? 'government' : 'workspace');
    $home = route(['workspace' => 'tenant.dashboard', 'platform' => 'platform.dashboard', 'government' => 'government.dashboard'][$shell]);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}" data-bs-theme="light" data-sidebar="light" data-layout="vertical" data-primary="blue" data-width="fluid" data-icons="{{ asset_v('icons.svg') }}" data-home="{{ $home }}">
<head>
    @include('partials.head')
</head>
<body>
<div class="app-wrapper" id="rahnuma-shell" data-shell="{{ $shell }}">
    @include('partials.sidebar')
    <div class="app-main">
        @include('partials.topbar')
        <main class="app-content">
            <div class="content-container" id="rahnuma-content">
                @if ($shell === 'government' && ! config('rahnuma.government_mode'))
                    <div class="alert alert-warning d-flex gap-2 align-items-start"><x-icon name="triangle-exclamation" class="mt-1" /><div><strong>{{ __('Private demonstration mode.') }}</strong> {{ __('This regulatory workspace is not represented as Ministry-approved or official.') }}</div></div>
                @endif
                @if (session('impersonator_id'))
                    <form class="rh-impersonate" method="post" action="{{ route('impersonate.stop') }}" data-native>@csrf
                        <span><x-icon name="user-shield" /> {{ __('You are signed in as :name. What you do here is done in their name.', ['name' => auth()->user()->name]) }}</span>
                        <button class="btn btn-sm btn-gold">{{ __('Back to administrator') }}</button>
                    </form>
                @endif
                @yield('workspace')
            </div>
        </main>
        <footer class="app-footer">
            <span>© {{ date('Y') }} {{ config('app.name') }}</span>
            <nav><span class="text-muted">{{ __('by') }} <a href="https://fida.af" target="_blank" rel="noopener">Fida Technologies</a></span></nav>
        </footer>
    </div>
</div>

<nav class="app-tabbar" aria-label="{{ __('Menu') }}">
    <a href="{{ $home }}" class="tab-item {{ request()->url() === $home ? 'active' : '' }}"><x-icon name="house" /><em>{{ __('Home') }}</em></a>
    @if ($shell === 'workspace')
        <a href="{{ route('tenant.properties.index') }}" class="tab-item {{ request()->routeIs('tenant.properties.*') ? 'active' : '' }}"><x-icon name="building" /><em>{{ __('Properties') }}</em></a>
        <a href="{{ route('tenant.transactions.index') }}" class="tab-item {{ request()->routeIs('tenant.transactions.*') ? 'active' : '' }}"><x-icon name="handshake" /><em>{{ __('Deals') }}</em></a>
    @endif
    <button type="button" class="tab-item" data-bs-toggle="modal" data-bs-target="#searchModal"><x-icon name="magnifying-glass" /><em>{{ __('Search') }}</em></button>
    <button type="button" class="tab-item" id="tabMenu"><x-icon name="grip" /><em>{{ __('Menu') }}</em></button>
</nav>

<div class="modal fade" id="searchModal" tabindex="-1" aria-label="{{ __('Search') }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable search-dialog">
        <div class="modal-content">
            <div class="search-input"><x-icon name="magnifying-glass" /><input type="text" id="searchInput" placeholder="{{ __('Search pages…') }}" autocomplete="off"><kbd>Esc</kbd></div>
            <div class="modal-body p-2" id="searchResults"></div>
        </div>
    </div>
</div>

<form id="logoutForm" method="POST" action="{{ route('logout') }}" hidden data-native
      data-confirm="{{ __('Sign out?') }}" data-confirm-text="{{ __('You will need to sign in again to continue.') }}"
      data-confirm-ok="{{ __('Sign out') }}" data-confirm-style="primary">@csrf</form>

@include('partials.flash')
@include('partials.scripts')
</body>
</html>
