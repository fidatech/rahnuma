@extends('layouts.app')
@section('body-class','workspace-page')
@section('content')
<div class="workspace-shell">
    <aside class="workspace-sidebar">
        <div class="workspace-sidebar__top">
            <a class="agency-lockup" href="{{ route('tenant.settings.edit') }}">
                <div class="agency-lockup__logo">@if(auth()->user()->agency?->logo_path)<img src="{{ asset('storage/'.auth()->user()->agency->logo_path) }}" alt="">@else{{ strtoupper(substr(auth()->user()->agency?->name ?: 'R',0,1)) }}@endif</div>
                <div><div class="agency-lockup__name">{{ auth()->user()->agency?->name }}</div><div class="agency-lockup__meta">Rahnama workspace</div></div>
            </a>
        </div>
        <div class="workspace-sidebar__nav">
            <div class="nav-group"><div class="nav-group__label">Overview</div><a class="{{ request()->routeIs('tenant.dashboard')?'active':'' }}" href="{{ route('tenant.dashboard') }}">Dashboard <span>›</span></a></div>
            <div class="nav-group"><div class="nav-group__label">Property & CRM</div>
                <a class="{{ request()->routeIs('tenant.properties.*')?'active':'' }}" href="{{ route('tenant.properties.index') }}">Properties <span>›</span></a>
                <a class="{{ request()->is('workspace/owners*')?'active':'' }}" href="{{ route('tenant.resource.index','owners') }}">Landlords & Owners <span>›</span></a>
                <a class="{{ request()->is('workspace/customers*')?'active':'' }}" href="{{ route('tenant.resource.index','customers') }}">Customers <span>›</span></a>
                <a class="{{ request()->routeIs('tenant.requirements.*')?'active':'' }}" href="{{ route('tenant.requirements.index') }}">Requirements & Matching <span>›</span></a>
                <a class="{{ request()->is('workspace/leads*')?'active':'' }}" href="{{ route('tenant.resource.index','leads') }}">Leads <span>›</span></a>
                <a class="{{ request()->is('workspace/visits*')?'active':'' }}" href="{{ route('tenant.resource.index','visits') }}">Visits & Follow-ups <span>›</span></a>
            </div>
            <div class="nav-group"><div class="nav-group__label">Deals & Finance</div>
                <a class="{{ request()->routeIs('tenant.transactions.*')?'active':'' }}" href="{{ route('tenant.transactions.index') }}">Sales / Rent / Mortgage <span>›</span></a>
                <a class="{{ request()->is('workspace/payments*')?'active':'' }}" href="{{ route('tenant.resource.index','payments') }}">Payments <span>›</span></a>
                <a class="{{ request()->is('workspace/expenses*')?'active':'' }}" href="{{ route('tenant.resource.index','expenses') }}">Expenses <span>›</span></a>
                <a class="{{ request()->routeIs('tenant.accounting')||request()->routeIs('tenant.ledger')?'active':'' }}" href="{{ route('tenant.accounting') }}">Accounting & Reports <span>›</span></a>
            </div>
            <div class="nav-group"><div class="nav-group__label">Network & Administration</div>
                <a class="{{ request()->routeIs('tenant.cooperation.*')?'active':'' }}" href="{{ route('tenant.cooperation.index') }}">Cross-Rahnama Cooperation <span>›</span></a>
                <a class="{{ request()->routeIs('tenant.staff.*')?'active':'' }}" href="{{ route('tenant.staff.index') }}">Team & Roles <span>›</span></a>
                <a class="{{ request()->routeIs('tenant.settings.*')?'active':'' }}" href="{{ route('tenant.settings.edit') }}">System & Company Settings <span>›</span></a>
            </div>
        </div>
        <div class="workspace-sidebar__bottom">
            <a class="sidebar-profile" href="{{ route('tenant.settings.edit') }}#profile">
                <span class="sidebar-profile__avatar">@if(auth()->user()->avatar_path)<img src="{{ asset('storage/'.auth()->user()->avatar_path) }}">@else{{ strtoupper(substr(auth()->user()->name,0,1)) }}@endif</span>
                <span><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->job_title ?: str_replace('_',' ',auth()->user()->role) }}</small></span><i>⚙</i>
            </a>
        </div>
    </aside>
    <section class="workspace-main">@yield('workspace')</section>
</div>
@endsection
