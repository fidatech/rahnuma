@extends('layouts.workspace')
@section('title', __('Sponsor a property'))
@section('workspace')
@php
    $kinds = ['sponsored' => ['Sponsored', 'bullhorn', 'First on the home page and in search results, with a Sponsored badge.'], 'featured' => ['Featured', 'star', 'In the featured collection, with a Featured badge.']];
    $methods = ['hesabpay' => 'HesabPay', 'aurumpay' => 'AurumPay', 'mobile_money' => __('Mobile money'), 'cash' => __('Cash')];
    $tones = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
    $oldKind = old('kind', 'sponsored'); $oldDays = (int) old('days', 30); $oldMethod = old('payment_method', 'hesabpay');
@endphp
<x-page-header :title="__('Sponsor a property')" icon="bullhorn" :subtitle="__('Ask for a paid placement on the marketplace. It starts when the administrator confirms your payment.')" />

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <form class="card mb-0" method="post" action="{{ route('tenant.promotions.store') }}" enctype="multipart/form-data" data-native id="promoForm">@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="bullhorn" /></span><div><h5>{{ __('New request') }}</h5><small>{{ __('Choose the property, the placement and the days, then tell us how you paid.') }}</small></div></div></div>
            <div class="card-body d-grid gap-4">
                @if ($properties->isEmpty())
                    <x-workspace.empty icon="building" :title="__('No property is on the marketplace yet.')" :description="__('Set a property to Public first, then come back to sponsor it.')" />
                @else
                <div>
                    <label class="form-label" for="promo_property">1. {{ __('Property') }} <span class="req">*</span></label>
                    <select class="form-select @error('property_id') is-invalid @enderror" name="property_id" id="promo_property" required data-placeholder="{{ __('Choose a property') }}"><option value=""></option>@foreach ($properties as $p)<option value="{{ $p->id }}" @selected((int) old('property_id', $chosen) === $p->id)>{{ $p->code }} · {{ $p->title }}@if ($p->is_sponsored) ({{ __('Sponsored') }})@elseif ($p->visibility === 'featured') ({{ __('Featured') }})@endif</option>@endforeach</select>
                    @error('property_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <fieldset class="rh-field"><legend>2. {{ __('Placement') }}</legend>
                    <div class="rh-choice-grid">
                        @foreach ($kinds as $kind => [$label, $icon, $text])
                            <label class="rh-choice"><input type="radio" name="kind" value="{{ $kind }}" @checked($oldKind === $kind)><span><x-icon :name="$icon" /><strong>{{ __($label) }}</strong><small>{{ __($text) }}</small></span></label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="rh-field"><legend>3. {{ __('How long') }}</legend>
                    <div class="rh-price-grid">
                        @foreach (\App\Models\PromotionRequest::DAYS as $days)
                            <label class="rh-price"><input type="radio" name="days" value="{{ $days }}" @checked($oldDays === $days)>
                                <span>
                                    <em>{{ __(':count days', ['count' => $days]) }}</em>
                                    @foreach ($kinds as $kind => $meta)<strong data-price-kind="{{ $kind }}" @if ($oldKind !== $kind) hidden @endif dir="ltr">{{ number_format($prices[$kind][$days]) }} <small>{{ $currency }}</small></strong>@endforeach
                                    @foreach ($kinds as $kind => $meta)<small data-price-kind="{{ $kind }}" @if ($oldKind !== $kind) hidden @endif dir="ltr">{{ number_format($prices[$kind][$days] / $days, 1) }} {{ $currency }} / {{ __('day') }}</small>@endforeach
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="rh-field"><legend>4. {{ __('How you paid') }}</legend>
                    <div class="rh-chips">@foreach ($methods as $method => $label)<label class="rh-chip"><input type="radio" name="payment_method" value="{{ $method }}" @checked($oldMethod === $method)><span>{{ $label }}</span></label>@endforeach</div>
                    @foreach ($methods as $method => $label)
                        <div class="rh-pay-note" data-pay-method="{{ $method }}" @if ($oldMethod !== $method) hidden @endif><x-icon name="circle-info" /> <span>{{ $instructions[$method] !== '' ? $instructions[$method] : __('Ask the administrator where to send the payment, then enter the proof below.') }}</span></div>
                    @endforeach
                </fieldset>

                <div class="row g-3">
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Transaction number') }}</label><input class="form-control @error('payment_reference') is-invalid @enderror" name="payment_reference" value="{{ old('payment_reference') }}" dir="ltr" maxlength="120">@error('payment_reference')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Receipt (photo or PDF)') }}</label><input class="form-control @error('receipt') is-invalid @enderror" type="file" name="receipt" accept=".jpg,.jpeg,.png,.webp,.pdf">@error('receipt')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-label">{{ __('Notes') }}</label><input class="form-control" name="notes" value="{{ old('notes') }}" maxlength="500"></div>
                </div>
                @endif
            </div>
            @unless ($properties->isEmpty())
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="rh-total"><span>{{ __('To pay') }}</span>@foreach ($kinds as $kind => $meta)@foreach (\App\Models\PromotionRequest::DAYS as $days)<strong data-total="{{ $kind }}-{{ $days }}" @if ($oldKind !== $kind || $oldDays !== $days) hidden @endif dir="ltr">{{ number_format($prices[$kind][$days]) }} {{ $currency }}</strong>@endforeach @endforeach</div>
                <button class="btn btn-primary btn-lg"><x-icon name="paper-plane" class="me-1" /> {{ __('Send request') }}</button>
            </div>
            @endunless
        </form>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Your requests') }}</h5><div class="small text-muted">{{ __('Waiting, approved and rejected.') }}</div></div></div>
            <div class="card-body">
                @if ($requests->isEmpty())
                    <x-workspace.empty icon="bullhorn" :title="__('No requests yet.')" :description="__('Your sponsorship requests will show here with their status.')" />
                @else
                <div class="rh-list">
                    @foreach ($requests as $q)
                        <div class="rh-list-row align-items-start">
                            <span class="rh-list-icon {{ ['pending' => 'is-amber', 'approved' => 'is-green', 'rejected' => 'is-red'][$q->status] ?? '' }}"><x-icon :name="$q->kind === 'featured' ? 'star' : 'bullhorn'" /></span>
                            <span class="min-w-0 flex-fill">
                                <strong class="d-block text-truncate">{{ $q->property?->title ?: '—' }}</strong>
                                <small class="d-block">{{ __($kinds[$q->kind][0] ?? $q->kind) }} · {{ __(':count days', ['count' => $q->days]) }} · <span dir="ltr">{{ number_format((float) $q->amount) }} {{ $q->currency }}</span></small>
                                <small class="d-block">{{ $methods[$q->payment_method] ?? $q->payment_method }}@if ($q->payment_reference) · <span dir="ltr">{{ $q->payment_reference }}</span>@endif · <span dir="ltr">{{ $q->created_at?->format('Y-m-d') }}</span></small>
                                @if ($q->admin_note)<small class="d-block text-heading">{{ $q->admin_note }}</small>@endif
                                @if ($q->status === 'pending')<form method="post" action="{{ route('tenant.promotions.destroy', $q) }}" class="mt-1" data-confirm="{{ __('Withdraw this request?') }}" data-confirm-ok="{{ __('Withdraw') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-light-secondary">{{ __('Withdraw') }}</button></form>@endif
                            </span>
                            <span class="badge-soft {{ $tones[$q->status] ?? 'secondary' }}">{{ enum_label($q->status) }}</span>
                        </div>
                    @endforeach
                </div>
                {{ $requests->links('partials.pagination') }}
                @endif
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(() => {
 const form=document.getElementById('promoForm'); if(!form) return;
 const picked=name=>form.querySelector('input[name="'+name+'"]:checked')?.value;
 // prices follow the placement, the total follows both, and the way to pay shows its own note
 const sync=()=>{
  const kind=picked('kind'), days=picked('days'), method=picked('payment_method');
  form.querySelectorAll('[data-price-kind]').forEach(el=>{ el.hidden=el.dataset.priceKind!==kind; });
  form.querySelectorAll('[data-total]').forEach(el=>{ el.hidden=el.dataset.total!==kind+'-'+days; });
  form.querySelectorAll('[data-pay-method]').forEach(el=>{ el.hidden=el.dataset.payMethod!==method; });
 };
 form.addEventListener('change',sync); sync();
})();
</script>
@endpush
@endsection
