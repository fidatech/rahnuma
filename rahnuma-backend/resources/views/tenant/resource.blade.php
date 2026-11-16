@extends('layouts.workspace')
@section('workspace')
@php
$meta = [
 'owners'=>['kicker'=>'Property relationships','title'=>'Owners & Landlords','subtitle'=>'People behind your inventory, ownership records and landlord relationships.','create'=>'Add owner','search'=>'Search name, phone, Tazkira or address','noun'=>'owner'],
 'customers'=>['kicker'=>'Customer intelligence','title'=>'Customers','subtitle'=>'Buyers, tenants and prospects with a clean history of every interaction.','create'=>'Add customer','search'=>'Search name, phone, source or address','noun'=>'customer'],
 'leads'=>['kicker'=>'Sales pipeline','title'=>'Lead Pipeline','subtitle'=>'Move every inquiry from first contact to visit, negotiation and closed deal.','create'=>'Capture lead','search'=>'Search source, status or notes','noun'=>'lead'],
 'visits'=>['kicker'=>'Field operations','title'=>'Visits & Follow-ups','subtitle'=>'A clear agenda of property visits, outcomes and next actions.','create'=>'Schedule visit','search'=>'Search visit status, outcome or notes','noun'=>'visit'],
 'payments'=>['kicker'=>'Money movement','title'=>'Payments','subtitle'=>'Track commission, rent, deposits and other money movements connected to real deals.','create'=>'Record payment','search'=>'Search reference, category, method or notes','noun'=>'payment'],
 'expenses'=>['kicker'=>'Office spending','title'=>'Expenses','subtitle'=>'Record operating costs without turning daily bookkeeping into accounting work.','create'=>'Add expense','search'=>'Search category, reference or description','noun'=>'expense'],
][$resource];
$relationMeta=['customer_id'=>['resource'=>'customer','modal'=>'quick-customer-modal','label'=>'New customer'],'owner_id'=>['resource'=>'owner','modal'=>'quick-owner-modal','label'=>'New owner'],'property_id'=>['resource'=>'property','modal'=>'quick-property-modal','label'=>'New property']];
$labels=['customer_id'=>'Customer','owner_id'=>'Owner / landlord','property_id'=>'Property','transaction_id'=>'Transaction','assigned_user_id'=>'Assigned agent','next_follow_up_at'=>'Next follow-up','scheduled_at'=>'Visit date & time','exchange_rate_to_afn'=>'Exchange rate to AFN','reference_no'=>'Reference','paid_at'=>'Payment date & time','expense_date'=>'Expense date','tazkira_no'=>'Tazkira number'];
@endphp

<div class="smart-page-head">
    <div>
        <div class="eyebrow">{{ $meta['kicker'] }}</div>
        <h1>{{ $meta['title'] }}</h1>
        <p>{{ $meta['subtitle'] }}</p>
    </div>
    <button class="btn" type="button" data-modal-open="resource-create-drawer">{{ $meta['create'] }}</button>
</div>

<div class="pulse-strip">
    <div class="pulse-item"><span>Total records</span><strong>{{ number_format($stats['total']) }}</strong><small>All-time</small></div>
    <div class="pulse-item"><span>Added this month</span><strong>{{ number_format($stats['month']) }}</strong><small>{{ now()->format('F') }}</small></div>
    @if($stats['attention'] !== null)
        <div class="pulse-item pulse-item--warm"><span>{{ $resource==='owners'?'Without property':'Needs attention' }}</span><strong>{{ number_format($stats['attention']) }}</strong><small>{{ $resource==='leads'?'Open pipeline':($resource==='visits'?'Scheduled':'Relationship gap') }}</small></div>
        <div class="pulse-item"><span>{{ $resource==='owners'?'With property':'Completed' }}</span><strong>{{ number_format($stats['done']) }}</strong><small>{{ $resource==='leads'?'Won':($resource==='visits'?'Visits done':'Linked inventory') }}</small></div>
    @else
        <div class="pulse-item pulse-item--quiet"><span>Workspace</span><strong>Live</strong><small>Tenant-isolated records</small></div>
    @endif
</div>

