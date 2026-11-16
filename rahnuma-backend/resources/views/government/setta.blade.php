@extends('layouts.workspace')
@section('title', __('Setta Inventory'))
@section('workspace')
<x-page-header :title="__('Setta Inventory')" icon="book" :crumbs="[[__('Regulatory'), route('government.dashboard')], [__('Setta Inventory')]]" :subtitle="__('Issue and reconcile Setta books, ranges and transaction categories.')" />

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Issued books') }}</h5><div class="small text-muted">{{ __('Tracked inventory by Rahnama office') }}</div></div></div>
            <div class="card-body">
                @if($books->isEmpty())
                    <x-workspace.empty icon="book" />
                @else
                <table class="table table-hover w-100">
                    <thead><tr><th>{{ __('Agency') }}</th><th>{{ __('Book') }}</th><th>{{ __('Type') }}</th><th>{{ __('Serial range') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>@foreach($books as $b)<tr><td><strong class="text-heading">{{ $b->agency?->name }}</strong></td><td dir="ltr">{{ $b->book_no }}</td><td>{{ deal_label($b->type, true) }}</td><td class="text-nowrap" dir="ltr">{{ $b->range_start }} – {{ $b->range_end }}</td><td><span class="badge-soft {{ status_tone($b->status) }}">{{ enum_label($b->status) }}</span></td></tr>@endforeach</tbody>
                </table>
                {{ $books->links('partials.pagination') }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <form class="card" method="post" action="{{ route('government.setta.store') }}">@csrf
            <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="plus" /></span><div><h5>{{ __('Issue Setta book') }}</h5><small>{{ __('Inventory issue') }}</small></div></div></div>
            <div class="card-body d-grid gap-3">
                <div><label class="form-label">{{ __('Agency') }}</label><select class="form-select" name="agency_id">@foreach($agencies as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('Book number') }} <span class="req">*</span></label><input class="form-control" name="book_no" required dir="ltr"></div>
                <div><label class="form-label">{{ __('Transaction type') }}</label><select class="form-select" name="type">@foreach(['sale','rent','bay_wafa'] as $s)<option value="{{ $s }}">{{ deal_label($s, true) }}</option>@endforeach</select></div>
                <div class="row g-2"><div class="col-6"><label class="form-label">{{ __('Range start') }} <span class="req">*</span></label><input class="form-control" type="number" name="range_start" required dir="ltr"></div><div class="col-6"><label class="form-label">{{ __('Range end') }} <span class="req">*</span></label><input class="form-control" type="number" name="range_end" required dir="ltr"></div></div>
            </div>
            <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Issue inventory') }}</button></div>
        </form>
    </div>
</div>
@endsection
