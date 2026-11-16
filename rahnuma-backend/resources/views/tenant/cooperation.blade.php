@extends('layouts.workspace')
@section('title', __('Cross-Rahnama Cooperation'))
@section('workspace')
<x-page-header :title="__('Cross-Rahnama Cooperation')" icon="people-arrows" :subtitle="__('Share opportunities without exposing private customer information before both offices agree to work together.')">
    <button class="btn btn-primary" type="button" data-modal-open="cooperation-drawer"><x-icon name="plus" class="me-1" /> {{ __('Request cooperation') }}</button>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><x-stat :label="__('Requests')" :value="number_format($requests->total())" icon="people-arrows" tone="blue" :note="__('Incoming + outgoing')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Public partner inventory')" :value="number_format($publicPropertyCount)" icon="building" tone="green" :note="__('Available to cooperate')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Your customers')" :value="number_format($customerCount)" icon="users" tone="violet" :note="__('Private until accepted')" /></div>
</div>

<div class="card">
    <div class="card-body">
    @if($requests->isEmpty())
        <x-workspace.empty icon="people-arrows" :title="__('No cooperation requests')" :description="__('When another Rahnama has the property your customer needs, propose a protected commission-sharing collaboration.')" />
    @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Property') }}</th><th>{{ __('Direction') }}</th><th>{{ __('Proposed split') }}</th><th>{{ __('Created') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach($requests as $c)
                @php($incoming = $c->target_agency_id===auth()->user()->agency_id)
                <tr>
                    <td><strong class="d-block text-heading">{{ $c->property?->title ?: __('Property unavailable') }}</strong><span class="small text-muted">{{ $c->message ?: __('No message was added.') }}</span></td>
                    <td><span class="badge-soft {{ $incoming ? 'info' : 'primary' }}"><x-icon :name="$incoming ? 'arrow-down' : 'arrow-up'" /> {{ $incoming ? __('Incoming request') : __('Outgoing request') }}</span></td>
                    <td class="text-nowrap" dir="ltr">{{ $c->requesting_share_percent }}% / {{ $c->target_share_percent }}%</td>
                    <td class="text-nowrap" dir="ltr">{{ $c->created_at?->format('d M Y') }}</td>
                    <td><span class="badge-soft {{ status_tone($c->status) }}">{{ enum_label($c->status) }}</span></td>
                    <td class="text-end text-nowrap">
                        @if($incoming && $c->status==='pending')
                            <form method="post" action="{{ route('tenant.cooperation.respond',$c->id) }}" class="d-inline-flex gap-2">@csrf<button class="btn btn-sm btn-primary" name="status" value="accepted">{{ __('Accept') }}</button><button class="btn btn-sm btn-light-secondary" name="status" value="rejected">{{ __('Decline') }}</button></form>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $requests->links('partials.pagination') }}
    @endif
    </div>
</div>
@endsection
@push('modals')
<x-workspace.drawer id="cooperation-drawer" :title="__('Request cooperation')" :description="__('Choose a public property of another Rahnama and your own customer. Private details remain protected until the workflow allows sharing.')" icon="people-arrows" :wide="true">
<form method="post" action="{{ route('tenant.cooperation.store') }}">@csrf
<div class="modal-body"><div class="row g-3">
    <div class="col-12"><label class="form-label">{{ __('Partner property') }} <span class="req">*</span></label><select class="form-select" name="property_id" required data-remote-url="{{ route('tenant.cooperation.lookup-properties') }}" data-placeholder="{{ __('Search partner property') }}"><option value=""></option></select></div>
    <div class="col-12"><label class="form-label">{{ __('Your customer') }} <span class="req">*</span></label><select class="form-select" name="customer_id" required data-smart-resource="customer" data-remote-url="{{ route('tenant.lookup','customers') }}" data-placeholder="{{ __('Search your customer') }}"><option value=""></option></select></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Your commission share %') }} <span class="req">*</span></label><input class="form-control" type="number" name="requesting_share_percent" value="50" min="0" max="100" required dir="ltr"></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Partner share') }}</label><input class="form-control" value="{{ __('Calculated automatically') }}" disabled></div>
    <div class="col-12"><label class="form-label">{{ __('Message') }}</label><textarea class="form-control" name="message" rows="3" placeholder="{{ __('Explain the customer need and proposed cooperation') }}"></textarea></div>
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="paper-plane" class="me-1" /> {{ __('Send request') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
