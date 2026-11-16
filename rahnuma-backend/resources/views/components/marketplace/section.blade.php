{{-- A titled part of a marketplace page: kicker, title, text and one link, then the slot. --}}
@props(['kicker' => null, 'title', 'text' => null, 'href' => null, 'link' => null])
<section {{ $attributes->class('rh-section') }}>
    <div class="rh-section-head">
        <div>
            @if ($kicker)<span class="rh-kicker">{{ $kicker }}</span>@endif
            <h2>{{ $title }}</h2>
            @if ($text)<p>{{ $text }}</p>@endif
        </div>
        @if ($href)<a class="btn btn-sm btn-outline-soft" href="{{ $href }}">{{ $link }} <x-icon name="arrow-right" class="ms-1" /></a>@endif
    </div>
    {{ $slot }}
</section>
