@extends('layouts.workspace')
@section('workspace')
<div class="smart-page-head">
    <div><div class="eyebrow">Deal intelligence</div><h1>Transactions</h1><p>Every sale, rent and Mortgage / بیع وفا in one clean deal history with Setta and commission context.</p></div>
    <a class="btn" href="{{ route('tenant.transactions.create') }}">New deal</a>
</div>

<div class="pulse-strip pulse-strip--4">
    <div class="pulse-item"><span>All deals</span><strong>{{ number_format($stats['total']) }}</strong><small>Permanent history</small></div>
    <div class="pulse-item"><span>Sales</span><strong>{{ number_format($stats['sale']) }}</strong><small>Ownership transfers</small></div>
    <div class="pulse-item"><span>Rentals</span><strong>{{ number_format($stats['rent']) }}</strong><small>Tenancy agreements</small></div>
    <div class="pulse-item"><span>Mortgage</span><strong>{{ number_format($stats['mortgage']) }}</strong><small>بیع وفا / بیع جایزی</small></div>
</div>

<div class="collection-shell">
    @php($activeFilterCount=collect([request('transaction_type'),request('status')])->filter(fn($v)=>filled($v))->count())
    <form class="filter-command" method="get" data-filter-form>
        <div class="filter-search"><span class="filter-search__icon"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4" stroke-linecap="round"></path></svg></span><input name="q" value="{{ request('q') }}" placeholder="Search reference, Setta, property title or code" data-filter-search autocomplete="off"><button class="filter-search__submit" type="submit" aria-label="Search"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4" stroke-linecap="round"></path></svg></button></div>
        <details class="filter-menu" data-filter-menu><summary class="filter-menu__trigger"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4" stroke-linecap="round"></path></svg><span>Filters</span>@if($activeFilterCount)<span class="filter-count">{{ $activeFilterCount }}</span>@endif<svg class="filter-menu__chevron" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8 10 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path></svg></summary><div class="filter-menu__panel"><div class="filter-menu__head"><div><small>Deal history</small><strong>Refine transactions</strong></div><span>{{ $activeFilterCount }} active</span></div><div class="filter-menu__grid filter-menu__grid--2"><label class="filter-field"><span>Deal type</span><select name="transaction_type"><option value="">Any deal type</option><option value="sale" @selected(request('transaction_type')==='sale')>Sale</option><option value="rent" @selected(request('transaction_type')==='rent')>Rent</option><option value="bay_wafa" @selected(request('transaction_type')==='bay_wafa')>Mortgage / بیع وفا</option></select></label><label class="filter-field"><span>Transaction status</span><select name="status"><option value="">Any status</option><option value="completed" @selected(request('status')==='completed')>Completed</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option></select></label></div><div class="filter-menu__footer"><a href="{{ route('tenant.transactions.index') }}" class="filter-reset">Reset all</a><button class="btn sm">Apply filters</button></div></div></details>
        <div class="filter-command__meta"><b>{{ number_format($transactions->total()) }}</b><span>deals</span></div>
    </form>
    @if(request()->hasAny(['q','transaction_type','status']))<div class="active-filter-row"><span class="active-filter-row__label">Active</span>@if(request('q'))<button type="button" class="filter-chip" data-filter-clear="q">Search: {{ \Illuminate\Support\Str::limit(request('q'),24) }}<span>×</span></button>@endif @if(request('transaction_type'))<button type="button" class="filter-chip" data-filter-clear="transaction_type">{{ request('transaction_type')==='bay_wafa'?'Mortgage':ucfirst(request('transaction_type')) }}<span>×</span></button>@endif @if(request('status'))<button type="button" class="filter-chip" data-filter-clear="status">{{ ucfirst(request('status')) }}<span>×</span></button>@endif <a href="{{ route('tenant.transactions.index') }}" class="active-filter-row__reset">Clear all</a></div>@endif

    <div class="deal-register">
        @forelse($transactions as $t)
        <a class="deal-row" href="{{ route('tenant.transactions.show',$t) }}" data-quick-view="{{ route('tenant.transactions.quick',$t) }}" data-quick-view-title="Deal quick view">
            <div class="deal-row__type deal-row__type--{{ $t->transaction_type }}">{{ $t->transaction_type==='sale'?'S':($t->transaction_type==='rent'?'R':'M') }}</div>
            <div class="deal-row__main"><div><strong>{{ $t->property?->title ?: 'Property unavailable' }}</strong><span>{{ $t->reference_no }} · {{ $t->property?->code ?: 'No property code' }}</span></div></div>
            <div class="register-fact"><span>Deal</span><b>{{ $t->transaction_type==='bay_wafa'?'Mortgage':ucfirst($t->transaction_type) }}</b></div>
            <div class="register-fact"><span>Setta</span><b>{{ $t->setta_number ?: 'Not entered' }}</b></div>
            <div class="register-fact register-fact--money"><span>Value</span><b>{{ number_format((float)($t->amount ?: $t->monthly_rent)) }} {{ $t->currency }}</b></div>
            <div class="register-fact"><span>Commission</span><b>{{ number_format((float)$t->commission_total) }} {{ $t->currency }}</b></div>
            <div class="deal-row__end"><span class="badge {{ $t->status==='completed'?'success':'neutral' }}">{{ $t->status }}</span><small>{{ $t->completed_at?->format('d M Y') ?: $t->created_at?->format('d M Y') }}</small></div>
        </a>
        @empty
        <x-workspace.empty title="No transactions yet" description="When a customer and property are ready, start a deal. Rahnuma will keep the owner, commission and verification links together." />
        @endforelse
    </div>
    <div class="pagination collection-pagination">{{ $transactions->links() }}</div>
</div>
@endsection
