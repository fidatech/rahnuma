@extends('layouts.workspace')
@section('workspace')
<div class="smart-page-head">
    <div>
        <div class="eyebrow">Inventory intelligence</div>
        <h1>Property Inventory</h1>
        <p>One place to manage availability, owners, marketplace exposure and deal readiness.</p>
    </div>
    <a class="btn" href="{{ route('tenant.properties.create') }}">Add property</a>
</div>

<div class="pulse-strip pulse-strip--4">
    <div class="pulse-item"><span>Total inventory</span><strong>{{ number_format($stats['total']) }}</strong><small>All property records</small></div>
    <div class="pulse-item"><span>Available now</span><strong>{{ number_format($stats['available']) }}</strong><small>Ready for customers</small></div>
    <div class="pulse-item"><span>Marketplace</span><strong>{{ number_format($stats['public']) }}</strong><small>Public / featured</small></div>
    <div class="pulse-item {{ $stats['unowned']?'pulse-item--warm':'' }}"><span>Missing owner</span><strong>{{ number_format($stats['unowned']) }}</strong><small>Needs relationship data</small></div>
</div>

<div class="inventory-shell">
    @php($activeFilterCount=collect([request('transaction_type'),request('status'),request('visibility')])->filter(fn($v)=>filled($v))->count())
    <form class="filter-command" method="get" data-filter-form>
        <div class="filter-search">
            <span class="filter-search__icon"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4" stroke-linecap="round"></path></svg></span>
            <input name="q" value="{{ request('q') }}" placeholder="Search title, code, owner, address or neighborhood" data-filter-search autocomplete="off">
            <button class="filter-search__submit" type="submit" aria-label="Search"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4" stroke-linecap="round"></path></svg></button>
        </div>
        <details class="filter-menu" data-filter-menu>
            <summary class="filter-menu__trigger"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4" stroke-linecap="round"></path></svg><span>Filters</span>@if($activeFilterCount)<span class="filter-count">{{ $activeFilterCount }}</span>@endif<svg class="filter-menu__chevron" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8 10 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path></svg></summary>
            <div class="filter-menu__panel">
                <div class="filter-menu__head"><div><small>Property inventory</small><strong>Refine what you see</strong></div><span>{{ $activeFilterCount }} active</span></div>
                <div class="filter-menu__grid">
                    <label class="filter-field"><span>Deal type</span><select name="transaction_type"><option value="">Any deal type</option><option value="sale" @selected(request('transaction_type')==='sale')>Sale</option><option value="rent" @selected(request('transaction_type')==='rent')>Rent</option><option value="bay_wafa" @selected(request('transaction_type')==='bay_wafa')>Mortgage / بیع وفا</option></select></label>
                    <label class="filter-field"><span>Property status</span><select name="status"><option value="">Any status</option>@foreach(['available','active','rented','sold','mortgaged','inactive'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
                    <label class="filter-field"><span>Visibility</span><select name="visibility"><option value="">Any visibility</option>@foreach(['private','link','public','featured'] as $s)<option value="{{ $s }}" @selected(request('visibility')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
                </div>
                <div class="filter-menu__footer"><a href="{{ route('tenant.properties.index') }}" class="filter-reset">Reset all</a><button class="btn sm">Apply filters</button></div>
            </div>
        </details>
        <div class="filter-command__meta"><b>{{ number_format($properties->total()) }}</b><span>properties</span></div>
    </form>
    @if(request()->hasAny(['q','transaction_type','status','visibility']))
    <div class="active-filter-row">
        <span class="active-filter-row__label">Active</span>
        @if(request('q'))<button type="button" class="filter-chip" data-filter-clear="q">Search: {{ \Illuminate\Support\Str::limit(request('q'),24) }}<span>×</span></button>@endif
        @if(request('transaction_type'))<button type="button" class="filter-chip" data-filter-clear="transaction_type">{{ request('transaction_type')==='bay_wafa'?'Mortgage':ucfirst(request('transaction_type')) }}<span>×</span></button>@endif
        @if(request('status'))<button type="button" class="filter-chip" data-filter-clear="status">{{ ucfirst(request('status')) }}<span>×</span></button>@endif
        @if(request('visibility'))<button type="button" class="filter-chip" data-filter-clear="visibility">{{ ucfirst(request('visibility')) }}<span>×</span></button>@endif
        <a href="{{ route('tenant.properties.index') }}" class="active-filter-row__reset">Clear all</a>
    </div>
    @endif

    <div class="property-board">
        @forelse($properties as $p)
            @php($cover=$p->coverMedia)
            <article class="inventory-card">
                <a href="{{ route('tenant.properties.edit',$p) }}" class="inventory-card__media" data-quick-view="{{ route('tenant.properties.quick',$p) }}" data-quick-view-title="Property quick view">
                    @if($cover)
                        <img src="{{ asset('storage/'.($cover->thumbnail_path ?: $cover->path)) }}" alt="{{ $p->title }}" loading="lazy" decoding="async">
                    @else
                        <div class="inventory-card__placeholder"><span>{{ strtoupper(substr($p->property_type ?: 'P',0,1)) }}</span><small>No property photo</small></div>
                    @endif
                    <span class="inventory-card__status {{ in_array($p->status,['available','active'])?'is-live':'' }}">{{ $p->status }}</span>
                    <span class="inventory-card__visibility">{{ $p->visibility }}</span>
                </a>
                <div class="inventory-card__body">
                    <div class="inventory-card__topline"><span>{{ $p->code }}</span><span>{{ $p->transaction_type==='bay_wafa'?'Mortgage':ucfirst($p->transaction_type) }}</span></div>
                    <h3><a href="{{ route('tenant.properties.edit',$p) }}">{{ $p->title }}</a></h3>
                    <p class="inventory-card__location">{{ $p->address ?: ($p->province?->name_en ?: 'Location not completed') }}</p>
                    <div class="inventory-card__price">{{ number_format((float)($p->transaction_type==='rent'?$p->monthly_rent:$p->price)) }} <small>{{ $p->currency }}{{ $p->transaction_type==='rent'?' / month':'' }}</small></div>
                    <div class="inventory-card__facts">
                        <span><b>{{ $p->bedrooms ?? '—' }}</b> beds</span>
                        <span><b>{{ $p->bathrooms ?? '—' }}</b> baths</span>
                        <span><b>{{ $p->total_area ?? '—' }}</b> {{ strtoupper($p->area_unit ?: 'sqm') }}</span>
                    </div>
                    <div class="inventory-card__owner">
                        <div class="mini-avatar">{{ strtoupper(mb_substr($p->owner?->name ?: '?',0,1)) }}</div>
                        <div><span>Primary owner</span><b>{{ $p->owner?->name ?: 'Not assigned' }}</b></div>
                    </div>
                </div>
                <div class="inventory-card__actions">
                    <button type="button" class="link-button" data-quick-view="{{ route('tenant.properties.quick',$p) }}" data-quick-view-title="Property quick view">Quick view</button><a href="{{ route('tenant.properties.edit',$p) }}">Edit property</a>
                    <a href="{{ route('tenant.properties.matches',$p) }}">Customer matches</a>
                </div>
            </article>
        @empty
            <div class="property-board__empty"><x-workspace.empty title="Your inventory is empty" description="Register the first property and link it to its owner. It can stay private or be published to the marketplace." /></div>
        @endforelse
    </div>
    <div class="pagination collection-pagination">{{ $properties->links() }}</div>
</div>
@endsection
