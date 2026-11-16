@extends('layouts.workspace')
@section('title', __('General Ledger'))
@section('workspace')
<x-page-header :title="__('General Ledger')" icon="book" :crumbs="[[__('Accounting'), route('tenant.accounting')], [__('General Ledger')]]" :subtitle="__('Trace every posted debit and credit back through the books.')" />

<div class="card">
    <div class="card-body">
        <form class="row g-2 mb-3" method="get" data-filter-form>
            <div class="col-12 col-md">
                <select class="form-select" name="account_id" aria-label="{{ __('Account') }}" data-placeholder="{{ __('All accounts') }}">
                    <option value="">{{ __('All accounts') }}</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(request('account_id')==$a->id)>{{ $a->code }} — {{ __($a->name) }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-primary" type="submit"><x-icon name="filter" class="me-1" /> {{ __('Apply filter') }}</button>
                @if(request('account_id'))<a class="btn btn-outline-soft" href="{{ route('tenant.ledger') }}">{{ __('Clear all') }}</a>@endif
            </div>
            <div class="col-12 col-md-auto d-flex align-items-center small text-muted">{{ __(':count lines', ['count' => number_format($lines->total())]) }}</div>
        </form>

        @if($lines->isEmpty())
            <x-workspace.empty icon="book" :title="__('No ledger lines')" :description="__('Post a transaction, payment or expense to create accounting activity.')" />
        @else
            <table class="table table-hover w-100">
                <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Entry') }}</th><th>{{ __('Account') }}</th><th>{{ __('Memo') }}</th><th class="text-end">{{ __('Debit') }}</th><th class="text-end">{{ __('Credit') }}</th></tr></thead>
                <tbody>
                @foreach($lines as $l)
                    <tr>
                        <td class="text-nowrap" dir="ltr">{{ $l->journalEntry?->entry_date }}</td>
                        <td><span class="badge-code">{{ $l->journalEntry?->entry_no }}</span></td>
                        <td><strong class="text-heading">{{ __($l->account?->name ?? '') }}</strong> <span class="small text-muted" dir="ltr">{{ $l->account?->code }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($l->memo,42) ?: '—' }}</td>
                        <td class="text-end fw-semibold" dir="ltr">{{ number_format($l->debit,2) }}</td>
                        <td class="text-end fw-semibold" dir="ltr">{{ number_format($l->credit,2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $lines->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
