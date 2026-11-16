{{-- Language list of the top bar. A change of language is a full page load: the style sheet changes direction. --}}
<div class="dropdown">
    <button class="topbar-icon topbar-lang" data-bs-toggle="dropdown" type="button" aria-expanded="false" title="{{ __('Language') }}" aria-label="{{ __('Language') }}"><x-icon name="globe" /><span>{{ strtoupper(app()->getLocale()) }}</span></button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li class="dropdown-header">{{ __('Language') }}</li>
        @foreach (config('app.locales') as $code => $language)
            <li><a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}" href="{{ route('locale', $code) }}" lang="{{ $code }}" data-native>{{ $language }}</a></li>
        @endforeach
    </ul>
</div>
