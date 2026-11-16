<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, PlatformEntry, Promotion, PromotionRequest, Property, Setting};
use App\Services\PromotionService;
use App\Support\{Audit, PromotionPrices};
use Illuminate\Support\Facades\Storage;
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
            'requests' => PromotionRequest::withoutGlobalScopes()->with(['agency:id,name,slug', 'property:id,title,code', 'requester:id,name'])->where('status', 'pending')->oldest()->get(),
            'reviewed' => PromotionRequest::withoutGlobalScopes()->with(['agency:id,name,slug', 'property:id,title,code'])->where('status', '!=', 'pending')->latest('reviewed_at')->limit(8)->get(),
            'prices' => PromotionPrices::all(),
            'priceCurrency' => PromotionPrices::currency(),
            'instructions' => PromotionPrices::instructions(),
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

    // ------------------------------------------------- requests of the offices

    /** The payment is confirmed: the placement starts and the fee goes to money in. */
    public function approve(Request $request, PromotionService $promotions, int $promotionRequest)
    {
        $ask = PromotionRequest::withoutGlobalScopes()->where('status', 'pending')->findOrFail($promotionRequest);
        $d = $request->validate(['amount' => 'required|numeric|min:0', 'admin_note' => 'nullable|max:500']);
        $property = Property::withoutGlobalScopes()->whereIn('visibility', ['public', 'featured'])->find($ask->property_id);
        if (! $property) return back()->withErrors(['request' => __('This property is no longer on the marketplace. Reject the request instead.')]);

        $method = ['hesabpay' => 'HesabPay', 'aurumpay' => 'AurumPay', 'mobile_money' => 'Mobile money', 'cash' => 'Cash'][$ask->payment_method] ?? $ask->payment_method;
        $paid = trim($method.($ask->payment_reference ? ' · '.$ask->payment_reference : ''));
        $promotion = $promotions->start($ask->kind, $property, (int) $ask->days, (float) $d['amount'], $ask->currency, $paid);
        $ask->update(['status' => 'approved', 'amount' => $d['amount'], 'admin_note' => $d['admin_note'] ?? null, 'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'promotion_id' => $promotion->id]);
        Audit::log('promotion.request_approved', $property, [], ['kind' => $ask->kind, 'days' => (int) $ask->days, 'amount' => (float) $d['amount'], 'method' => $ask->payment_method]);

        return back()->with('success', __('Request approved. The placement has started.'));
    }

    public function reject(Request $request, int $promotionRequest)
    {
        $ask = PromotionRequest::withoutGlobalScopes()->where('status', 'pending')->findOrFail($promotionRequest);
        $d = $request->validate(['admin_note' => 'required|max:500']);
        $ask->update(['status' => 'rejected', 'admin_note' => $d['admin_note'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        Audit::log('promotion.request_rejected', $ask, [], ['reason' => $d['admin_note']]);

        return back()->with('success', __('Request rejected.'));
    }

    public function receipt(int $promotionRequest)
    {
        $ask = PromotionRequest::withoutGlobalScopes()->findOrFail($promotionRequest);
        abort_unless($ask->receipt_path && Storage::disk('local')->exists($ask->receipt_path), 404);

        return Storage::disk('local')->response($ask->receipt_path);
    }

    /** The prices offices see, and where they send the money. */
    public function prices(Request $request)
    {
        $rules = ['currency' => ['required', Rule::in(config('rahnuma.currencies'))]];
        foreach (PromotionRequest::KINDS as $kind) foreach (PromotionRequest::DAYS as $days) $rules["price.$kind.$days"] = 'required|numeric|min:0';
        foreach (PromotionRequest::METHODS as $method) $rules["pay.$method"] = 'nullable|max:300';
        $d = $request->validate($rules);

        foreach (PromotionRequest::KINDS as $kind) foreach (PromotionRequest::DAYS as $days) Setting::put("promo_price_{$kind}_{$days}", (string) (float) $d['price'][$kind][$days]);
        foreach (PromotionRequest::METHODS as $method) Setting::put("promo_pay_$method", $d['pay'][$method] ?? null);
        Setting::put('promo_currency', $d['currency']);
        Audit::log('promotion.prices_updated', null, [], ['currency' => $d['currency']]);

        return back()->with('success', __('Prices saved.'));
    }
}
