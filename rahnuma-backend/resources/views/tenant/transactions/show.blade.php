@extends('layouts.workspace')
@section('workspace')
<div class="deal-page-head dossier-head">
    <div><div class="deal-page-head__back"><a href="{{ route('tenant.transactions.index') }}">← Transactions</a></div><div class="deal-title-row"><h1>{{ $transaction->reference_no }}</h1><span class="badge {{ $transaction->status==='completed'?'success':'neutral' }}">{{ $transaction->status }}</span></div><p>{{ $transaction->transaction_type==='bay_wafa'?'Mortgage / بیع وفا':ucfirst($transaction->transaction_type) }} · {{ $transaction->property?->title }}</p></div>
    <div class="deal-head-actions"><a class="btn secondary" target="_blank" href="{{ route('verify',$transaction->verification_code) }}">Verification page</a></div>
</div>

<div class="dossier-shell">
    <main class="dossier-main">
        <section class="dossier-hero">
            <div><span class="dossier-kicker">Deal value</span><strong>{{ number_format((float)($transaction->amount ?: $transaction->monthly_rent)) }} <small>{{ $transaction->currency }}</small></strong><p>{{ $transaction->transaction_type==='rent'?'Monthly rent':'Transaction amount' }}</p></div>
            <div class="dossier-hero__facts"><div><span>Commission</span><b>{{ number_format((float)$transaction->commission_total) }} {{ $transaction->currency }}</b></div><div><span>Setta</span><b>{{ $transaction->setta_number ?: 'Not entered' }}</b></div><div><span>Completed</span><b>{{ $transaction->completed_at?->format('d M Y') ?: '—' }}</b></div></div>
        </section>

        <section class="dossier-section"><div class="dossier-section__head"><span>Property</span><h2>{{ $transaction->property?->title }}</h2></div><div class="dossier-property"><div class="dossier-property__mark">{{ strtoupper(substr($transaction->property?->property_type?:'P',0,1)) }}</div><div><b>{{ $transaction->property?->code }}</b><p>{{ $transaction->property?->address ?: 'Location not completed' }}</p></div></div></section>

        <section class="dossier-section"><div class="dossier-section__head"><span>Commercial terms</span><h2>Agreement details</h2></div><div class="dossier-grid"><div><span>Transaction type</span><b>{{ $transaction->transaction_type==='bay_wafa'?'Mortgage / بیع وفا':ucfirst($transaction->transaction_type) }}</b></div><div><span>Currency</span><b>{{ $transaction->currency }}</b></div><div><span>Exchange rate to AFN</span><b>{{ $transaction->exchange_rate_to_afn ?: '1.00' }}</b></div>@if($transaction->transaction_type==='rent')<div><span>Deposit</span><b>{{ number_format((float)$transaction->deposit_amount) }} {{ $transaction->currency }}</b></div><div><span>Start date</span><b>{{ $transaction->start_date?->format('d M Y') ?: '—' }}</b></div><div><span>End date</span><b>{{ $transaction->end_date?->format('d M Y') ?: '—' }}</b></div>@endif</div></section>

        @if($transaction->notes)<section class="dossier-section"><div class="dossier-section__head"><span>Notes</span><h2>Deal context</h2></div><p class="dossier-notes">{{ $transaction->notes }}</p></section>@endif
    </main>
    <aside class="dossier-rail">
        <div class="deal-rail-card"><div class="rail-label">Internal verification</div><h3>Audit-ready record</h3><p>This verifies the transaction inside Rahnuma. It is not Ministry verification unless formally authorized.</p><div class="verification-code">{{ $transaction->verification_code }}</div><a class="btn secondary block" target="_blank" href="{{ route('verify',$transaction->verification_code) }}">Open verification</a></div>
        <div class="deal-rail-card deal-rail-card--quiet"><div class="rail-label">Commission split</div><div class="commission-lines-v4"><div><span>Party A</span><b>{{ number_format((float)$transaction->commission_side_a) }} {{ $transaction->currency }}</b></div><div><span>Party B</span><b>{{ number_format((float)$transaction->commission_side_b) }} {{ $transaction->currency }}</b></div><div><span>Total</span><b>{{ number_format((float)$transaction->commission_total) }} {{ $transaction->currency }}</b></div></div></div>
    </aside>
</div>
@endsection
