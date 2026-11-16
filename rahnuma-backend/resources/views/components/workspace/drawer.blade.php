@props(['id','title','kicker'=>null,'description'=>null,'wide'=>false,'icon'=>null])
<div class="smart-modal smart-sheet" id="{{ $id }}" aria-hidden="true">
    <div class="smart-sheet__panel {{ $wide ? 'smart-sheet__panel--wide' : '' }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="smart-sheet__accent" aria-hidden="true"></div>
        <div class="smart-sheet__header">
            <div class="smart-sheet__heading">
                <div class="smart-sheet__icon" aria-hidden="true">{!! $icon ?: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>' !!}</div>
                <div>
                    @if($kicker)<div class="sheet-kicker">{{ $kicker }}</div>@endif
                    <h2 id="{{ $id }}-title">{{ $title }}</h2>
                    @if($description)<p>{{ $description }}</p>@endif
                </div>
            </div>
            <button type="button" class="sheet-close" data-modal-close aria-label="Close">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
