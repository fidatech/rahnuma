@extends('layouts.workspace')
@section('title', __('Promotions'))
@section('workspace')
@php
    $money = fn ($set) => $set->isEmpty() ? '0' : $set->map(fn ($total, $currency) => number_format((float) $total).' '.$currency)->join(' · ');
    $left = fn ($until) => $until ? ($until->isFuture() ? __(':count days left', ['count' => max(1, (int) ceil(now()->diffInHours($until) / 24))]) : __('Ended')) : __('No end date');
    $names = ['sponsored' => 'Sponsored', 'featured' => 'Featured', 'featured_office' => 'Featured office'];
    $tones = ['sponsored' => 'warning', 'featured' => 'primary', 'featured_office' => 'success'];
    $thumb = fn ($p) => $p->coverMedia ? asset('storage/'.($p->coverMedia->thumbnail_path ?: $p->coverMedia->path)) : null;
@endphp
<x-page-header :title="__('Promotions')" icon="bullhorn" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Promotions')]]" :subtitle="__('Sponsored and featured placements are sold by the administrator. Offices cannot give them to themselves, and every fee goes to money in.')">
    <a class="btn btn-outline-soft" href="{{ route('platform.finance', ['direction' => 'in']) }}"><x-icon name="sack-dollar" class="me-1" /> {{ __('Money in') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('Sponsored properties')" :value="number_format($sponsored->count())" icon="bullhorn" tone="amber" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Featured properties')" :value="number_format($featured->count())" icon="star" tone="blue" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Featured offices')" :value="number_format($featuredOffices->count())" icon="building-user" tone="green" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Promotion income this month')" :value="$money($income)" icon="circle-arrow-down" tone="violet" /></div>
</div>

@php $methods = ['hesabpay' => 'HesabPay', 'aurumpay' => 'AurumPay', 'mobile_money' => __('Mobile money'), 'cash' => __('Cash')]; @endphp
<div class="card rh-queue">
    <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Requests from offices') }} @if ($requests->isNotEmpty())<span class="rh-filter-count">{{ $requests->count() }}</span>@endif</h5><div class="small text-muted">{{ __('Check the payment, then approve to start the placement or reject with a reason.') }}</div></div><button class="btn btn-sm btn-outline-soft" type="button" data-modal-open="promo-prices"><x-icon name="gear" /> {{ __('Prices and payment details') }}</button></div>
    <div class="card-body">
        @if ($requests->isEmpty())
            <x-workspace.empty icon="circle-check" :title="__('No request is waiting.')" :description="__('New requests from offices show here.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Property') }}</th><th>{{ __('Office') }}</th><th>{{ __('Placement') }}</th><th>{{ __('Payment') }}</th><th class="text-end">{{ __('Amount') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach ($requests as $q)
                <tr>
                    <td><strong class="d-block text-heading text-truncate" style="max-width:240px">{{ $q->property?->title ?: '—' }}</strong><span class="small text-muted" dir="ltr">{{ $q->property?->code }} · {{ $q->created_at?->format('Y-m-d H:i') }}</span></td>
                    <td>@if ($q->agency)<a href="{{ route('platform.agencies.show', $q->agency) }}">{{ $q->agency->name }}</a>@else—@endif<span class="small text-muted d-block">{{ $q->requester?->name }}</span></td>
                    <td><span class="badge-soft {{ $tones[$q->kind] ?? 'secondary' }}">{{ __($names[$q->kind] ?? $q->kind) }}</span><span class="small text-muted d-block">{{ __(':count days', ['count' => $q->days]) }}</span></td>
                    <td>{{ $methods[$q->payment_method] ?? $q->payment_method }}@if ($q->payment_reference)<span class="small d-block" dir="ltr">{{ $q->payment_reference }}</span>@endif @if ($q->receipt_path)<a class="small fw-bold d-block" href="{{ route('platform.marketplace.requests.receipt', $q->id) }}" target="_blank" rel="noopener" data-native><x-icon name="file" /> {{ __('View receipt') }}</a>@endif @if ($q->notes)<span class="small text-muted d-block">{{ $q->notes }}</span>@endif</td>
                    <td class="text-end fw-bold text-nowrap" dir="ltr">{{ number_format((float) $q->amount) }} {{ $q->currency }}</td>
                    <td class="text-end text-nowrap"><div class="rh-actions">
                        <button class="btn btn-sm rh-btn-in" type="button" data-modal="request-approve" data-action="{{ route('platform.marketplace.requests.approve', $q->id) }}" data-title="{{ $q->property?->code }} · {{ $q->property?->title }} · {{ __(':count days', ['count' => $q->days]) }} · {{ $methods[$q->payment_method] ?? $q->payment_method }} {{ $q->payment_reference }}" data-edit="{{ json_encode(['amount' => (float) $q->amount]) }}"><x-icon name="circle-check" /> <span>{{ __('Approve') }}</span></button>
                        <button class="btn btn-sm btn-soft-danger" type="button" data-modal="request-reject" data-action="{{ route('platform.marketplace.requests.reject', $q->id) }}" data-title="{{ $q->property?->code }} · {{ $q->property?->title }}" data-edit="{}"><x-icon name="ban" /> <span>{{ __('Reject') }}</span></button>
                    </div></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
        @if ($reviewed->isNotEmpty())
            <div class="rh-reviewed"><span>{{ __('Recently decided') }}</span>@foreach ($reviewed as $q)<span class="badge-soft {{ $q->status === 'approved' ? 'success' : 'danger' }}" title="{{ $q->admin_note }}">{{ $q->property?->code }} · {{ $q->agency?->name }} · {{ enum_label($q->status) }}</span>@endforeach</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Running placements') }}</h5><div class="small text-muted">{{ __('What is promoted on the marketplace right now, and until when.') }}</div></div></div>
    <div class="card-body">
        @if ($sponsored->isEmpty() && $featured->isEmpty() && $featuredOffices->isEmpty())
            <x-workspace.empty icon="bullhorn" :title="__('Nothing is promoted right now.')" :description="__('Sell a placement from the lists below.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Placement') }}</th><th>{{ __('Office') }}</th><th>{{ __('Kind') }}</th><th>{{ __('Until') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach ([['sponsored', $sponsored, 'sponsored_until'], ['featured', $featured, 'featured_until']] as [$kind, $rows, $field])
                @foreach ($rows as $p)
                    <tr>
                        <td><div class="d-flex align-items-center gap-2"><span class="rh-thumb">@if ($thumb($p))<img src="{{ $thumb($p) }}" alt="" loading="lazy">@else<x-icon name="building" />@endif</span><div class="min-w-0"><strong class="d-block text-heading text-truncate" style="max-width:300px">{{ $p->title }}</strong><span class="small text-muted" dir="ltr">{{ $p->code }}</span></div></div></td>
                        <td>@if ($p->agency)<a href="{{ route('platform.agencies.show', $p->agency) }}">{{ $p->agency->name }}</a>@else—@endif</td>
                        <td><span class="badge-soft {{ $tones[$kind] }}">{{ __($names[$kind]) }}</span></td>
                        <td><span dir="ltr">{{ $p->$field?->format('Y-m-d') ?: '—' }}</span><span class="small text-muted d-block">{{ $left($p->$field) }}</span></td>
                        <td class="text-end text-nowrap"><div class="rh-actions">
                            <button class="btn btn-sm btn-soft-primary" type="button" data-modal="promote-modal" data-title="{{ __($names[$kind]) }} · {{ $p->code }} · {{ $p->title }}" data-edit="{{ json_encode(['kind' => $kind, 'property_id' => $p->id, 'agency_id' => '']) }}"><x-icon name="rotate" /> <span>{{ __('Add days') }}</span></button>
                            <form method="post" action="{{ route('platform.marketplace.stop') }}" data-confirm="{{ __('End this placement now?') }}" data-confirm-text="{{ __('The fee already received stays in money in.') }}" data-confirm-ok="{{ __('End placement') }}">@csrf<input type="hidden" name="kind" value="{{ $kind }}"><input type="hidden" name="id" value="{{ $p->id }}"><button class="btn btn-sm btn-light-secondary"><x-icon name="ban" /> <span>{{ __('End') }}</span></button></form>
                        </div></td>
                    </tr>
                @endforeach
            @endforeach
            @foreach ($featuredOffices as $a)
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm rounded-3 avatar-soft-primary">@if ($a->logo_path)<img src="{{ asset('storage/'.$a->logo_path) }}" alt="">@else{{ initial($a->name) }}@endif</span><strong class="text-heading">{{ $a->name }}</strong></div></td>
                    <td><a href="{{ route('platform.agencies.show', $a) }}">{{ $a->name }}</a></td>
                    <td><span class="badge-soft {{ $tones['featured_office'] }}">{{ __($names['featured_office']) }}</span></td>
                    <td><span dir="ltr">{{ $a->marketplace_featured_until?->format('Y-m-d') ?: '—' }}</span><span class="small text-muted d-block">{{ $left($a->marketplace_featured_until) }}</span></td>
                    <td class="text-end text-nowrap"><div class="rh-actions">
                        <button class="btn btn-sm btn-soft-primary" type="button" data-modal="promote-modal" data-title="{{ __($names['featured_office']) }} · {{ $a->name }}" data-edit="{{ json_encode(['kind' => 'featured_office', 'agency_id' => $a->id, 'property_id' => '']) }}"><x-icon name="rotate" /> <span>{{ __('Add days') }}</span></button>
                        <form method="post" action="{{ route('platform.marketplace.stop') }}" data-confirm="{{ __('End this placement now?') }}" data-confirm-text="{{ __('The fee already received stays in money in.') }}" data-confirm-ok="{{ __('End placement') }}">@csrf<input type="hidden" name="kind" value="featured_office"><input type="hidden" name="id" value="{{ $a->id }}"><button class="btn btn-sm btn-light-secondary"><x-icon name="ban" /> <span>{{ __('End') }}</span></button></form>
                    </div></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card h-100 mb-0">
            <div class="card-header flex-wrap gap-2">
                <div><h5 class="card-title mb-0">{{ __('Sell a placement for a property') }}</h5><div class="small text-muted">{{ __('Sponsored: first on the home page and in search. Featured: in the featured collection, with a badge.') }}</div></div>
                <form method="get" class="d-flex gap-2"><input class="form-control form-control-sm" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search property code or title') }}"><button class="btn btn-sm btn-light-secondary" aria-label="{{ __('Search') }}"><x-icon name="magnifying-glass" /></button></form>
            </div>
            <div class="card-body">
                @if ($properties->isEmpty())
                    <x-workspace.empty icon="building" :title="__('No properties found.')" :description="__('Only public listings can be promoted.')" />
                @else
                <table class="table table-hover w-100 align-middle">
                    <thead><tr><th>{{ __('Property') }}</th><th>{{ __('Office') }}</th><th class="text-end">{{ __('Sell') }}</th></tr></thead>
                    <tbody>
                    @foreach ($properties as $p)
                        <tr>
                            <td><div class="d-flex align-items-center gap-2"><span class="rh-thumb">@if ($thumb($p))<img src="{{ $thumb($p) }}" alt="" loading="lazy">@else<x-icon name="building" />@endif</span><div class="min-w-0"><strong class="d-block text-heading text-truncate" style="max-width:260px">{{ $p->title }}</strong><span class="small text-muted"><span dir="ltr">{{ $p->code }}</span> · {{ loc_name($p->province) ?: '—' }}</span></div></div></td>
                            <td>{{ $p->agency?->name ?: '—' }}</td>
                            <td class="text-end text-nowrap"><div class="rh-actions">
                                <button class="btn btn-sm btn-gold" type="button" data-modal="promote-modal" data-title="{{ __('Sponsored') }} · {{ $p->code }} · {{ $p->title }}" data-edit="{{ json_encode(['kind' => 'sponsored', 'property_id' => $p->id, 'agency_id' => '']) }}"><x-icon name="bullhorn" /> <span>{{ $p->is_sponsored ? __('Add days') : __('Sponsor') }}</span></button>
                                <button class="btn btn-sm btn-soft-primary" type="button" data-modal="promote-modal" data-title="{{ __('Featured') }} · {{ $p->code }} · {{ $p->title }}" data-edit="{{ json_encode(['kind' => 'featured', 'property_id' => $p->id, 'agency_id' => '']) }}"><x-icon name="star" /> <span>{{ $p->visibility === 'featured' ? __('Add days') : __('Feature') }}</span></button>
                            </div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $properties->links('partials.pagination') }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Feature an office') }}</h5><div class="small text-muted">{{ __('Shown first among offices on the home page.') }}</div></div></div>
            <div class="card-body"><div class="rh-list rh-scroll">
                @forelse ($offices as $a)
                    <div class="rh-list-row"><span class="avatar avatar-sm rounded-3 avatar-soft-primary">@if ($a->logo_path)<img src="{{ asset('storage/'.$a->logo_path) }}" alt="">@else{{ initial($a->name) }}@endif</span><span class="min-w-0 flex-fill"><strong class="d-block text-truncate">{{ $a->name }}</strong>@if ($a->marketplace_featured)<small class="text-success">{{ __('Featured') }}</small>@endif</span><button class="btn btn-sm btn-soft-primary" type="button" data-modal="promote-modal" data-title="{{ __('Featured office') }} · {{ $a->name }}" data-edit="{{ json_encode(['kind' => 'featured_office', 'agency_id' => $a->id, 'property_id' => '']) }}">{{ $a->marketplace_featured ? __('Add days') : __('Feature') }}</button></div>
                @empty
                    <x-workspace.empty icon="building-user" :title="__('No offices found.')" :description="__('Offices on the marketplace show here.')" />
                @endforelse
            </div></div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Sales history') }}</h5><div class="small text-muted">{{ __('The latest placements sold.') }}</div></div></div>
    <div class="card-body">
        @if ($history->isEmpty())
            <x-workspace.empty icon="receipt" :title="__('No placements sold yet.')" :description="__('Each sale is listed here with its fee.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Kind') }}</th><th>{{ __('Placement') }}</th><th>{{ __('Office') }}</th><th>{{ __('Period') }}</th><th class="text-end">{{ __('Fee') }}</th></tr></thead>
            <tbody>
            @foreach ($history as $h)
                <tr>
                    <td dir="ltr" class="text-nowrap">{{ $h->created_at?->format('Y-m-d') }}</td>
                    <td><span class="badge-soft {{ $tones[$h->kind] ?? 'secondary' }}">{{ __($names[$h->kind] ?? $h->kind) }}</span>@if ($h->stopped_at) <span class="badge-soft secondary">{{ __('Ended early') }}</span>@endif</td>
                    <td>@if ($h->property)<span class="d-block text-truncate" style="max-width:240px">{{ $h->property->title }}</span><span class="small text-muted" dir="ltr">{{ $h->property->code }}</span>@else—@endif</td>
                    <td>{{ $h->agency?->name ?: '—' }}</td>
                    <td dir="ltr" class="text-nowrap">{{ $h->starts_at?->format('Y-m-d') }} → {{ $h->ends_at?->format('Y-m-d') }}</td>
                    <td class="text-end fw-bold text-nowrap" dir="ltr">@if ((float) $h->amount > 0)<span class="text-success">+{{ number_format((float) $h->amount) }} {{ $h->currency }}</span>@else<span class="text-muted">{{ __('Free') }}</span>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
