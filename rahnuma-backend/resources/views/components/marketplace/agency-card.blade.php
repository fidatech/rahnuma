@props(['agency'])
<article class="market-agency-card">
    <div class="market-agency-card__cover" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif></div>
    <div class="market-agency-card__body">
        <div class="market-agency-card__logo">@if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}">@else{{ strtoupper(substr($agency->name,0,1)) }}@endif</div>
        <div class="market-agency-card__status">{{ $agency->is_verified?'✓ Verified':'Rahnama' }}</div>
        <h3>{{ $agency->name }}</h3><p>{{ $agency->tagline ?: 'Local property guidance and listings.' }}</p>
        <div class="market-agency-card__facts"><span><b>{{ number_format($agency->public_properties_count ?? 0) }}</b> listings</span>@if($agency->province)<span>{{ $agency->province->name_en }}</span>@endif</div>
        <a href="{{ route('marketplace.agency',$agency) }}">View Rahnama profile <span>→</span></a>
    </div>
</article>
