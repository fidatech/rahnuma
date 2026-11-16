@extends('layouts.workspace')
@section('title', __('Team & Roles'))
@section('workspace')
<x-page-header :title="__('Team & Roles')" icon="users-gear" :subtitle="__('Give every staff member their own login and keep responsibility visible across the office.')">
    <button class="btn btn-primary" type="button" data-modal-open="staff-drawer"><x-icon name="user-plus" class="me-1" /> {{ __('Add team member') }}</button>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><x-stat :label="__('Team members')" :value="number_format($users->count())" icon="users" tone="blue" :note="__('All workspace users')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Active')" :value="number_format($users->where('is_active',true)->count())" icon="circle-check" tone="green" :note="__('Can access workspace')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Your role')" :value="enum_label(auth()->user()->role)" icon="user-shield" tone="violet" :note="__('Current session')" /></div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Team member') }}</th><th>{{ __('Role') }}</th><th>{{ __('Language') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm avatar-soft-primary">@if($u->avatar_path)<img src="{{ asset('storage/'.$u->avatar_path) }}" alt="">@else{{ initial($u->name) }}@endif</span><div class="min-w-0"><strong class="d-block text-heading">{{ $u->name }}</strong><span class="small text-muted" dir="ltr">{{ $u->email }}</span></div></div></td>
                    <td>{{ enum_label($u->role) }}</td>
                    <td>{{ config('app.locales')[$u->locale] ?? strtoupper((string) $u->locale) }}</td>
                    <td><span class="badge-soft {{ $u->is_active ? 'success' : 'danger' }}">{{ $u->is_active ? __('Active') : __('Disabled') }}</span></td>
                    <td class="text-end text-nowrap">
                        @if($u->id!==auth()->id())
                            <form method="post" action="{{ route('tenant.staff.toggle',$u) }}" class="d-inline">@csrf<button class="btn btn-sm {{ $u->is_active ? 'btn-light-secondary' : 'btn-soft-primary' }}">{{ $u->is_active ? __('Disable access') : __('Enable access') }}</button></form>
                        @else
                            <span class="small text-muted">{{ __('Current user') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('modals')
<x-workspace.drawer id="staff-drawer" :title="__('Add team member')" :description="__('Create a dedicated account. Never share the sign-in of the owner with staff.')" icon="user-plus">
<form method="post" action="{{ route('tenant.staff.store') }}">@csrf
<div class="modal-body"><div class="row g-3">
    <div class="col-12"><label class="form-label">{{ __('Full name') }} <span class="req">*</span></label><input class="form-control" name="name" required></div>
    <div class="col-12"><label class="form-label">{{ __('Email') }} <span class="req">*</span></label><input class="form-control" type="email" name="email" required dir="ltr"></div>
    <div class="col-12"><label class="form-label">{{ __('Temporary password') }} <span class="req">*</span></label><input class="form-control" type="password" name="password" required autocomplete="new-password"></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Role') }}</label><select class="form-select" name="role">@foreach(['agency_manager','agent','accountant','cashier','data_entry','viewer'] as $r)<option value="{{ $r }}">{{ enum_label($r) }}</option>@endforeach</select></div>
    <div class="col-12 col-md-6"><label class="form-label">{{ __('Interface language') }}</label><select class="form-select" name="locale"><option value="fa">دری</option><option value="ps">پښتو</option><option value="en">English</option></select></div>
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Create account') }}</button></div>
</form>
</x-workspace.drawer>
@endpush
