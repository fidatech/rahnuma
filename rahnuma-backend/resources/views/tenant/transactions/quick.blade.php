@php
    $amount=$transaction->transaction_type==='rent'?$transaction->monthly_rent:$transaction->amount;
    $property=$transaction->property;
@endphp
<div class="quick-view-content">
    <div class="quick-view-body quick-view-body--top">
        <div class="quick-view-eyebrow">{{ $transaction->reference_no }}</div>
        <div class="quick-view-title-row"><h2>{{ $property?->title ?: 'Transaction' }}</h2><span class="badge {{ $transaction->status==='completed'?'success':'neutral' }}">{{ $transaction->status }}</span></div>
        <p class="quick-view-location">{{ $property?->code ?: 'No property code' }} · {{ $transaction->transaction_type==='bay_wafa'?'Mortgage / بیع وفا':ucfirst($transaction->transaction_type) }}</p>
        <div class="quick-view-price">{{ number_format((float)$amount) }} <small>{{ $transaction->currency }}{{ $transaction->transaction_type==='rent'?' / month':'' }}</small></div>
        <div class="quick-view-facts quick-view-facts--3">
            <div><span>Setta</span><b>{{ $transaction->setta_number ?: 'Not entered' }}</b></div>
            <div><span>Commission</span><b>{{ number_format((float)$transaction->commission_total) }} {{ $transaction->currency }}</b></div>
            <div><span>Date</span><b>{{ $transaction->completed_at?->format('d M Y') ?: $transaction->created_at?->format('d M Y') }}</b></div>
        </div>
        <div class="quick-view-section"><span>Property owner</span><div class="quick-view-person"><div class="mini-avatar">{{ strtoupper(mb_substr($property?->owner?->name ?: '?',0,1)) }}</div><div><b>{{ $property?->owner?->name ?: 'Not assigned' }}</b><small>{{ $property?->owner?->phone ?: 'No phone on record' }}</small></div></div></div>
        @if($transaction->notes)<div class="quick-view-section"><span>Notes</span><p>{{ \Illuminate\Support\Str::limit($transaction->notes,240) }}</p></div>@endif
    </div>
    <div class="quick-view-actions"><a class="btn" href="{{ route('tenant.transactions.show',$transaction) }}">Open full deal</a></div>
</div>
