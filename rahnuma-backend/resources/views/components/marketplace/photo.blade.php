{{--
    A property photo in the Rahnuma frame: a fine gold line inside the edge and the brand tab in
    the corner, with the property code when one is given. The frame is drawn over the picture by
    the page, so the stored file stays clean and every photo, old or new, gets the same frame.
--}}
@props(['src', 'alt' => '', 'code' => null, 'priority' => false, 'brand' => true])
<span {{ $attributes->class('rh-frame') }}>
    <img src="{{ $src }}" alt="{{ $alt }}" decoding="async" @if ($priority) fetchpriority="high" @else loading="lazy" @endif>
    <span class="rh-frame-line" aria-hidden="true"></span>
    @if ($brand)<span class="rh-frame-brand" aria-hidden="true"><x-brand-logo :size="16" inverse /><b>{{ config('app.name') }}</b>@if ($code)<i dir="ltr">{{ $code }}</i>@endif</span>@endif
    {{ $slot }}
</span>
