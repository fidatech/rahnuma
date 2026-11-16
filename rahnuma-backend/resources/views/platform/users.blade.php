@extends('layouts.workspace')
@section('title', __('Users'))
@section('workspace')
<x-page-header :title="__('Users')" icon="users" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Users')]]" :subtitle="__('Every account in the system: office staff, the authority and administrators.')">
    <button class="btn btn-primary" type="button" data-modal-open="user-create"><x-icon name="user-plus" class="me-1" /> {{ __('Add user') }}</button>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('All users')" :value="number_format($counts['all'])" icon="users" tone="blue" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Administrators')" :value="number_format($counts['admins'])" icon="user-shield" tone="violet" :href="route('platform.users', ['role' => 'platform_admin'])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Authority accounts')" :value="number_format($counts['government'])" icon="landmark" tone="green" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Disabled')" :value="number_format($counts['disabled'])" icon="ban" tone="red" :href="route('platform.users', ['status' => 'disabled'])" /></div>
</div>

<form class="card" method="get">
    <div class="card-body"><div class="row g-2 align-items-end">
        <div class="col-12 col-lg"><label class="form-label">{{ __('Search') }}</label><div class="input-icon"><x-icon name="magnifying-glass" /><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Name or email') }}"></div></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Role') }}</label><select class="form-select" name="role" data-placeholder="{{ __('Any role') }}"><option value=""></option>@foreach ($roles as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ enum_label($role) }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-3"><label class="form-label">{{ __('Office') }}</label><select class="form-select" name="agency_id" data-placeholder="{{ __('Any office') }}"><option value=""></option>@foreach ($agencies as $a)<option value="{{ $a->id }}" @selected(request('agency_id') == $a->id)>{{ $a->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Status') }}</label><select class="form-select" name="status" data-placeholder="{{ __('Any') }}"><option value=""></option><option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option><option value="disabled" @selected(request('status') === 'disabled')>{{ __('Disabled') }}</option></select></div>
        <div class="col-6 col-lg-auto d-flex gap-2"><button class="btn btn-primary flex-fill"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button>@if (request()->hasAny(['q', 'role', 'agency_id', 'status']))<a class="btn btn-outline-soft" href="{{ route('platform.users') }}">{{ __('Reset') }}</a>@endif</div>
    </div></div>
</form>

<div class="card">
    <div class="card-body">
        @if ($users->isEmpty())
            <x-workspace.empty icon="users" :title="__('No users found.')" :description="__('Try another search.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('User') }}</th><th>{{ __('Role') }}</th><th>{{ __('Office') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                @php $self = $u->id === auth()->id(); @endphp
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm avatar-soft-primary">{{ initial($u->name) }}</span><div class="min-w-0"><strong class="d-block text-heading">{{ $u->name }} @if ($self)<span class="badge-soft primary">{{ __('You') }}</span>@endif</strong><span class="small text-muted" dir="ltr">{{ $u->email }}</span></div></div></td>
                    <td><span class="badge-soft {{ $u->role === 'platform_admin' ? 'primary' : ($u->isGovernment() ? 'success' : 'secondary') }}">{{ enum_label($u->role) }}</span></td>
                    <td>@if ($u->agency)<a href="{{ route('platform.agencies.show', $u->agency) }}">{{ $u->agency->name }}</a>@else—@endif</td>
                    <td><span class="badge-soft {{ $u->is_active ? 'success' : 'danger' }}">{{ $u->is_active ? __('Active') : __('Disabled') }}</span></td>
                    <td class="text-end text-nowrap">
                        <div class="rh-actions">
                            @if ($u->role !== 'platform_admin' && $u->is_active)<form method="post" action="{{ route('platform.users.impersonate', $u) }}" data-native>@csrf<button class="btn btn-sm btn-soft-primary" title="{{ __('Sign in as this user') }}"><x-icon name="user-shield" /> <span>{{ __('Sign in as') }}</span></button></form>@endif
                            <button class="btn btn-sm btn-light-secondary" type="button" title="{{ __('Edit') }}" data-modal="user-edit" data-action="{{ route('platform.users.update', $u) }}" data-title="{{ $u->name }}" data-edit="{{ json_encode(['name' => $u->name, 'email' => $u->email, 'role' => $u->role, 'agency_id' => $u->agency_id, 'locale' => $u->locale]) }}"><x-icon name="pen" /></button>
                            <button class="btn btn-sm btn-light-secondary" type="button" title="{{ __('Change password') }}" data-modal="user-password" data-action="{{ route('platform.users.password', $u) }}" data-title="{{ $u->name }}" data-edit="{}"><x-icon name="key" /></button>
                            @unless ($self)
                                <form method="post" action="{{ route('platform.users.toggle', $u) }}">@csrf<button class="btn btn-sm btn-light-secondary" title="{{ $u->is_active ? __('Disable') : __('Enable') }}"><x-icon :name="$u->is_active ? 'ban' : 'circle-check'" /></button></form>
                                <form method="post" action="{{ route('platform.users.destroy', $u) }}" data-confirm="{{ __('Delete this user?') }}" data-confirm-text="{{ $u->name }} · {{ $u->email }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" title="{{ __('Delete') }}"><x-icon name="trash" /></button></form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $users->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
@push('modals')
@foreach (['user-create' => [route('platform.users.store'), 'Add user', 'user-plus', false], 'user-edit' => ['#', 'Edit user', 'pen', true]] as $id => [$action, $title, $icon, $editing])
<x-workspace.drawer :id="$id" :title="__($title)" :description="__('An office account needs an office. Administrator and authority accounts belong to none.')" :icon="$icon" wide>
<form method="post" action="{{ $action }}">@csrf @if ($editing) @method('PUT') @endif
<div class="modal-body"><div class="row g-3">
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Full name') }} <span class="req">*</span></label><input class="form-control" name="name" required></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Email') }} <span class="req">*</span></label><input class="form-control" type="email" name="email" dir="ltr" required></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Role') }} <span class="req">*</span></label><select class="form-select" name="role" data-plain required>@foreach ($roles as $role)<option value="{{ $role }}" @selected($role === 'agent')>{{ enum_label($role) }}</option>@endforeach</select></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Office') }}</label><select class="form-select" name="agency_id" data-plain><option value="">{{ __('None (administrator or authority)') }}</option>@foreach ($agencies as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Interface language') }}</label><select class="form-select" name="locale" data-plain>@foreach (config('app.locales') as $code => $language)<option value="{{ $code }}" @selected($code === 'fa')>{{ $language }}</option>@endforeach</select></div>
    @unless ($editing)<div class="col-12 col-md-6"><label class="form-label">{{ __('Temporary password') }} <span class="req">*</span></label><input class="form-control" type="password" name="password" autocomplete="new-password" minlength="8" required></div>@endunless
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save') }}</button></div>
</form>
</x-workspace.drawer>
@endforeach
<x-workspace.drawer id="user-password" :title="__('Change password')" :description="__('The user signs in with the new password from now on.')" icon="key">
<form method="post" action="#">@csrf
<div class="modal-body"><p class="fw-bold text-heading mb-3" data-edit-title></p><label class="form-label">{{ __('New password') }} <span class="req">*</span></label><input class="form-control" type="password" name="password" autocomplete="new-password" minlength="8" required></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Change password') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
@push('scripts')
@include('platform._edit-script')
@endpush
