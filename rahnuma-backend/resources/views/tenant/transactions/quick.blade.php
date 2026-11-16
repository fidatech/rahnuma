@php
    $amount=$transaction->transaction_type==='rent'?$transaction->monthly_rent:$transaction->amount;
    $property=$transaction->property;
@endphp
<div class="modal-body">
    <span class="badge-code mb-2" dir="ltr">{{ $transaction->reference_no }}</span>
    <div class="d-flex justify-content-between align-items-start gap-2"><h4 class="mb-1">{{ $property?->title ?: __('Transaction') }}</h4><span class="badge-soft {{ status_tone($transaction->status) }}">{{ enum_label($transaction->status) }}</span></div>
    <p class="text-muted mb-2">{{ $property?->code ?: '—' }} · {{ deal_label($transaction->transaction_type, true) }}</p>
    <div class="rh-prop-price mb-3" dir="ltr">{{ number_format((float)$amount) }} <small>{{ $transaction->currency }}{{ price_period($transaction->transaction_type) }}</small></div>
    <dl class="facts">
        <div><dt>{{ __('Setta') }}</dt><dd dir="ltr">{{ $transaction->setta_number ?: __('Not entered') }}</dd></div>
        <div><dt>{{ __('Commission') }}</dt><dd dir="ltr">{{ number_format((float)$transaction->commission_total) }} {{ $transaction->currency }}</dd></div>
        <div><dt>{{ __('Date') }}</dt><dd dir="ltr">{{ $transaction->completed_at?->format('d M Y') ?: $transaction->created_at?->format('d M Y') }}</dd></div>
    </dl>
    <h6 class="text-muted small text-uppercase mt-3 mb-2">{{ __('Property owner') }}</h6>
    <div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm avatar-soft-primary">{{ initial($property?->owner?->name) }}</span><div><strong class="d-block text-heading">{{ $property?->owner?->name ?: __('Not assigned') }}</strong><span class="small text-muted" dir="ltr">{{ $property?->owner?->phone ?: __('No phone on record') }}</span></div></div>
    @if($transaction->notes)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Notes') }}</h6><p class="mb-0">{{ \Illuminate\Support\Str::limit($transaction->notes,240) }}</p>@endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <a class="btn btn-primary" href="{{ route('tenant.transactions.show',$transaction) }}"><x-icon name="arrow-right" class="me-1" /> {{ __('Open full deal') }}</a>
</div>
