@extends('layouts.app')
@section('title', __('Verify a transaction record'))
@section('content')
<div class="rh-narrow">
    <section class="rh-verify">
        <span class="rh-verify-icon"><x-icon name="shield-halved" /></span>
        <h1>{{ __('Verify a transaction record') }}</h1>
        <p>{{ __('Every deal a Rahnama office records in Rahnuma gets a verification code. Enter the code printed on the record to see whether it is in the system and what it says.') }}</p>
        <form class="rh-verify-form" method="get" action="{{ route('verify.form') }}" data-native>
            <label class="visually-hidden" for="verify_code">{{ __('Verification code') }}</label>
            <span class="rh-verify-field"><x-icon name="key" /><input id="verify_code" name="code" value="{{ $code }}" required autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="RHM-XXXXXXXXXXXX" @if(!$tx) autofocus @endif></span>
            <button class="btn btn-gold"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Verify') }}</button>
        </form>
    </section>

    @if ($code !== '' && ! $tx)
        <div class="rh-verify-result is-missing">
            <span class="rh-verify-mark"><x-icon name="circle-xmark" /></span>
            <div><strong>{{ __('No record has this code.') }}</strong><p>{{ __('Check the code on the document and try again. If it still does not match, the document is not a Rahnuma record.') }}</p></div>
        </div>
    @endif

    @if ($tx)
        <div class="rh-verify-result">
            <span class="rh-verify-mark"><x-icon name="circle-check" /></span>
            <div><strong>{{ __('This record is in Rahnuma.') }}</strong><p>{{ __('Compare the details below with the document in your hand.') }}</p></div>
        </div>
        @if(!config('rahnuma.government_mode'))
            <div class="alert alert-warning d-flex gap-2 align-items-start"><x-icon name="triangle-exclamation" class="mt-1" /><div><strong>{{ __('Private platform record.') }}</strong> {{ __('This page is not Government of Afghanistan or Ministry of Justice verification unless an official written integration is activated.') }}</div></div>
        @endif
        <div class="card">
            <div class="card-body">
                <div class="rh-verify-code mb-3" dir="ltr">{{ $tx->verification_code }}</div>
                <dl class="facts">
                    <div><dt>{{ __('Status') }}</dt><dd><span class="badge-soft {{ status_tone($tx->status) }}">{{ enum_label($tx->status) }}</span></dd></div>
                    <div><dt>{{ __('Type') }}</dt><dd>{{ deal_label($tx->transaction_type, true) }}</dd></div>
                    <div><dt>{{ __('Reference') }}</dt><dd dir="ltr">{{ $tx->reference_no }}</dd></div>
                    <div><dt>{{ __('Setta number') }}</dt><dd dir="ltr">{{ $tx->setta_number ?: '—' }}</dd></div>
                    <div><dt>{{ __('Property') }}</dt><dd>{{ $tx->property?->title }}</dd></div>
                    <div><dt>{{ __('Amount') }}</dt><dd dir="ltr">{{ number_format($tx->amount ?: $tx->monthly_rent) }} {{ $tx->currency }}</dd></div>
                    @if ($office)<div><dt>{{ __('Rahnama office') }}</dt><dd>@if($office->is_active && $office->marketplace_enabled)<a href="{{ route('marketplace.agency', $office) }}">{{ $office->name }}</a>@else{{ $office->name }}@endif @if($office->is_verified)<x-icon name="circle-check" class="text-success ms-1" />@endif</dd></div>@endif
                </dl>
            </div>
        </div>
    @endif

    <div class="rh-verify-notes">
        <div><x-icon name="circle-check" /><span>{{ __('It confirms that a deal with this code was recorded by a Rahnama office, with its type, amount, property and status.') }}</span></div>
        <div><x-icon name="circle-info" /><span>{{ __('It does not show the names of the buyer, seller, landlord or tenant.') }}</span></div>
    </div>
</div>
@endsection