@push('modals')
<div class="rh-money-modal is-in">
<x-workspace.drawer id="request-approve" :title="__('Approve request')" :description="__('Confirm the amount you received. The placement starts now and the amount goes to money in.')" icon="circle-check">
<form method="post" action="#">@csrf
<div class="modal-body d-grid gap-3">
    <div class="rh-modal-subject" data-edit-title></div>
    <fieldset class="rh-field"><legend>{{ __('Amount received') }} <span class="req">*</span></legend><div class="rh-amount"><input class="form-control" type="number" step="0.01" min="0" name="amount" dir="ltr" inputmode="decimal" required><span class="fw-bold text-heading">{{ $priceCurrency }}</span></div></fieldset>
    <div><label class="form-label">{{ __('Note to the office') }}</label><input class="form-control" name="admin_note" maxlength="500"></div>
</div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn rh-btn-in" type="submit"><x-icon name="check" class="me-1" /> {{ __('Approve and start') }}</button></div>
</form>
</x-workspace.drawer>
</div>
<div class="rh-money-modal is-out">
<x-workspace.drawer id="request-reject" :title="__('Reject request')" :description="__('The office sees your reason next to its request.')" icon="ban">
<form method="post" action="#">@csrf
<div class="modal-body d-grid gap-3">
    <div class="rh-modal-subject" data-edit-title></div>
    <div><label class="form-label">{{ __('Reason') }} <span class="req">*</span></label><textarea class="form-control" name="admin_note" rows="3" maxlength="500" required></textarea></div>
