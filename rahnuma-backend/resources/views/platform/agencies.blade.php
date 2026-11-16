@extends('layouts.workspace')
@section('title', __('Rahnama Tenants'))
@section('workspace')
<x-page-header :title="__('Rahnama Tenants')" icon="building-user" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Rahnama Tenants')]]" :subtitle="__('Onboard, monitor and control agencies subscribed to the SaaS platform.')" />

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Agency directory') }}</h5><div class="small text-muted">{{ __(':count Rahnama tenants', ['count' => number_format($agencies->total())]) }}</div></div></div>
            <div class="card-body">
                <table class="table table-hover w-100 align-middle">
                    <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Plan') }}</th><th>{{ __('License') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                    <tbody>@foreach($agencies as $a)<tr><td><strong class="text-heading">{{ $a->name }}</strong></td><td>{{ $a->plan?->name ?: '—' }}</td><td dir="ltr">{{ $a->license_number ?: '—' }}</td><td><span class="badge-soft {{ $a->is_active ? 'success' : 'danger' }}">{{ $a->is_active ? __('Active') : __('Disabled') }}</span></td><td class="text-end"><form method="post" action="{{ route('platform.agencies.toggle',$a) }}" class="d-inline">@csrf<button class="btn btn-sm {{ $a->is_active ? 'btn-light-secondary' : 'btn-soft-primary' }}">{{ $a->is_active ? __('Disable') : __('Enable') }}</button></form></td></tr>@endforeach</tbody>
                </table>
                {{ $agencies->links('partials.pagination') }}
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <form class="card" method="post" action="{{ route('platform.agencies.store') }}">@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="plus" /></span><div><h5>{{ __('Create Rahnama tenant') }}</h5><small>{{ __('Provision a workspace, owner login and subscription plan.') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                @foreach(['name'=>'Rahnama name','owner_name'=>'Owner name','email'=>'Email','phone'=>'Phone','license_number'=>'License number','password'=>'Temporary password'] as $f=>$l)
                    <div><label class="form-label">{{ __($l) }}@if(in_array($f,['name','owner_name','email','password'])) <span class="req">*</span>@endif</label><input class="form-control" type="{{ $f==='email'?'email':($f==='password'?'password':'text') }}" name="{{ $f }}" value="{{ $f==='password' ? '' : old($f) }}" @if(in_array($f,['email','phone','license_number'])) dir="ltr" @endif @if($f==='password') autocomplete="new-password" @endif {{ in_array($f,['name','owner_name','email','password'])?'required':'' }}></div>
                @endforeach
                <div><label class="form-label">{{ __('Subscription plan') }}</label><select class="form-select" name="plan_id">@foreach($plans as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
            </div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Create tenant') }}</button></div>
        </form>
    </div>
</div>
@endsection
