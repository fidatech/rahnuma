@extends('layouts.workspace')
@section('title', __('Audit log'))
@section('workspace')
<x-page-header :title="__('Audit log')" icon="clipboard-check" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('Audit log')]]" :subtitle="__('Who did what, to which record, and when. Entries cannot be edited.')" />

<form class="card" method="get">
    <div class="card-body"><div class="row g-2 align-items-end">
        <div class="col-12 col-lg"><label class="form-label">{{ __('Action') }}</label><div class="input-icon"><x-icon name="magnifying-glass" /><input class="form-control" name="q" value="{{ request('q') }}" dir="ltr" placeholder="office, user, plan, money…"></div></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Done by') }}</label><select class="form-select" name="user_id" data-placeholder="{{ __('Anyone') }}"><option value=""></option>@foreach ($actors as $u)<option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Office') }}</label><select class="form-select" name="agency_id" data-placeholder="{{ __('Any office') }}"><option value=""></option>@foreach ($agencies as $a)<option value="{{ $a->id }}" @selected(request('agency_id') == $a->id)>{{ $a->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('From') }}</label><input class="form-control" type="date" name="from" value="{{ request('from') }}" dir="ltr"></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('To') }}</label><input class="form-control" type="date" name="to" value="{{ request('to') }}" dir="ltr"></div>
        <div class="col-12 col-lg-auto d-flex gap-2"><button class="btn btn-primary flex-fill"><x-icon name="filter" class="me-1" /> {{ __('Show') }}</button>@if (request()->hasAny(['q', 'user_id', 'agency_id', 'from', 'to']))<a class="btn btn-outline-soft" href="{{ route('platform.audit') }}">{{ __('Reset') }}</a>@endif</div>
    </div></div>
</form>

<div class="card">
    <div class="card-body">
        @if ($logs->isEmpty())
            <x-workspace.empty icon="clipboard-check" :title="__('No activity yet.')" :description="__('Every change made in this panel is recorded here.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('When') }}</th><th>{{ __('Done by') }}</th><th>{{ __('Action') }}</th><th>{{ __('Record') }}</th><th>{{ __('Details') }}</th><th>IP</th></tr></thead>
            <tbody>
            @foreach ($logs as $log)
                @php $changes = collect((array) $log->new_values)->map(fn ($v, $k) => $k.': '.(is_scalar($v) || $v === null ? (is_bool($v) ? ($v ? 'yes' : 'no') : (string) $v) : json_encode($v)))->join(' · '); @endphp
                <tr>
                    <td dir="ltr" class="text-nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user?->name ?: '—' }}<span class="small text-muted d-block" dir="ltr">{{ $log->user?->email }}</span></td>
                    <td><span class="badge-code" dir="ltr">{{ $log->event }}</span></td>
                    <td>{{ $log->auditable_type }}@if ($log->auditable_id) <span dir="ltr">#{{ $log->auditable_id }}</span>@endif @if ($log->agency)<a class="small d-block" href="{{ route('platform.agencies.show', $log->agency) }}">{{ $log->agency->name }}</a>@endif</td>
                    <td><span class="small text-muted d-block text-truncate" style="max-width:320px" dir="ltr" title="{{ $changes }}">{{ $changes ?: '—' }}</span></td>
                    <td dir="ltr" class="small text-muted">{{ $log->ip_address ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $logs->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
