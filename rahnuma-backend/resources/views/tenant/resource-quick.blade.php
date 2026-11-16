@php
    $title = match($resource) {
        'owners','customers' => $item->name,
        'leads' => $item->customer?->name ?: __('Lead').' #'.$item->id,
        'visits' => $item->customer?->name ?: __('Visit').' #'.$item->id,
        'payments' => enum_label($item->category, __('Payment')),
        'expenses' => enum_label($item->category, __('Expense')),
        default => '#'.$item->id,
    };
    $kind = ['owners'=>'Owner','customers'=>'Customer','leads'=>'Lead','visits'=>'Visit','payments'=>'Payment','expenses'=>'Expense'][$resource] ?? 'Record';
@endphp
<div class="modal-body">
    <span class="badge-code mb-2">{{ __($kind) }} · #{{ $item->id }}</span>
    <h4 class="mb-3">{{ $title }}</h4>

    @if(in_array($resource,['owners','customers']))
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="avatar avatar-lg {{ $resource==='owners' ? 'avatar-soft-primary' : 'avatar-soft-purple' }}">{{ initial($item->name) }}</span>
            <div><strong class="d-block text-heading">{{ $item->name }}</strong><span class="text-muted" dir="ltr">{{ $item->phone ?: __('No phone') }}@if($item->whatsapp) · WhatsApp {{ $item->whatsapp }}@endif</span></div>
        </div>
        <dl class="facts">
            <div><dt>{{ __('Tazkira') }}</dt><dd dir="ltr">{{ $item->tazkira_no ?: '—' }}</dd></div>
            <div><dt>{{ __('Email') }}</dt><dd dir="ltr">{{ $item->email ?: '—' }}</dd></div>
            <div><dt>{{ __('Address') }}</dt><dd>{{ $item->address ?: '—' }}</dd></div>
            <div><dt>{{ __('Added') }}</dt><dd dir="ltr">{{ $item->created_at?->format('d M Y') ?: '—' }}</dd></div>
        </dl>
        @if($item->notes)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Notes') }}</h6><p class="mb-0">{{ $item->notes }}</p>@endif
    @elseif($resource==='leads')
        <dl class="facts">
            <div><dt>{{ __('Stage') }}</dt><dd><span class="badge-soft {{ status_tone($item->status ?: 'new') }}">{{ enum_label($item->status ?: 'new') }}</span></dd></div>
            <div><dt>{{ __('Source') }}</dt><dd>{{ enum_label($item->source ?: 'other') }}</dd></div>
            <div><dt>{{ __('Property') }}</dt><dd>{{ $item->property?->code ? $item->property->code.' · '.$item->property->title : '—' }}</dd></div>
            <div><dt>{{ __('Agent') }}</dt><dd>{{ $item->assignedUser?->name ?: '—' }}</dd></div>
            <div><dt>{{ __('Follow-up') }}</dt><dd dir="ltr">{{ $item->next_follow_up_at?->format('d M Y · H:i') ?: '—' }}</dd></div>
            <div><dt>{{ __('Phone') }}</dt><dd dir="ltr">{{ $item->customer?->phone ?: '—' }}</dd></div>
        </dl>
        @if($item->notes)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Notes') }}</h6><p class="mb-0">{{ $item->notes }}</p>@endif
    @elseif($resource==='visits')
        <dl class="facts">
            <div><dt>{{ __('Schedule') }}</dt><dd dir="ltr">{{ $item->scheduled_at?->format('d M Y · H:i') ?: '—' }}</dd></div>
            <div><dt>{{ __('Status') }}</dt><dd><span class="badge-soft {{ status_tone($item->status ?: 'scheduled') }}">{{ enum_label($item->status ?: 'scheduled') }}</span></dd></div>
            <div><dt>{{ __('Property') }}</dt><dd>{{ $item->property?->code ? $item->property->code.' · '.$item->property->title : '—' }}</dd></div>
            <div><dt>{{ __('Agent') }}</dt><dd>{{ $item->assignedUser?->name ?: '—' }}</dd></div>
        </dl>
        @if($item->outcome)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Outcome') }}</h6><p class="mb-0">{{ $item->outcome }}</p>@endif
        @if($item->notes)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Notes') }}</h6><p class="mb-0">{{ $item->notes }}</p>@endif
    @elseif($resource==='payments')
        <div class="display-6 fw-bold text-heading mb-3" dir="ltr">{{ number_format((float)$item->amount,2) }} <small class="fs-6 text-muted">{{ $item->currency }}</small></div>
        <dl class="facts">
            <div><dt>{{ __('Direction') }}</dt><dd>{{ $item->direction==='out' ? __('Money out') : __('Money in') }}</dd></div>
            <div><dt>{{ __('Method') }}</dt><dd>{{ enum_label($item->method ?: 'cash') }}</dd></div>
            <div><dt>{{ __('Reference') }}</dt><dd dir="ltr">{{ $item->reference_no ?: '—' }}</dd></div>
            <div><dt>{{ __('Date') }}</dt><dd dir="ltr">{{ $item->paid_at?->format('d M Y · H:i') ?: $item->created_at?->format('d M Y') }}</dd></div>
            <div><dt>{{ __('Party') }}</dt><dd>{{ $item->customer?->name ?: ($item->owner?->name ?: '—') }}</dd></div>
            <div><dt>{{ __('Transaction') }}</dt><dd dir="ltr">{{ $item->transaction?->reference_no ?: '—' }}</dd></div>
        </dl>
        @if($item->notes)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Notes') }}</h6><p class="mb-0">{{ $item->notes }}</p>@endif
    @else
        <div class="display-6 fw-bold text-heading mb-3" dir="ltr">{{ number_format((float)$item->amount,2) }} <small class="fs-6 text-muted">{{ $item->currency }}</small></div>
        <dl class="facts">
            <div><dt>{{ __('Category') }}</dt><dd>{{ enum_label($item->category ?: 'other') }}</dd></div>
            <div><dt>{{ __('Method') }}</dt><dd>{{ enum_label($item->method ?: 'cash') }}</dd></div>
            <div><dt>{{ __('Reference') }}</dt><dd dir="ltr">{{ $item->reference_no ?: '—' }}</dd></div>
            <div><dt>{{ __('Date') }}</dt><dd dir="ltr">{{ $item->expense_date?->format('d M Y') ?: $item->created_at?->format('d M Y') }}</dd></div>
        </dl>
        @if($item->description)<h6 class="text-muted small text-uppercase mt-3 mb-1">{{ __('Description') }}</h6><p class="mb-0">{{ $item->description }}</p>@endif
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
</div>
