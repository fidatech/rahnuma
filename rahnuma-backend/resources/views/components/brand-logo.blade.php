{{-- The Rahnuma mark: a house on the gradient tile of the template. --}}
@props(['size' => 32, 'inverse' => false])
@php
    $gradient = 'lg'.\Illuminate\Support\Str::random(6);
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
@if ($inverse)
<rect width="40" height="40" rx="11" fill="#fff"/><path d="M20 9.5 8.5 19h3.2v11.5h6.2V23h4.2v7.5h6.2V19h3.2L20 9.5z" style="fill:var(--primary)"/>
@else
<rect width="40" height="40" rx="11" fill="url(#{{ $gradient }})"/><path d="M20 9.5 8.5 19h3.2v11.5h6.2V23h4.2v7.5h6.2V19h3.2L20 9.5z" fill="#fff"/>
<defs><linearGradient id="{{ $gradient }}" x1="0" y1="0" x2="40" y2="40"><stop style="stop-color:var(--brand-from,#4a8df8)"/><stop offset="1" style="stop-color:var(--brand-to,#5b45e0)"/></linearGradient></defs>
@endif
</svg>
