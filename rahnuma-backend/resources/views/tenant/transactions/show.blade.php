@extends('layouts.workspace')
@section('title', $transaction->reference_no)
@section('workspace')
<x-page-header :title="$transaction->reference_no" icon="handshake" :crumbs="[[__('Transactions'), route('tenant.transactions.index')], [deal_label($transaction->transaction_type, true)], [$transaction->property?->title]]">
    <span class="badge-soft {{ status_tone($transaction->status) }}">{{ enum_label($transaction->status) }}</span>
    <a class="btn btn-outline-soft" target="_blank" rel="noopener" href="{{ route('verify',$transaction->verification_code) }}"><x-icon name="shield-halved" class="me-1" /> {{ __('Verification page') }}</a>
</x-page-header>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card welcome-card">
            <div class="card-body">
                <div class="small text-uppercase fw-semibold opacity-75">{{ __('Deal value') }}</div>
                <div class="display-6 fw-bold" dir="ltr">{{ number_format((float)($transaction->amount ?: $transaction->monthly_rent)) }} <small class="fs-5">{{ $transaction->currency }}</small></div>
                <p class="mb-3">{{ $transaction->transaction_type==='rent' ? __('Monthly rent') : __('Transaction amount') }}</p>
                <div class="row g-3">
                    <div class="col-4"><div class="small opacity-75">{{ __('Commission') }}</div><strong dir="ltr">{{ number_format((float)$transaction->commission_total) }} {{ $transaction->currency }}</strong></div>
                    <div class="col-4"><div class="small opacity-75">{{ __('Setta') }}</div><strong dir="ltr">{{ $transaction->setta_number ?: __('Not entered') }}</strong></div>
                    <div class="col-4"><div class="small opacity-75">{{ __('Completed') }}</div><strong dir="ltr">{{ $transaction->completed_at?->format('d M Y') ?: '—' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="building" /></span><div><h5>{{ $transaction->property?->title }}</h5><small>{{ __('Property') }}</small></div></div></div>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="badge-code">{{ $transaction->property?->code }}</span>
                <span class="text-muted"><x-icon name="location-dot" class="me-1" />{{ $transaction->property?->address ?: __('Location not completed') }}</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="file-contract" /></span><div><h5>{{ __('Agreement details') }}</h5><small>{{ __('Commercial terms') }}</small></div></div></div>
            <div class="card-body">
                <dl class="facts">
                    <div><dt>{{ __('Transaction type') }}</dt><dd>{{ deal_label($transaction->transaction_type, true) }}</dd></div>
                    <div><dt>{{ __('Currency') }}</dt><dd>{{ $transaction->currency }}</dd></div>
                    <div><dt>{{ __('Exchange rate to AFN') }}</dt><dd dir="ltr">{{ $transaction->exchange_rate_to_afn ?: '1.00' }}</dd></div>
                    @if($transaction->transaction_type==='rent')
                        <div><dt>{{ __('Deposit') }}</dt><dd dir="ltr">{{ number_format((float)$transaction->deposit_amount) }} {{ $transaction->currency }}</dd></div>
                        <div><dt>{{ __('Start date') }}</dt><dd dir="ltr">{{ $transaction->start_date?->format('d M Y') ?: '—' }}</dd></div>
                        <div><dt>{{ __('End date') }}</dt><dd dir="ltr">{{ $transaction->end_date?->format('d M Y') ?: '—' }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        @if($transaction->notes)
        <div class="card">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="note-sticky" /></span><div><h5>{{ __('Deal context') }}</h5><small>{{ __('Notes') }}</small></div></div></div>
            <div class="card-body"><p class="mb-0" style="white-space:pre-line">{{ $transaction->notes }}</p></div>
        </div>
        @endif
    </div>

    <aside class="col-12 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold mb-1">{{ __('Internal verification') }}</div>
                <h5>{{ __('Audit-ready record') }}</h5>
                <p class="small text-muted">{{ __('This verifies the transaction inside Rahnuma. It is not Ministry verification unless formally authorized.') }}</p>
                <div class="rh-verify-code mb-3" dir="ltr">{{ $transaction->verification_code }}</div>
                <a class="btn btn-outline-soft w-100" target="_blank" rel="noopener" href="{{ route('verify',$transaction->verification_code) }}">{{ __('Open verification') }}</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="small text-muted text-uppercase fw-semibold mb-2">{{ __('Commission split') }}</div>
                <dl class="facts">
                    <div><dt>{{ __('Party A') }}</dt><dd dir="ltr">{{ number_format((float)$transaction->commission_side_a) }} {{ $transaction->currency }}</dd></div>
                    <div><dt>{{ __('Party B') }}</dt><dd dir="ltr">{{ number_format((float)$transaction->commission_side_b) }} {{ $transaction->currency }}</dd></div>
                    <div><dt>{{ __('Total') }}</dt><dd dir="ltr">{{ number_format((float)$transaction->commission_total) }} {{ $transaction->currency }}</dd></div>
                </dl>
            </div>
        </div>
    </aside>
</div>
@endsection