</div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn rh-btn-out" type="submit"><x-icon name="ban" class="me-1" /> {{ __('Reject request') }}</button></div>
</form>
</x-workspace.drawer>
</div>
<x-workspace.drawer id="promo-prices" :title="__('Prices and payment details')" :description="__('What offices see when they ask for a placement.')" icon="gear" wide>
<form method="post" action="{{ route('platform.marketplace.prices') }}">@csrf
<div class="modal-body d-grid gap-4">
    <fieldset class="rh-field"><legend>{{ __('Prices') }}</legend>
        <div class="table-responsive"><table class="table align-middle mb-2">
            <thead><tr><th></th>@foreach (\App\Models\PromotionRequest::DAYS as $days)<th>{{ __(':count days', ['count' => $days]) }}</th>@endforeach</tr></thead>
            <tbody>@foreach (\App\Models\PromotionRequest::KINDS as $kind)<tr><th class="text-nowrap">{{ __($names[$kind]) }}</th>@foreach (\App\Models\PromotionRequest::DAYS as $days)<td><input class="form-control" type="number" step="0.01" min="0" name="price[{{ $kind }}][{{ $days }}]" value="{{ (float) $prices[$kind][$days] }}" dir="ltr" required aria-label="{{ __($names[$kind]) }} {{ $days }}"></td>@endforeach</tr>@endforeach</tbody>
        </table></div>
        <div class="d-flex align-items-center gap-3"><span class="form-label mb-0">{{ __('Currency') }}</span><div class="rh-chips">@foreach (config('rahnuma.currencies') as $c)<label class="rh-chip"><input type="radio" name="currency" value="{{ $c }}" @checked($priceCurrency === $c)><span>{{ $c }}</span></label>@endforeach</div></div>
    </fieldset>
    <fieldset class="rh-field"><legend>{{ __('Where offices send the money') }}</legend>
        <div class="row g-3">@foreach ($methods as $method => $label)<div class="col-12 col-md-6"><label class="form-label">{{ $label }}</label><input class="form-control" name="pay[{{ $method }}]" value="{{ $instructions[$method] }}" maxlength="300" placeholder="{{ __('Account, wallet number or address') }}"></div>@endforeach</div>
    </fieldset>