<div class="collection-shell">
    @php($hasStatusFilter=in_array($resource,['leads','visits']))
    @php($activeFilterCount=$hasStatusFilter && filled(request('status')) ? 1 : 0)
    <form method="get" class="filter-command" data-filter-form>
        <div class="filter-search"><span class="filter-search__icon"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4" stroke-linecap="round"></path></svg></span><input name="q" value="{{ request('q') }}" placeholder="{{ $meta['search'] }}" data-filter-search autocomplete="off"><button class="filter-search__submit" type="submit" aria-label="Search"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4" stroke-linecap="round"></path></svg></button></div>
        @if($hasStatusFilter)
        <details class="filter-menu" data-filter-menu><summary class="filter-menu__trigger"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4" stroke-linecap="round"></path></svg><span>Filters</span>@if($activeFilterCount)<span class="filter-count">{{ $activeFilterCount }}</span>@endif<svg class="filter-menu__chevron" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8 10 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path></svg></summary><div class="filter-menu__panel filter-menu__panel--compact"><div class="filter-menu__head"><div><small>{{ $meta['kicker'] }}</small><strong>Refine this workspace</strong></div><span>{{ $activeFilterCount }} active</span></div><div class="filter-menu__grid filter-menu__grid--1"><label class="filter-field"><span>Status</span><select name="status"><option value="">Any status</option>@foreach($selectOptions['status']??[] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></label></div><div class="filter-menu__footer"><a href="{{ route('tenant.resource.index',$resource) }}" class="filter-reset">Reset all</a><button class="btn sm">Apply filter</button></div></div></details>
        @endif
        <div class="filter-command__meta"><b>{{ number_format($items->total()) }}</b><span>results</span></div>
    </form>
    @if(request()->hasAny(['q','status']))<div class="active-filter-row"><span class="active-filter-row__label">Active</span>@if(request('q'))<button type="button" class="filter-chip" data-filter-clear="q">Search: {{ \Illuminate\Support\Str::limit(request('q'),24) }}<span>×</span></button>@endif @if($hasStatusFilter && request('status'))<button type="button" class="filter-chip" data-filter-clear="status">{{ ucfirst(str_replace('_',' ',request('status'))) }}<span>×</span></button>@endif <a href="{{ route('tenant.resource.index',$resource) }}" class="active-filter-row__reset">Clear all</a></div>@endif

    <div class="smart-register">
        @forelse($items as $item)
            @if($resource==='owners')
                <article data-quick-view="{{ route('tenant.resource.quick',[$resource,$item->id]) }}" data-quick-view-title="{{ ucfirst($meta['noun']) }} preview" class="register-row register-row--contact">
                    <div class="contact-avatar">{{ strtoupper(mb_substr($item->name,0,1)) }}</div>
                    <div class="register-main"><strong>{{ $item->name }}</strong><span>{{ $item->phone ?: 'No phone' }} @if($item->whatsapp) · WhatsApp {{ $item->whatsapp }} @endif</span></div>
                    <div class="register-fact"><span>Tazkira</span><b>{{ $item->tazkira_no ?: '—' }}</b></div>
                    <div class="register-fact register-fact--wide"><span>Address</span><b>{{ \Illuminate\Support\Str::limit($item->address,42) ?: '—' }}</b></div>
                    <div class="row-more"><span>{{ $item->created_at?->format('d M Y') }}</span><form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}" onsubmit="return confirm('Remove this owner record?')">@csrf @method('DELETE')<button class="icon-quiet" title="Delete">×</button></form></div>
                </article>
            @elseif($resource==='customers')
                <article data-quick-view="{{ route('tenant.resource.quick',[$resource,$item->id]) }}" data-quick-view-title="{{ ucfirst($meta['noun']) }} preview" class="register-row register-row--contact">
                    <div class="contact-avatar contact-avatar--customer">{{ strtoupper(mb_substr($item->name,0,1)) }}</div>
                    <div class="register-main"><strong>{{ $item->name }}</strong><span>{{ $item->phone ?: 'No phone' }} @if($item->source) · {{ ucfirst(str_replace('_',' ',$item->source)) }} @endif</span></div>
                    <div class="register-fact"><span>Tazkira</span><b>{{ $item->tazkira_no ?: '—' }}</b></div>
                    <div class="register-fact register-fact--wide"><span>Notes</span><b>{{ \Illuminate\Support\Str::limit($item->notes,48) ?: 'No notes yet' }}</b></div>
                    <div class="row-more"><span>{{ $item->created_at?->format('d M Y') }}</span><form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}" onsubmit="return confirm('Remove this customer record?')">@csrf @method('DELETE')<button class="icon-quiet" title="Delete">×</button></form></div>
                </article>
            @elseif($resource==='leads')
                <article data-quick-view="{{ route('tenant.resource.quick',[$resource,$item->id]) }}" data-quick-view-title="{{ ucfirst($meta['noun']) }} preview" class="register-row register-row--pipeline">
                    <div class="pipeline-dot pipeline-dot--{{ $item->status }}"></div>
                    <div class="register-main"><strong>{{ $item->customer?->name ?: '—' }}</strong><span>{{ $item->property?->code ? $item->property->code.' — '.$item->property->title : '—' }}</span></div>
                    <div class="register-fact"><span>Stage</span><b><span class="badge {{ in_array($item->status,['won'])?'success':(in_array($item->status,['lost'])?'danger':'neutral') }}">{{ str_replace('_',' ',$item->status ?: 'new') }}</span></b></div>
                    <div class="register-fact"><span>Agent</span><b>{{ $item->assignedUser?->name ?: '—' }}</b></div>
                    <div class="register-fact"><span>Follow-up</span><b>{{ $item->next_follow_up_at?->format('d M · H:i') ?: 'Not set' }}</b></div>
                    <div class="row-more"><span>{{ ucfirst($item->source ?: 'direct') }}</span><form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}" onsubmit="return confirm('Remove this lead?')">@csrf @method('DELETE')<button class="icon-quiet">×</button></form></div>
                </article>
            @elseif($resource==='visits')
                <article data-quick-view="{{ route('tenant.resource.quick',[$resource,$item->id]) }}" data-quick-view-title="{{ ucfirst($meta['noun']) }} preview" class="register-row register-row--visit">
                    <div class="date-tile"><b>{{ $item->scheduled_at?->format('d') ?: '—' }}</b><span>{{ $item->scheduled_at?->format('M') ?: '' }}</span></div>
                    <div class="register-main"><strong>{{ $item->customer?->name ?: '—' }}</strong><span>{{ $item->property?->code ? $item->property->code.' — '.$item->property->title : '—' }}</span></div>
                    <div class="register-fact"><span>Time</span><b>{{ $item->scheduled_at?->format('H:i') ?: '—' }}</b></div>
                    <div class="register-fact"><span>Agent</span><b>{{ $item->assignedUser?->name ?: '—' }}</b></div>
                    <div class="register-fact"><span>Status</span><b><span class="badge {{ $item->status==='completed'?'success':'neutral' }}">{{ str_replace('_',' ',$item->status ?: 'scheduled') }}</span></b></div>
                    <div class="row-more"><span>{{ \Illuminate\Support\Str::limit($item->outcome,18) ?: 'No outcome' }}</span><form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}" onsubmit="return confirm('Remove this visit?')">@csrf @method('DELETE')<button class="icon-quiet">×</button></form></div>
                </article>
            @elseif($resource==='payments')
                <article data-quick-view="{{ route('tenant.resource.quick',[$resource,$item->id]) }}" data-quick-view-title="{{ ucfirst($meta['noun']) }} preview" class="register-row register-row--money">
                    <div class="money-direction {{ $item->direction==='out'?'money-direction--out':'' }}">{{ $item->direction==='out'?'↗':'↙' }}</div>
                    <div class="register-main"><strong>{{ ucfirst($selectOptions['category'][$item->category]??$item->category??'Payment') }}</strong><span>{{ $item->reference_no ?: 'No reference' }} · {{ ucfirst($selectOptions['method'][$item->method]??$item->method??'cash') }}</span></div>
                    <div class="register-fact register-fact--money"><span>{{ $item->direction==='out'?'Money out':'Money in' }}</span><b>{{ number_format((float)$item->amount,2) }} {{ $item->currency }}</b></div>
                    <div class="register-fact"><span>Linked party</span><b>{{ $item->customer?->name ?: ($item->owner?->name ?: '—') }}</b></div>
                    <div class="register-fact"><span>Date</span><b>{{ $item->paid_at?->format('d M Y') ?: $item->created_at?->format('d M Y') }}</b></div>
                    <div class="row-more"><span>{{ $item->currency }}</span><form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}" onsubmit="return confirm('Remove this payment?')">@csrf @method('DELETE')<button class="icon-quiet">×</button></form></div>
                </article>
            @else
                <article data-quick-view="{{ route('tenant.resource.quick',[$resource,$item->id]) }}" data-quick-view-title="{{ ucfirst($meta['noun']) }} preview" class="register-row register-row--money">
                    <div class="money-direction money-direction--out">↗</div>
                    <div class="register-main"><strong>{{ ucfirst(str_replace('_',' ',$selectOptions['category'][$item->category]??$item->category??'Expense')) }}</strong><span>{{ $item->reference_no ?: 'No reference' }} · {{ ucfirst($selectOptions['method'][$item->method]??$item->method??'cash') }}</span></div>
                    <div class="register-fact register-fact--money"><span>Expense</span><b>{{ number_format((float)$item->amount,2) }} {{ $item->currency }}</b></div>
                    <div class="register-fact register-fact--wide"><span>Description</span><b>{{ \Illuminate\Support\Str::limit($item->description,38) ?: '—' }}</b></div>
                    <div class="register-fact"><span>Date</span><b>{{ $item->expense_date?->format('d M Y') ?: $item->created_at?->format('d M Y') }}</b></div>
                    <div class="row-more"><span>{{ $item->currency }}</span><form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}" onsubmit="return confirm('Remove this expense?')">@csrf @method('DELETE')<button class="icon-quiet">×</button></form></div>
                </article>
            @endif
        @empty
            <x-workspace.empty title="No {{ strtolower($meta['title']) }} yet" description="Use {{ strtolower($meta['create']) }} to create the first record." />
        @endforelse
    </div>
    <div class="pagination collection-pagination">{{ $items->links() }}</div>
