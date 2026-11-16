<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, Property};
use Illuminate\Http\Request;

class MarketplacePromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::withoutGlobalScopes()->with(['agency:id,name,slug','coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order','province:id,name_en,name_fa'])
            ->whereIn('visibility', ['public','featured'])
            ->whereIn('status', ['available','active']);
        if ($request->filled('q')) {
            $query->where(fn($q) => $q->where('title','like','%'.$request->q.'%')->orWhere('code','like','%'.$request->q.'%'));
        }
        return view('platform.marketplace', [
            'properties' => $query->latest()->paginate(24)->withQueryString(),
            'sponsoredCount' => Property::withoutGlobalScopes()->where('is_sponsored', true)->where(fn($q)=>$q->whereNull('sponsored_until')->orWhere('sponsored_until','>',now()))->count(),
            'featuredAgencies' => Agency::where('marketplace_featured', true)->count(),
            'agencies' => Agency::where('is_active',1)->where('marketplace_enabled',1)->withCount(['properties as public_properties_count'=>fn($q)=>$q->whereIn('visibility',['public','featured'])->whereIn('status',['available','active'])])->orderByDesc('marketplace_featured')->orderByDesc('public_properties_count')->limit(12)->get(),
        ]);
    }

    public function sponsor(Request $request, Property $property)
    {
        $data = $request->validate(['days' => 'nullable|integer|min:1|max:365']);
        $property->update([
            'is_sponsored' => true,
            'sponsored_until' => now()->addDays($data['days'] ?? 30),
        ]);
        \Illuminate\Support\Facades\Cache::forget('rahnuma:marketplace:home:v8');
        return back()->with('success', __('Property is now sponsored on the marketplace.'));
    }

    public function unsponsor(Property $property)
    {
        $property->update(['is_sponsored' => false, 'sponsored_until' => null]);
        \Illuminate\Support\Facades\Cache::forget('rahnuma:marketplace:home:v8');
        return back()->with('success', __('Sponsored placement removed.'));
    }

    public function toggleAgency(Agency $agency)
    {
        $agency->update(['marketplace_featured' => !$agency->marketplace_featured]);
        \Illuminate\Support\Facades\Cache::forget('rahnuma:marketplace:home:v8');
        return back()->with('success', $agency->marketplace_featured ? __('Rahnama is now featured on the marketplace.') : __('Featured Rahnama placement removed.'));
    }
}