</div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save') }}</button></div>
</form>
</x-workspace.drawer>
<x-workspace.drawer id="promote-modal" :title="__('Sell a placement')" :description="__('Days bought while a placement is running are added to its end.')" icon="bullhorn">
<form method="post" action="{{ route('platform.marketplace.promote') }}">@csrf
<input type="hidden" name="kind"><input type="hidden" name="property_id"><input type="hidden" name="agency_id">
<div class="modal-body d-grid gap-3">
    <div class="rh-modal-subject" data-edit-title></div>
    <fieldset class="rh-field"><legend>{{ __('How long') }}</legend>
        <div class="rh-chips">@foreach ([7, 15, 30, 60, 90, 180, 365] as $n)<label class="rh-chip"><input type="radio" name="days" value="{{ $n }}" @checked($n === 30)><span>{{ __(':count days', ['count' => $n]) }}</span></label>@endforeach</div>
    </fieldset>
    <fieldset class="rh-field"><legend>{{ __('Fee received from the office') }}</legend>
        <div class="rh-amount"><input class="form-control" type="number" step="0.01" min="0" name="amount" dir="ltr" placeholder="0" inputmode="decimal"><div class="rh-chips">@foreach (config('rahnuma.currencies') as $c)<label class="rh-chip"><input type="radio" name="currency" value="{{ $c }}" @checked($loop->first)><span>{{ $c }}</span></label>@endforeach</div></div>
        <div class="form-text"><x-icon name="circle-info" /> {{ __('The fee is recorded in money in with today’s date. Leave it empty for a free placement.') }}</div>
    </fieldset>
    <div><label class="form-label">{{ __('Notes') }}</label><input class="form-control" name="notes" maxlength="500"></div>
</div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Start placement') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
@push('scripts')
@include('platform._edit-script')
@endpush
