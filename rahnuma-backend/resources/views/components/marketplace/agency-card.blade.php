@props(['agency'])
<article class="card card-hover h-100 mb-0 overflow-hidden">
    <div class="profile-cover rh-cover-sm" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif></div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <span class="avatar avatar-lg rounded-3 avatar-soft-primary rh-cover-logo">@if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}" alt="" loading="lazy">@else{{ initial($agency->name) }}@endif</span>
            <span class="badge-soft {{ $agency->is_verified ? 'success' : 'secondary' }}">@if($agency->is_verified)<x-icon name="circle-check" /> {{ __('Verified') }}@else{{ __('Rahnama') }}@endif</span>
        </div>
        <h3 class="h6 mt-2 mb-1">{{ $agency->name }}</h3>
        <p class="small text-muted">{{ $agency->tagline ?: __('Local property guidance and listings.') }}</p>
        <div class="d-flex flex-wrap gap-2 small text-muted mb-3"><span class="badge-count">{{ __(':count listings', ['count' => number_format($agency->public_properties_count ?? 0)]) }}</span>@if($agency->province)<span class="badge-count">{{ loc_name($agency->province) }}</span>@endif</div>
        <a class="btn btn-sm btn-soft-primary w-100" href="{{ route('marketplace.agency',$agency) }}">{{ __('View Rahnama profile') }}</a>
    </div>
</article>
