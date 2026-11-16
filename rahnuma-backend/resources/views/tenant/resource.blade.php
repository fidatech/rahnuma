@extends('layouts.workspace')
@php
$meta = [
 'owners'=>['icon'=>'user-tie','title'=>'Owners & Landlords','subtitle'=>'People behind your inventory, ownership records and landlord relationships.','create'=>'Add owner','search'=>'Search name, phone, Tazkira or address','noun'=>'owner','preview'=>'Owner preview','confirm'=>'Remove this owner record?','empty'=>'No owners yet'],
 'customers'=>['icon'=>'users','title'=>'Customers','subtitle'=>'Buyers, tenants and prospects with a clean history of every interaction.','create'=>'Add customer','search'=>'Search name, phone, source or address','noun'=>'customer','preview'=>'Customer preview','confirm'=>'Remove this customer record?','empty'=>'No customers yet'],
 'leads'=>['icon'=>'filter','title'=>'Lead Pipeline','subtitle'=>'Move every inquiry from first contact to visit, negotiation and closed deal.','create'=>'Capture lead','search'=>'Search source, status or notes','noun'=>'lead','preview'=>'Lead preview','confirm'=>'Remove this lead?','empty'=>'No leads yet'],
 'visits'=>['icon'=>'calendar-check','title'=>'Visits & Follow-ups','subtitle'=>'A clear agenda of property visits, outcomes and next actions.','create'=>'Schedule visit','search'=>'Search visit status, outcome or notes','noun'=>'visit','preview'=>'Visit preview','confirm'=>'Remove this visit?','empty'=>'No visits yet'],
 'payments'=>['icon'=>'money-bill-wave','title'=>'Payments','subtitle'=>'Track commission, rent, deposits and other money movements connected to real deals.','create'=>'Record payment','search'=>'Search reference, category, method or notes','noun'=>'payment','preview'=>'Payment preview','confirm'=>'Remove this payment?','empty'=>'No payments yet'],
 'expenses'=>['icon'=>'receipt','title'=>'Expenses','subtitle'=>'Record operating costs without turning daily bookkeeping into accounting work.','create'=>'Add expense','search'=>'Search category, reference or description','noun'=>'expense','preview'=>'Expense preview','confirm'=>'Remove this expense?','empty'=>'No expenses yet'],
][$resource];
$relationMeta=['customer_id'=>['resource'=>'customer','modal'=>'quick-customer-modal','label'=>'New customer'],'owner_id'=>['resource'=>'owner','modal'=>'quick-owner-modal','label'=>'New owner'],'property_id'=>['resource'=>'property','modal'=>'quick-property-modal','label'=>'New property']];
$labels=['name'=>'Full name','phone'=>'Phone','whatsapp'=>'WhatsApp','email'=>'Email','address'=>'Address','source'=>'Source','notes'=>'Notes','status'=>'Status','outcome'=>'Outcome','rejection_reason'=>'Rejection reason','direction'=>'Direction','category'=>'Category','amount'=>'Amount','currency'=>'Currency','method'=>'Method','description'=>'Description','customer_id'=>'Customer','owner_id'=>'Owner / landlord','property_id'=>'Property','transaction_id'=>'Transaction','assigned_user_id'=>'Assigned agent','next_follow_up_at'=>'Next follow-up','scheduled_at'=>'Visit date & time','exchange_rate_to_afn'=>'Exchange rate to AFN','reference_no'=>'Reference','paid_at'=>'Payment date & time','expense_date'=>'Expense date','tazkira_no'=>'Tazkira number'];
$hasStatusFilter=in_array($resource,['leads','visits']);
$filtered=request()->hasAny(['q','status']) && (filled(request('q')) || filled(request('status')));
$quick=fn($item)=>route('tenant.resource.quick',[$resource,$item->id]);
$option=fn(string $field,$value,$empty='—')=>filled($value) ? __($selectOptions[$field][$value] ?? ucfirst(str_replace(['_','-'],' ',$value))) : $empty;
@endphp
@section('title', __($meta['title']))
@section('workspace')
<x-page-header :title="__($meta['title'])" :icon="$meta['icon']" :subtitle="__($meta['subtitle'])">
    <button class="btn btn-primary" type="button" data-modal-open="resource-create-drawer"><x-icon name="plus" class="me-1" /> {{ __($meta['create']) }}</button>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('Total records')" :value="number_format($stats['total'])" :icon="$meta['icon']" tone="blue" :note="__('All-time')" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Added this month')" :value="number_format($stats['month'])" icon="calendar-plus" tone="violet" /></div>
    @if($stats['attention'] !== null)
        <div class="col-6 col-xl-3"><x-stat :label="$resource==='owners' ? __('Without property') : __('Needs attention')" :value="number_format($stats['attention'])" icon="triangle-exclamation" tone="amber" :note="$resource==='leads' ? __('Open pipeline') : ($resource==='visits' ? __('Scheduled') : __('Relationship gap'))" /></div>
        <div class="col-6 col-xl-3"><x-stat :label="$resource==='owners' ? __('With property') : __('Completed')" :value="number_format($stats['done'])" icon="circle-check" tone="green" :note="$resource==='leads' ? __('Won') : ($resource==='visits' ? __('Visits done') : __('Linked inventory'))" /></div>
    @endif