</div>
@endsection

@push('modals')
<x-workspace.drawer id="resource-create-drawer" :title="$meta['create']" :kicker="$meta['kicker']" description="Create the record here and return to the same workspace without losing context." :wide="in_array($resource,['payments','leads','visits'])">
    <form method="post" action="{{ route('tenant.resource.store',$resource) }}" class="drawer-form">@csrf
        <div class="smart-sheet__body">
            <div class="drawer-intro">
                <span>{{ strtoupper(mb_substr($meta['noun'],0,1)) }}</span>
                <div><b>{{ $meta['create'] }}</b><small>Only capture what is useful now. You can enrich the record as the relationship grows.</small></div>
            </div>
            <div class="drawer-grid">
            @foreach($fields as $field=>$type)
                @php($label=$labels[$field]??ucwords(str_replace('_',' ',$field)))
                <div class="field {{ in_array($type,['textarea'])?'drawer-span-2':'' }}">
                    <label>{{ $label }}</label>
                    @if(isset($lookupUrls[$field]))
                        <div class="inline-create-field">
                            <select name="{{ $field }}" id="resource_{{ $field }}" data-remote-url="{{ $lookupUrls[$field] }}" @if(isset($relationMeta[$field])) data-smart-resource="{{ $relationMeta[$field]['resource'] }}" @endif data-placeholder="Search {{ strtolower($label) }}" @if(in_array($field,['customer_id','property_id']) && in_array($resource,['leads','visits'])) required @endif>
                                <option value=""></option>
                            </select>
                            @if(isset($relationMeta[$field]))<button class="inline-create-link" type="button" data-modal-open="{{ $relationMeta[$field]['modal'] }}" data-quick-target="resource_{{ $field }}">{{ $relationMeta[$field]['label'] }}</button>@endif
                        </div>
                    @elseif(isset($selectOptions[$field]))
                        <select name="{{ $field }}"><option value="">Select {{ strtolower($label) }}</option>@foreach($selectOptions[$field] as $value=>$text)<option value="{{ $value }}">{{ $text }}</option>@endforeach</select>
                    @elseif($type==='textarea')
                        <textarea name="{{ $field }}" placeholder="Add useful context, not unnecessary detail"></textarea>
                    @else
                        <input type="{{ $type }}" name="{{ $field }}" @if($type==='number') step="any" @endif>
                    @endif
                </div>
            @endforeach
            </div>
        </div>
        <div class="smart-sheet__footer">
            <span class="sheet-footer-note">Saved directly into this Rahnama workspace.</span>
            <button type="button" class="btn secondary" data-modal-close>Cancel</button>
            <button class="btn">Save {{ $meta['noun'] }}</button>
        </div>
    </form>
</x-workspace.drawer>

@if(in_array('customer_id',array_keys($fields)))<x-quick-create.customer-modal />@endif
@if(in_array('owner_id',array_keys($fields)) || in_array('property_id',array_keys($fields)))<x-quick-create.owner-modal />@endif
@if(in_array('property_id',array_keys($fields)))<x-quick-create.property-modal :provinces="$provinces" />@endif
@endpush
