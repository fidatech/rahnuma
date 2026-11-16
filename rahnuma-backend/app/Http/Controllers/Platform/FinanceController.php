<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, PlatformEntry};
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Money in and money out of the platform itself. */
class FinanceController extends Controller
{
    public function index(Request $r)
    {
        $from = $r->filled('from') ? $r->date('from')->toDateString() : now()->startOfYear()->toDateString();
        $to = $r->filled('to') ? $r->date('to')->toDateString() : now()->toDateString();

        $period = fn () => PlatformEntry::whereBetween('entry_date', [$from, $to]);
        $entries = $period()->with(['agency:id,name,slug', 'author:id,name'])
            ->when($r->filled('direction'), fn ($q) => $q->where('direction', $r->direction))
            ->when($r->filled('category'), fn ($q) => $q->where('category', $r->category))
            ->when($r->filled('currency'), fn ($q) => $q->where('currency', $r->currency))
            ->orderByDesc('entry_date')->orderByDesc('id')->paginate(30)->withQueryString();

        // totals of the period for each currency: in, out and what is left
        $totals = [];
        foreach ($period()->selectRaw('currency, direction, SUM(amount) as total')->groupBy('currency', 'direction')->get() as $row) {
            $totals[$row->currency][$row->direction] = (float) $row->total;
        }
        foreach (config('rahnuma.currencies') as $currency) {
            $totals[$currency] = ($totals[$currency] ?? []) + ['in' => 0.0, 'out' => 0.0];
            $totals[$currency]['net'] = $totals[$currency]['in'] - $totals[$currency]['out'];
        }

        $byCategory = $period()->selectRaw('direction, category, currency, SUM(amount) as total')->groupBy('direction', 'category', 'currency')->orderByDesc('total')->get();

        return view('platform.finance', [
            'entries' => $entries, 'totals' => $totals, 'byCategory' => $byCategory, 'from' => $from, 'to' => $to,
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'direction' => 'required|in:in,out',
            'category' => ['required', Rule::in(array_merge(PlatformEntry::IN, PlatformEntry::OUT))],
            'amount' => 'required|numeric|min:0.01',
            'currency' => ['required', Rule::in(config('rahnuma.currencies'))],
            'entry_date' => 'required|date',
            'agency_id' => 'nullable|exists:agencies,id',
            'reference' => 'nullable|max:120',
            'notes' => 'nullable|max:1000',
        ]);
        // the kind of entry must belong to its side
        if (! in_array($d['category'], $d['direction'] === 'in' ? PlatformEntry::IN : PlatformEntry::OUT, true)) {
            return back()->withErrors(['category' => __('Choose a kind that fits money in or money out.')])->withInput();
        }

        $entry = PlatformEntry::create($d + ['created_by' => auth()->id()]);
        Audit::log('money.'.$d['direction'], $entry, [], $entry->only(['category', 'amount', 'currency']));

        return back()->with('success', $d['direction'] === 'in' ? __('Money in recorded.') : __('Money out recorded.'));
    }

    public function destroy(PlatformEntry $entry)
    {
        Audit::log('money.deleted', $entry, $entry->only(['direction', 'category', 'amount', 'currency']));
        $entry->delete();

        return back()->with('success', __('Entry deleted.'));
    }
}
