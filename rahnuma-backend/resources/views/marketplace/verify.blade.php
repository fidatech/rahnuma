@extends('layouts.app')
@section('title', __('Transaction Verification Record'))
@section('content')
<div class="rh-narrow">
    <x-page-header :title="__('Transaction Verification Record')" icon="shield-halved" />
    @if(!config('rahnuma.government_mode'))
        <div class="alert alert-warning d-flex gap-2 align-items-start"><x-icon name="triangle-exclamation" class="mt-1" /><div><strong>{{ __('Private platform record.') }}</strong> {{ __('This page is not Government of Afghanistan or Ministry of Justice verification unless an official written integration is activated.') }}</div></div>
    @endif
    <div class="card">
        <div class="card-body">
            <div class="rh-verify-code mb-3" dir="ltr">{{ $tx->verification_code }}</div>
            <dl class="facts">
                <div><dt>{{ __('Verification code') }}</dt><dd dir="ltr">{{ $tx->verification_code }}</dd></div>
                <div><dt>{{ __('Reference') }}</dt><dd dir="ltr">{{ $tx->reference_no }}</dd></div>
                <div><dt>{{ __('Type') }}</dt><dd>{{ deal_label($tx->transaction_type, true) }}</dd></div>
                <div><dt>{{ __('Setta number') }}</dt><dd dir="ltr">{{ $tx->setta_number ?: '—' }}</dd></div>
                <div><dt>{{ __('Property') }}</dt><dd>{{ $tx->property?->title }}</dd></div>
                <div><dt>{{ __('Amount') }}</dt><dd dir="ltr">{{ number_format($tx->amount ?: $tx->monthly_rent) }} {{ $tx->currency }}</dd></div>
                <div><dt>{{ __('Status') }}</dt><dd><span class="badge-soft {{ status_tone($tx->status) }}">{{ enum_label($tx->status) }}</span></dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
