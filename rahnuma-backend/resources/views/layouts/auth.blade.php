{{--
    Sign-in page in the formal look of the public site: a navy panel with the brand and who the
    system is for, and the form on a clean side with the languages and the light / dark switch.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}" data-bs-theme="light" data-primary="blue" data-icons="{{ asset_v('icons.svg') }}">
<head>
    @include('partials.head')
</head>
<body class="auth-body">
<div class="rh-auth">
    <aside class="rh-auth-side">
        <a href="{{ url('/') }}" class="rh-auth-brand" data-native><x-brand-logo :size="46" inverse /><span>{{ config('app.name') }}<small>{{ __('Property marketplace and Rahnama offices') }}</small></span></a>
        <div class="rh-auth-pitch">
            <span class="rh-auth-arch" aria-hidden="true"><x-icon name="landmark" /></span>
            <h1>{{ __('Your Rahnama office, in one system.') }}</h1>
            <p>{{ __('Properties, owners, customers, deals and accounts of every office, published to the Afghanistan property marketplace without duplicate work.') }}</p>
        </div>
        <ul class="rh-auth-roles">
            <li><span><x-icon name="briefcase" /></span><div><strong>{{ __('Rahnama office workspace') }}</strong><small>{{ __('Manage properties, customers and deals, and publish listings.') }}</small></div></li>
            <li><span><x-icon name="landmark" /></span><div><strong>{{ __('Regulatory workspace') }}</strong><small>{{ __('Oversight and reporting for the responsible authorities.') }}</small></div></li>
            <li><span><x-icon name="server" /></span><div><strong>{{ __('Platform administration') }}</strong><small>{{ __('Offices, plans and marketplace promotion.') }}</small></div></li>
        </ul>
    </aside>
    <main class="rh-auth-main">
        <div class="rh-auth-top">
            <a href="{{ url('/') }}" class="rh-auth-brand is-mobile" data-native><x-brand-logo :size="38" /><span>{{ config('app.name') }}</span></a>
            <nav class="rh-auth-langs" aria-label="{{ __('Language') }}">
                @foreach (config('app.locales') as $code => $language)
                    <a href="{{ route('locale', $code) }}" lang="{{ $code }}" class="{{ app()->getLocale() === $code ? 'active' : '' }}" data-native>{{ $language }}</a>
                @endforeach
            </nav>
            <button class="topbar-icon" type="button" data-theme-toggle title="{{ __('Light / dark mode') }}" aria-label="{{ __('Light / dark mode') }}"><x-icon name="moon" /></button>
        </div>
        <div class="rh-auth-center">@yield('content')</div>
        <footer class="rh-auth-foot">© {{ date('Y') }} {{ config('app.name') }} · {{ __('by') }} <a href="https://fida.af" target="_blank" rel="noopener">Fida Technologies</a></footer>
    </main>
</div>
@include('partials.flash')
@include('partials.scripts')
</body>
</html>
