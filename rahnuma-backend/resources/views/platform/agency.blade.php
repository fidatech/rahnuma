@extends('layouts.workspace')
@section('title', $agency->name)
@section('workspace')
@php $owner = $users->firstWhere('role', 'agency_owner') ?? $users->first(); @endphp
<x-page-header :title="$agency->name" icon="building-user" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Rahnama offices'), route('platform.agencies')], [$agency->name]]">
    @if ($agency->marketplace_enabled && $agency->is_active)<a class="btn btn-outline-soft" href="{{ route('marketplace.agency', $agency) }}" target="_blank" rel="noopener"><x-icon name="store" class="me-1" /> {{ __('Public page') }}</a>@endif
    @if ($owner && $owner->is_active)<form method="post" action="{{ route('platform.users.impersonate', $owner) }}" class="d-inline" data-native>@csrf<button class="btn btn-primary"><x-icon name="user-shield" class="me-1" /> {{ __('Sign in as this office') }}</button></form>@endif
</x-page-header>

<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge-soft {{ $agency->is_active ? 'success' : 'danger' }}">{{ $agency->is_active ? __('Active') : __('Disabled') }}</span>
    <span class="badge-soft {{ $agency->is_verified ? 'success' : 'secondary' }}">@if ($agency->is_verified)<x-icon name="circle-check" /> {{ __('Verified') }}@else{{ __('Not verified') }}@endif</span>
    <span class="badge-soft primary">{{ $agency->plan?->name ?: __('No plan') }}</span>
    @if ($agency->trial_ends_at)<span class="badge-soft {{ $agency->trial_ends_at->isFuture() ? 'info' : 'secondary' }}">{{ __('Trial') }} <span dir="ltr">{{ $agency->trial_ends_at->format('Y-m-d') }}</span></span>@endif
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('Properties')" :value="number_format($stats['properties'])" icon="building" tone="blue" :href="route('platform.properties', ['agency_id' => $agency->id])" :note="__(':count public', ['count' => number_format($stats['public'])])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Deals')" :value="number_format($stats['transactions'])" icon="handshake" tone="green" :href="route('platform.transactions', ['agency_id' => $agency->id])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Users')" :value="number_format($stats['users'])" icon="users" tone="violet" :href="route('platform.users', ['agency_id' => $agency->id])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Subscriptions')" :value="number_format($subscriptions->count())" icon="receipt" tone="amber" :href="route('platform.subscriptions', ['agency_id' => $agency->id])" /></div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <form class="card" method="post" action="{{ route('platform.agencies.update', $agency) }}">@csrf @method('PUT')
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="pen" /></span><div><h5>{{ __('Office details') }}</h5><small>{{ __('Change anything about this office.') }}</small></div></div></div>
            <div class="card-body"><div class="row g-3">
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Rahnama name') }} <span class="req">*</span></label><input class="form-control" name="name" value="{{ old('name', $agency->name) }}" required></div>
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Province') }}</label><select class="form-select" name="province_id" data-placeholder="{{ __('Not set') }}"><option value=""></option>@foreach ($provinces as $p)<option value="{{ $p->id }}" @selected(old('province_id', $agency->province_id) == $p->id)>{{ loc_name($p) }}</option>@endforeach</select></div>
                <div class="col-12 col-md-4"><label class="form-label">{{ __('Phone') }}</label><input class="form-control" name="phone" value="{{ old('phone', $agency->phone) }}" dir="ltr"></div>
                <div class="col-12 col-md-4"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" value="{{ old('whatsapp', $agency->whatsapp) }}" dir="ltr"></div>
                <div class="col-12 col-md-4"><label class="form-label">{{ __('Email') }}</label><input class="form-control" type="email" name="email" value="{{ old('email', $agency->email) }}" dir="ltr"></div>
                <div class="col-12"><label class="form-label">{{ __('Address') }}</label><input class="form-control" name="address" value="{{ old('address', $agency->address) }}"></div>
                <div class="col-12 col-md-4"><label class="form-label">{{ __('License number') }}</label><input class="form-control" name="license_number" value="{{ old('license_number', $agency->license_number) }}" dir="ltr"></div>
                <div class="col-12 col-md-4"><label class="form-label">{{ __('License status') }}</label><select class="form-select" name="license_status" data-plain>@foreach (['unverified', 'pending', 'verified', 'suspended', 'expired'] as $s)<option value="{{ $s }}" @selected(old('license_status', $agency->license_status) === $s)>{{ enum_label($s) }}</option>@endforeach</select></div>
                <div class="col-12 col-md-4"><label class="form-label">{{ __('License expires') }}</label><input class="form-control" type="date" name="license_expires_at" value="{{ old('license_expires_at', $agency->license_expires_at?->format('Y-m-d')) }}" dir="ltr"></div>
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Subscription plan') }}</label><select class="form-select" name="plan_id" data-placeholder="{{ __('No plan') }}"><option value=""></option>@foreach ($plans as $p)<option value="{{ $p->id }}" @selected(old('plan_id', $agency->plan_id) == $p->id)>{{ $p->name }}</option>@endforeach</select></div>
                <div class="col-12 col-md-6"><label class="form-label">{{ __('Trial ends') }}</label><input class="form-control" type="date" name="trial_ends_at" value="{{ old('trial_ends_at', $agency->trial_ends_at?->format('Y-m-d')) }}" dir="ltr"></div>
                <div class="col-12"><div class="rh-switches">
                    @foreach (['is_active' => 'Office can sign in and work', 'is_verified' => 'Show the verified mark', 'marketplace_enabled' => 'Listings appear on the marketplace'] as $field => $label)
                        <label class="form-check form-switch"><span>{{ __($label) }}</span><input class="form-check-input" type="checkbox" role="switch" name="{{ $field }}" value="1" @checked(old($field, $agency->$field))></label>
                    @endforeach
                </div></div>
            </div></div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save changes') }}</button></div>
        </form>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Users') }}</h5></div><a class="btn btn-sm btn-outline-soft" href="{{ route('platform.users', ['agency_id' => $agency->id]) }}">{{ __('Manage') }}</a></div>
            <div class="card-body"><div class="rh-list">
                @forelse ($users as $u)
                    <div class="rh-list-row"><span class="avatar avatar-sm avatar-soft-primary">{{ initial($u->name) }}</span><span class="min-w-0 flex-fill"><strong class="d-block text-truncate">{{ $u->name }}</strong><small dir="ltr">{{ $u->email }}</small></span><span class="badge-soft {{ $u->is_active ? 'secondary' : 'danger' }}">{{ enum_label($u->role) }}</span></div>
                @empty
                    <x-workspace.empty icon="users" :title="__('No users.')" :description="__('Add one from the Users page.')" />
                @endforelse
            </div></div>
        </div>
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Subscriptions') }}</h5></div><a class="btn btn-sm btn-outline-soft" href="{{ route('platform.subscriptions', ['agency_id' => $agency->id]) }}">{{ __('Manage') }}</a></div>
            <div class="card-body"><div class="rh-list">
                @forelse ($subscriptions as $s)
                    <div class="rh-list-row"><span class="rh-list-icon"><x-icon name="receipt" /></span><span class="min-w-0 flex-fill"><strong class="d-block">{{ $s->plan?->name }} · {{ enum_label($s->billing_cycle) }}</strong><small dir="ltr">{{ $s->starts_at?->format('Y-m-d') }} → {{ $s->ends_at?->format('Y-m-d') ?: '—' }}</small></span><span class="text-end"><strong class="d-block" dir="ltr">{{ number_format((float) $s->amount) }} {{ $s->currency }}</strong><span class="badge-soft {{ $s->paid_at ? 'success' : 'warning' }}">{{ $s->paid_at ? __('Paid') : __('Unpaid') }}</span></span></div>
                @empty
                    <x-workspace.empty icon="receipt" :title="__('No subscription yet.')" :description="__('Add one from the Subscriptions page.')" />
                @endforelse
            </div></div>
        </div>
        <div class="card rh-danger">
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="triangle-exclamation" /></span><div><h5>{{ __('Danger zone') }}</h5><small>{{ __('These actions affect the whole office.') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div class="d-flex flex-wrap gap-2">
                    <form method="post" action="{{ route('platform.agencies.toggle', $agency) }}">@csrf<button class="btn btn-light-secondary"><x-icon name="ban" class="me-1" /> {{ $agency->is_active ? __('Disable office') : __('Enable office') }}</button></form>
                    <form method="post" action="{{ route('platform.agencies.verify', $agency) }}">@csrf<button class="btn btn-light-secondary"><x-icon name="circle-check" class="me-1" /> {{ $agency->is_verified ? __('Remove verified mark') : __('Mark as verified') }}</button></form>
                </div>
                <form method="post" action="{{ route('platform.agencies.destroy', $agency) }}" data-confirm="{{ __('Delete this office and everything it has entered?') }}" data-confirm-text="{{ __('Properties, customers, deals and accounts of this office are deleted. This cannot be undone.') }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')
                    <label class="form-label">{{ __('To delete, type the name of the office') }}</label>
                    <div class="d-flex gap-2"><input class="form-control" name="confirm_name" placeholder="{{ $agency->name }}" autocomplete="off" required><button class="btn btn-soft-danger text-nowrap"><x-icon name="trash" class="me-1" /> {{ __('Delete office') }}</button></div>
                    @error('confirm_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
