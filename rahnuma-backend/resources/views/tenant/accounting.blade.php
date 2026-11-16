@extends('layouts.workspace')
@section('title', __('Accounting'))
@section('workspace')
<x-page-header :title="__('Accounting')" icon="chart-column" :subtitle="__('Double-entry books underneath; a simple operating view for the Rahnama team.')">
    <a class="btn btn-outline-soft" href="{{ route('tenant.ledger') }}"><x-icon name="book" class="me-1" /> {{ __('Open general ledger') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><x-stat :label="__('Net result')" :value="number_format($income-$expense,2)" icon="scale-balanced" tone="blue" :note="__('Booked income less booked expenses')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Income')" :value="number_format($income,2)" icon="arrow-trend-up" tone="green" :note="__('Posted income entries')" /></div>
    <div class="col-12 col-md-4"><x-stat :label="__('Expenses')" :value="number_format($expense,2)" icon="arrow-trend-down" tone="red" :note="__('Posted operating costs')" /></div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card h-100 mb-0">
            <div class="card-header"><div><h5 class="card-title mb-0">{{ __('Trial balance') }}</h5><div class="small text-muted">{{ __('Chart position') }}</div></div><a class="btn btn-sm btn-outline-soft" href="{{ route('tenant.ledger') }}">{{ __('Inspect entries') }}</a></div>
            <div class="card-body">
                @if(count($balances) === 0)
                    <x-workspace.empty icon="chart-column" :title="__('No journal activity')" :description="__('Accounting entries will appear automatically when transactions, payments and expenses are posted.')" />
                @else
                    <table class="table table-hover w-100">
                        <thead><tr><th>{{ __('Account') }}</th><th class="text-end">{{ __('Debit') }}</th><th class="text-end">{{ __('Credit') }}</th><th class="text-end">{{ __('Balance') }}</th></tr></thead>
                        <tbody>
                        @foreach($balances as $b)
                            <tr>
                                <td><span class="badge-code me-1">{{ $b->account?->code }}</span> <strong class="text-heading">{{ __($b->account?->name ?? '') }}</strong></td>
                                <td class="text-end" dir="ltr">{{ number_format($b->debit,2) }}</td>
                                <td class="text-end" dir="ltr">{{ number_format($b->credit,2) }}</td>
                                <td class="text-end fw-semibold" dir="ltr">{{ number_format($b->debit-$b->credit,2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">{{ __('Recent payments') }}</h5></div>
            <div class="card-body">
                <ul class="list-clean">
                @forelse($payments as $p)
                    <li><span class="flex-fill min-w-0"><strong class="d-block text-heading">{{ enum_label($p->category, __('Payment')) }}</strong><span class="small text-muted" dir="ltr">{{ $p->paid_at?->format('d M') ?: $p->created_at?->format('d M') }}</span></span><strong class="{{ $p->direction==='out' ? 'text-danger' : 'text-success' }}" dir="ltr">{{ $p->direction==='out'?'-':'+' }}{{ number_format((float)$p->amount) }} {{ $p->currency }}</strong></li>
                @empty
                    <li class="text-muted">{{ __('No payments yet.') }}</li>
                @endforelse
                </ul>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header"><h5 class="card-title mb-0">{{ __('Recent expenses') }}</h5></div>
            <div class="card-body">
                <ul class="list-clean">
                @forelse($expenses as $e)
                    <li><span class="flex-fill min-w-0"><strong class="d-block text-heading">{{ enum_label($e->category, __('Expense')) }}</strong><span class="small text-muted" dir="ltr">{{ $e->expense_date?->format('d M') ?: $e->created_at?->format('d M') }}</span></span><strong class="text-danger" dir="ltr">-{{ number_format((float)$e->amount) }} {{ $e->currency }}</strong></li>
                @empty
                    <li class="text-muted">{{ __('No expenses yet.') }}</li>
                @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
