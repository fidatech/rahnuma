@extends('layouts.workspace')
@section('title', __('System status'))
@section('workspace')
<x-page-header :title="__('System status')" icon="server" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('System status')]]" :subtitle="__('How the installation is set up and how much it holds.')">
    <form method="post" action="{{ route('platform.system.cache') }}" class="d-inline" data-confirm="{{ __('Clear the cache?') }}" data-confirm-text="{{ __('Pages rebuild their saved data on the next visit. Nobody is signed out unless sessions are kept in the cache.') }}" data-confirm-ok="{{ __('Clear cache') }}" data-confirm-style="primary">@csrf<button class="btn btn-primary"><x-icon name="broom" class="me-1" /> {{ __('Clear cache') }}</button></form>
</x-page-header>

<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header"><h5 class="card-title mb-0">{{ __('Checks') }}</h5></div>
            <div class="card-body"><div class="rh-list">
                @foreach ($checks as $label => $ok)
                    <div class="rh-list-row"><span class="rh-list-icon {{ $ok ? 'is-green' : 'is-red' }}"><x-icon :name="$ok ? 'circle-check' : 'triangle-exclamation'" /></span><span class="flex-fill fw-bold text-heading">{{ __($label) }}</span><span class="badge-soft {{ $ok ? 'success' : 'danger' }}">{{ $ok ? __('OK') : __('Needs attention') }}</span></div>
                @endforeach
            </div></div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header"><h5 class="card-title mb-0">{{ __('Installation') }}</h5></div>
            <div class="card-body"><dl class="facts">@foreach ($info as $label => $text)<div><dt>{{ __($label) }}</dt><dd dir="ltr">{{ $text }}</dd></div>@endforeach</dl></div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header"><h5 class="card-title mb-0">{{ __('Records') }}</h5></div>
            <div class="card-body"><dl class="facts">
                @foreach ($tables as $table => $count)<div><dt dir="ltr">{{ $table }}</dt><dd dir="ltr">{{ $count === null ? '—' : number_format($count) }}</dd></div>@endforeach
                <div><dt>{{ __('Property photos on disk') }}</dt><dd dir="ltr">{{ $photos === null ? '—' : number_format($photos / 1048576, 1).' MB' }}</dd></div>
            </dl></div>
        </div>
    </div>
</div>
@endsection
