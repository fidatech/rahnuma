<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, PlatformEntry, Promotion, Property};
use App\Services\PromotionService;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Sponsored and featured placements: given by the administrator, paid by the office. */
class MarketplacePromotionController extends Controller
{
    public function index(Request $request, PromotionService $promotions)
    {
        $promotions->expire();

        $relations = ['agency:id,name,slug', 'coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order', 'province:id,name_en,name_fa,name_ps'];
        $public = fn () => Property::withoutGlobalScopes()->whereIn('visibility', ['public', 'featured'])->whereIn('status', ['available', 'active']);

        $month = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];

        return view('platform.marketplace', [
            'sponsored' => Property::withoutGlobalScopes()->with($relations)->where('is_sponsored', 1)->orderBy('sponsored_until')->get(),
            'featured' => Property::withoutGlobalScopes()->with($relations)->where('visibility', 'featured')->orderBy('featured_until')->get(),
            'featuredOffices' => Agency::where('marketplace_featured', 1)->orderBy('marketplace_featured_until')->get(),
            'properties' => $public()->with($relations)
                ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', '%'.$request->q.'%')->orWhere('code', 'like', '%'.$request->q.'%')))
                ->when($request->filled('agency_id'), fn ($q) => $q->where('agency_id', $request->agency_id))
                ->latest()->paginate(15)->withQueryString(),
            'offices' => Agency::where('is_active', 1)->where('marketplace_enabled', 1)->orderBy('name')->get(['id', 'name', 'slug', 'logo_path', 'marketplace_featured', 'marketplace_featured_until', 'license_number']),
            'income' => PlatformEntry::where('direction', 'in')->whereIn('category', ['sponsorship', 'featuring'])->whereBetween('entry_date', $month)
                ->selectRaw('currency, SUM(amount) as total')->groupBy('currency')->pluck('total', 'currency'),
            'history' => Promotion::with(['agency:id,name,slug', 'property:id,title,code'])->latest()->limit(15)->get(),
        ]);
    }

    public function store(Request $request, PromotionService $promotions)
    {
        $d = $request->validate([
            'kind' => ['required', Rule::in(Promotion::KINDS)],
            'property_id' => 'nullable|integer',
            'agency_id' => 'nullable|integer',
            'days' => 'required|integer|min:1|max:365',
            'amount' => 'nullable|numeric|min:0',
            'currency' => ['required', Rule::in(config('rahnuma.currencies'))],
            'notes' => 'nullable|max:500',
        ]);

        $target = $d['kind'] === 'featured_office'
            ? Agency::findOrFail($d['agency_id'] ?? 0)
            : Property::withoutGlobalScopes()->whereIn('visibility', ['public', 'featured'])->findOrFail($d['property_id'] ?? 0);

        $promotion = $promotions->start($d['kind'], $target, (int) $d['days'], (float) ($d['amount'] ?? 0), $d['currency'], $d['notes'] ?? null);
        Audit::log('promotion.'.$d['kind'], $target, [], ['days' => (int) $d['days'], 'amount' => (float) ($d['amount'] ?? 0), 'currency' => $d['currency'], 'until' => $promotion->ends_at->toDateString()]);

        return back()->with('success', match ($d['kind']) {
            'sponsored' => __('Property is now sponsored on the marketplace.'),
            'featured' => __('Property is now featured on the marketplace.'),
            default => __('Rahnama is now featured on the marketplace.'),
        });
    }

    public function stop(Request $request, PromotionService $promotions)
    {
        $d = $request->validate(['kind' => ['required', Rule::in(Promotion::KINDS)], 'id' => 'required|integer']);
        $target = $d['kind'] === 'featured_office' ? Agency::findOrFail($d['id']) : Property::withoutGlobalScopes()->findOrFail($d['id']);

        $promotions->stop($d['kind'], $target);
        Audit::log('promotion.stopped', $target, [], ['kind' => $d['kind']]);

        return back()->with('success', __('Placement ended.'));
    }
}
