<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(),['fa','ps'])?'rtl':'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101827">
    <title>@yield('title','Rahnuma')</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('assets/rahnuma.css') }}?v={{ @filemtime(public_path('assets/rahnuma.css')) ?: time() }}">
</head>
<body class="@yield('body-class')">
<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="/">
            <span class="brand__mark">R</span>
            <span class="brand__copy">RAHNUMA<small>AFGHANISTAN REAL ESTATE NETWORK</small></span>
        </a>
        <nav class="site-nav">
            <a href="{{ route('marketplace.properties') }}">Properties</a>
            @auth
                @if(auth()->user()->agency_id)<a href="{{ route('tenant.dashboard') }}">Workspace</a>@endif
                @if(auth()->user()->isPlatformAdmin())<a href="{{ route('platform.dashboard') }}">Platform</a>@endif
                @if(auth()->user()->isGovernment())<a href="{{ route('government.dashboard') }}">Regulatory</a>@endif
            @endauth
        </nav>
        <div class="site-actions">
            <div class="locale-switch">
                <a href="{{ route('locale','fa') }}">دری</a><a href="{{ route('locale','ps') }}">پښتو</a><a href="{{ route('locale','en') }}">EN</a>
            </div>
            @auth
                <div class="user-chip"><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span></div>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="btn ghost sm" style="color:#d0d5dd">Logout</button></form>
            @else
                <a class="btn sm" href="{{ route('login') }}">Sign in</a>
            @endauth
        </div>
    </div>
</header>
<main class="page-shell" id="rahnuma-page">
    <div id="rahnuma-flashes">
        @if(session('success'))<div class="flash">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="error">{{ implode(' · ',$errors->all()) }}</div>@endif
    </div>
    @yield('content')
</main>
<div id="rahnuma-modals">@stack('modals')</div>
<div id="rahnuma-page-scripts" hidden>@stack('scripts')</div>
<div class="spa-progress" id="rahnuma-spa-progress" aria-hidden="true"><span></span></div>
<script defer src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script defer src="{{ asset('assets/rahnuma-smart.js') }}?v={{ @filemtime(public_path('assets/rahnuma-smart.js')) ?: time() }}"></script>
<script defer src="{{ asset('assets/rahnuma-spa.js') }}?v={{ @filemtime(public_path('assets/rahnuma-spa.js')) ?: time() }}"></script>
</body>
</html>
