@php
    $title = match($resource) {
        'owners','customers' => $item->name,
        'leads' => $item->customer?->name ?: 'Lead #'.$item->id,
        'visits' => $item->customer?->name ?: 'Visit #'.$item->id,
        'payments' => ucfirst(str_replace('_',' ',$item->category ?: 'Payment')),
        'expenses' => ucfirst(str_replace('_',' ',$item->category ?: 'Expense')),
        default => 'Record #'.$item->id,
    };
@endphp
<div class="quick-view-content quick-view-content--resource">
    <div class="quick-view-body">
        <div class="quick-view-eyebrow">{{ strtoupper(str_replace('_',' ',$resource)) }} · #{{ $item->id }}</div>
        <h2>{{ $title }}</h2>

        @if(in_array($resource,['owners','customers']))
            <div class="quick-view-person quick-view-person--large">
                <div class="mini-avatar">{{ strtoupper(mb_substr($item->name ?: '?',0,1)) }}</div>
                <div><b>{{ $item->name }}</b><small>{{ $item->phone ?: 'No phone' }} @if($item->whatsapp) · WhatsApp {{ $item->whatsapp }} @endif</small></div>
            </div>
            <div class="quick-view-facts quick-view-facts--2">
                <div><span>Tazkira</span><b>{{ $item->tazkira_no ?: '—' }}</b></div>
                <div><span>Email</span><b>{{ $item->email ?: '—' }}</b></div>
                <div><span>Address</span><b>{{ $item->address ?: '—' }}</b></div>
                <div><span>Added</span><b>{{ $item->created_at?->format('d M Y') ?: '—' }}</b></div>
            </div>
            @if($item->notes)<div class="quick-view-section"><span>Notes</span><p>{{ $item->notes }}</p></div>@endif
        @elseif($resource==='leads')
            <div class="quick-view-facts quick-view-facts--2">
                <div><span>Stage</span><b>{{ ucfirst(str_replace('_',' ',$item->status ?: 'new')) }}</b></div>
                <div><span>Source</span><b>{{ ucfirst(str_replace('_',' ',$item->source ?: 'direct')) }}</b></div>
                <div><span>Property</span><b>{{ $item->property?->code ? $item->property->code.' · '.$item->property->title : '—' }}</b></div>
                <div><span>Agent</span><b>{{ $item->assignedUser?->name ?: '—' }}</b></div>
                <div><span>Follow-up</span><b>{{ $item->next_follow_up_at?->format('d M Y · H:i') ?: 'Not scheduled' }}</b></div>
                <div><span>Phone</span><b>{{ $item->customer?->phone ?: '—' }}</b></div>
            </div>
            @if($item->notes)<div class="quick-view-section"><span>Notes</span><p>{{ $item->notes }}</p></div>@endif
        @elseif($resource==='visits')
            <div class="quick-view-facts quick-view-facts--2">
                <div><span>Schedule</span><b>{{ $item->scheduled_at?->format('d M Y · H:i') ?: '—' }}</b></div>
                <div><span>Status</span><b>{{ ucfirst(str_replace('_',' ',$item->status ?: 'scheduled')) }}</b></div>
                <div><span>Property</span><b>{{ $item->property?->code ? $item->property->code.' · '.$item->property->title : '—' }}</b></div>
                <div><span>Agent</span><b>{{ $item->assignedUser?->name ?: '—' }}</b></div>
            </div>
            @if($item->outcome)<div class="quick-view-section"><span>Outcome</span><p>{{ $item->outcome }}</p></div>@endif
            @if($item->notes)<div class="quick-view-section"><span>Notes</span><p>{{ $item->notes }}</p></div>@endif
        @elseif($resource==='payments')
            <div class="quick-view-price">{{ number_format((float)$item->amount,2) }} <small>{{ $item->currency }}</small></div>
            <div class="quick-view-facts quick-view-facts--2">
                <div><span>Direction</span><b>{{ $item->direction==='out'?'Money out':'Money in' }}</b></div>
                <div><span>Method</span><b>{{ ucfirst($item->method ?: 'cash') }}</b></div>
                <div><span>Reference</span><b>{{ $item->reference_no ?: '—' }}</b></div>
                <div><span>Date</span><b>{{ $item->paid_at?->format('d M Y · H:i') ?: $item->created_at?->format('d M Y') }}</b></div>
                <div><span>Party</span><b>{{ $item->customer?->name ?: ($item->owner?->name ?: '—') }}</b></div>
                <div><span>Transaction</span><b>{{ $item->transaction?->reference_no ?: '—' }}</b></div>
            </div>
            @if($item->notes)<div class="quick-view-section"><span>Notes</span><p>{{ $item->notes }}</p></div>@endif
        @else
            <div class="quick-view-price">{{ number_format((float)$item->amount,2) }} <small>{{ $item->currency }}</small></div>
            <div class="quick-view-facts quick-view-facts--2">
                <div><span>Category</span><b>{{ ucfirst(str_replace('_',' ',$item->category ?: 'Other')) }}</b></div>
                <div><span>Method</span><b>{{ ucfirst($item->method ?: 'cash') }}</b></div>
                <div><span>Reference</span><b>{{ $item->reference_no ?: '—' }}</b></div>
                <div><span>Date</span><b>{{ $item->expense_date?->format('d M Y') ?: $item->created_at?->format('d M Y') }}</b></div>
            </div>
            @if($item->description)<div class="quick-view-section"><span>Description</span><p>{{ $item->description }}</p></div>@endif
        @endif
    </div>
</div>