</div>

<div class="card">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3" data-filter-form>
            <div class="col-12 col-md">
                <input type="search" class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __($meta['search']) }}" aria-label="{{ __('Search') }}" data-filter-search autocomplete="off">
            </div>
            @if($hasStatusFilter)
                <div class="col col-md-auto">
                    <select class="form-select" name="status" aria-label="{{ __('Status') }}" data-plain onchange="this.form.requestSubmit()">
                        <option value="">{{ __('Any status') }}</option>
                        @foreach($selectOptions['status']??[] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ __($label) }}</option>@endforeach
                    </select>
                </div>
            @endif
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-light-secondary" type="submit" aria-label="{{ __('Search') }}"><x-icon name="magnifying-glass" /></button>
                @if($filtered)<a class="btn btn-outline-soft" href="{{ route('tenant.resource.index',$resource) }}">{{ __('Clear all') }}</a>@endif
            </div>
        </form>

        @if($items->isEmpty())
            <x-workspace.empty :icon="$meta['icon']" :title="$filtered ? __('Nothing found') : __($meta['empty'])" :description="$filtered ? __('Try another search or clear the filters.') : __('Use :button to create the first record.', ['button' => __($meta['create'])])" />
        @else
            <table class="table table-hover w-100 align-middle">
                <thead>
                    <tr>
                    @if($resource==='owners')
                        <th>{{ __('Owner / landlord') }}</th><th>{{ __('Tazkira') }}</th><th>{{ __('Address') }}</th><th>{{ __('Added') }}</th>
                    @elseif($resource==='customers')
                        <th>{{ __('Customer') }}</th><th>{{ __('Tazkira') }}</th><th>{{ __('Notes') }}</th><th>{{ __('Added') }}</th>
                    @elseif($resource==='leads')
                        <th>{{ __('Customer') }}</th><th>{{ __('Stage') }}</th><th>{{ __('Agent') }}</th><th>{{ __('Follow-up') }}</th><th>{{ __('Source') }}</th>
                    @elseif($resource==='visits')
                        <th>{{ __('Customer') }}</th><th>{{ __('Date') }}</th><th>{{ __('Agent') }}</th><th>{{ __('Status') }}</th><th>{{ __('Outcome') }}</th>
                    @elseif($resource==='payments')
                        <th>{{ __('Payment') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Linked party') }}</th><th>{{ __('Date') }}</th>
                    @else
                        <th>{{ __('Expense') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Description') }}</th><th>{{ __('Date') }}</th>
                    @endif
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr class="cursor-pointer" data-quick-view="{{ $quick($item) }}" data-quick-view-title="{{ __($meta['preview']) }}">
                    @if($resource==='owners')
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm avatar-soft-primary">{{ initial($item->name) }}</span><div class="min-w-0"><strong class="d-block text-heading">{{ $item->name }}</strong><span class="small text-muted" dir="ltr">{{ $item->phone ?: __('No phone') }}@if($item->whatsapp) · WhatsApp {{ $item->whatsapp }}@endif</span></div></div></td>
                        <td dir="ltr">{{ $item->tazkira_no ?: '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($item->address,42) ?: '—' }}</td>
                        <td class="text-nowrap" dir="ltr">{{ $item->created_at?->format('d M Y') }}</td>
                    @elseif($resource==='customers')
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm avatar-soft-purple">{{ initial($item->name) }}</span><div class="min-w-0"><strong class="d-block text-heading">{{ $item->name }}</strong><span class="small text-muted"><span dir="ltr">{{ $item->phone ?: __('No phone') }}</span>@if($item->source) · {{ $option('source',$item->source) }}@endif</span></div></div></td>
                        <td dir="ltr">{{ $item->tazkira_no ?: '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($item->notes,48) ?: __('No notes yet') }}</td>
                        <td class="text-nowrap" dir="ltr">{{ $item->created_at?->format('d M Y') }}</td>
                    @elseif($resource==='leads')
                        <td><strong class="d-block text-heading">{{ $item->customer?->name ?: '—' }}</strong><span class="small text-muted">{{ $item->property?->code ? $item->property->code.' — '.$item->property->title : '—' }}</span></td>
                        <td><span class="badge-soft {{ status_tone($item->status ?: 'new') }}">{{ $option('status',$item->status ?: 'new') }}</span></td>
                        <td>{{ $item->assignedUser?->name ?: '—' }}</td>
                        <td class="text-nowrap" dir="ltr">{{ $item->next_follow_up_at?->format('d M · H:i') ?: '—' }}</td>
                        <td>{{ $option('source',$item->source ?: 'other') }}</td>
                    @elseif($resource==='visits')
                        <td><strong class="d-block text-heading">{{ $item->customer?->name ?: '—' }}</strong><span class="small text-muted">{{ $item->property?->code ? $item->property->code.' — '.$item->property->title : '—' }}</span></td>
                        <td class="text-nowrap" dir="ltr">{{ $item->scheduled_at?->format('d M Y · H:i') ?: '—' }}</td>
                        <td>{{ $item->assignedUser?->name ?: '—' }}</td>
                        <td><span class="badge-soft {{ status_tone($item->status ?: 'scheduled') }}">{{ $option('status',$item->status ?: 'scheduled') }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($item->outcome,28) ?: __('No outcome') }}</td>
                    @elseif($resource==='payments')
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm rounded-3 {{ $item->direction==='out' ? 'avatar-soft-danger' : 'avatar-soft-success' }}"><x-icon :name="$item->direction==='out' ? 'arrow-up' : 'arrow-down'" /></span><div class="min-w-0"><strong class="d-block text-heading">{{ $option('category',$item->category,__('Payment')) }}</strong><span class="small text-muted">{{ $item->reference_no ?: __('No reference') }} · {{ $option('method',$item->method ?: 'cash') }}</span></div></div></td>
                        <td class="text-nowrap"><strong class="{{ $item->direction==='out' ? 'text-danger' : 'text-success' }}" dir="ltr">{{ number_format((float)$item->amount,2) }} {{ $item->currency }}</strong><div class="small text-muted">{{ $item->direction==='out' ? __('Money out') : __('Money in') }}</div></td>
                        <td>{{ $item->customer?->name ?: ($item->owner?->name ?: '—') }}</td>
                        <td class="text-nowrap" dir="ltr">{{ $item->paid_at?->format('d M Y') ?: $item->created_at?->format('d M Y') }}</td>
                    @else
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm rounded-3 avatar-soft-danger"><x-icon name="arrow-up" /></span><div class="min-w-0"><strong class="d-block text-heading">{{ $option('category',$item->category,__('Expense')) }}</strong><span class="small text-muted">{{ $item->reference_no ?: __('No reference') }} · {{ $option('method',$item->method ?: 'cash') }}</span></div></div></td>
                        <td class="text-nowrap"><strong dir="ltr">{{ number_format((float)$item->amount,2) }} {{ $item->currency }}</strong></td>
                        <td>{{ \Illuminate\Support\Str::limit($item->description,38) ?: '—' }}</td>
                        <td class="text-nowrap" dir="ltr">{{ $item->expense_date?->format('d M Y') ?: $item->created_at?->format('d M Y') }}</td>
                    @endif
                        <td class="text-end text-nowrap">
                            <div class="act-group">
                                <button type="button" class="btn-act is-blue" data-quick-view="{{ $quick($item) }}" data-quick-view-title="{{ __($meta['preview']) }}" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-icon name="eye" /></button>
                                <form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}" class="d-inline" data-confirm="{{ __($meta['confirm']) }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button type="submit" class="btn-act is-red" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-icon name="trash" /></button></form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $items->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection

@push('modals')
<x-workspace.drawer id="resource-create-drawer" :title="__($meta['create'])" :description="__('Only capture what is useful now. You can enrich the record later.')" :icon="$meta['icon']" :wide="in_array($resource,['payments','leads','visits','expenses'])">
    <form method="post" action="{{ route('tenant.resource.store',$resource) }}">@csrf
        <div class="modal-body">
            <div class="row g-3">
            @foreach($fields as $field=>$type)
                @php($label=__($labels[$field]??ucwords(str_replace('_',' ',$field))))
                <div class="col-12 {{ $type==='textarea' ? '' : 'col-md-6' }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label" for="resource_{{ $field }}">{{ $label }}</label>
                        @if(isset($lookupUrls[$field]) && isset($relationMeta[$field]))<button class="btn btn-sm btn-link p-0 mb-2 text-decoration-none" type="button" data-modal-open="{{ $relationMeta[$field]['modal'] }}" data-quick-target="resource_{{ $field }}"><x-icon name="plus" class="small" /> {{ __($relationMeta[$field]['label']) }}</button>@endif
                    </div>
                    @if(isset($lookupUrls[$field]))
                        <select class="form-select" name="{{ $field }}" id="resource_{{ $field }}" data-remote-url="{{ $lookupUrls[$field] }}" @if(isset($relationMeta[$field])) data-smart-resource="{{ $relationMeta[$field]['resource'] }}" @endif data-placeholder="{{ __('Search…') }}" @if(in_array($field,['customer_id','property_id']) && in_array($resource,['leads','visits'])) required @endif>
                            <option value=""></option>
                        </select>
                    @elseif(isset($selectOptions[$field]))
                        <select class="form-select" name="{{ $field }}" id="resource_{{ $field }}" data-placeholder="{{ __('Choose…') }}"><option value="">{{ __('Choose…') }}</option>@foreach($selectOptions[$field] as $value=>$text)<option value="{{ $value }}">{{ __($text) }}</option>@endforeach</select>
                    @elseif($type==='textarea')
                        <textarea class="form-control" name="{{ $field }}" id="resource_{{ $field }}" rows="3" placeholder="{{ __('Add useful context, not unnecessary detail') }}"></textarea>
                    @else
                        <input class="form-control" type="{{ $type }}" name="{{ $field }}" id="resource_{{ $field }}" @if($type==='number') step="any" dir="ltr" @endif @if(in_array($field,['phone','whatsapp','tazkira_no','reference_no','email'])) dir="ltr" @endif @if(in_array($field,['phone','whatsapp'])) inputmode="tel" @endif>
                    @endif
                </div>
            @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <span class="small text-muted me-auto">{{ __('Saved directly into this Rahnama workspace.') }}</span>
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save') }}</button>
        </div>
    </form>
</x-workspace.drawer>

@if(in_array('customer_id',array_keys($fields)))<x-quick-create.customer-modal />@endif
@if(in_array('owner_id',array_keys($fields)) || in_array('property_id',array_keys($fields)))<x-quick-create.owner-modal />@endif
@if(in_array('property_id',array_keys($fields)))<x-quick-create.property-modal :provinces="$provinces" />@endif
@endpush
