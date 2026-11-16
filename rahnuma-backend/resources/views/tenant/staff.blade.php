@extends('layouts.workspace')
@section('workspace')
<div class="smart-page-head"><div><div class="eyebrow">Access & responsibility</div><h1>Team & Roles</h1><p>Give every staff member their own login and keep responsibility visible across the office.</p></div><button class="btn" type="button" data-modal-open="staff-drawer">Add team member</button></div>
<div class="pulse-strip pulse-strip--3"><div class="pulse-item"><span>Team members</span><strong>{{ $users->count() }}</strong><small>All workspace users</small></div><div class="pulse-item"><span>Active</span><strong>{{ $users->where('is_active',true)->count() }}</strong><small>Can access workspace</small></div><div class="pulse-item pulse-item--quiet"><span>Your role</span><strong>{{ ucwords(str_replace('_',' ',auth()->user()->role)) }}</strong><small>Current session</small></div></div>
<div class="team-grid">
@foreach($users as $u)
<article class="team-card {{ !$u->is_active?'team-card--disabled':'' }}"><div class="team-card__top"><div class="team-avatar">{{ strtoupper(mb_substr($u->name,0,1)) }}</div><div><h3>{{ $u->name }}</h3><p>{{ $u->email }}</p></div><span class="badge {{ $u->is_active?'success':'danger' }}">{{ $u->is_active?'Active':'Disabled' }}</span></div><div class="team-role"><span>Role</span><b>{{ ucwords(str_replace('_',' ',$u->role)) }}</b></div><div class="team-card__bottom"><span>{{ strtoupper($u->locale) }} workspace</span>@if($u->id!==auth()->id())<form method="post" action="{{ route('tenant.staff.toggle',$u) }}">@csrf<button class="text-action">{{ $u->is_active?'Disable access':'Enable access' }}</button></form>@else<span class="muted">Current user</span>@endif</div></article>
@endforeach
</div>
@endsection
@push('modals')
<x-workspace.drawer id="staff-drawer" title="Add team member" kicker="Workspace access" description="Create a dedicated account. Never share the owner's credentials with staff.">
<form method="post" action="{{ route('tenant.staff.store') }}" class="drawer-form">@csrf<div class="smart-sheet__body"><div class="drawer-grid drawer-grid--single"><div class="field"><label>Full name</label><input name="name" required></div><div class="field"><label>Email</label><input type="email" name="email" required></div><div class="field"><label>Temporary password</label><input type="password" name="password" required></div><div class="field"><label>Role</label><select name="role">@foreach(['agency_manager','agent','accountant','cashier','data_entry','viewer'] as $r)<option value="{{ $r }}">{{ ucwords(str_replace('_',' ',$r)) }}</option>@endforeach</select></div><div class="field"><label>Interface language</label><select name="locale"><option value="fa">دری</option><option value="ps">پښتو</option><option value="en">English</option></select></div></div></div><div class="smart-sheet__footer"><button class="btn secondary" type="button" data-modal-close>Cancel</button><button class="btn">Create account</button></div></form>
</x-workspace.drawer>
@endpush
