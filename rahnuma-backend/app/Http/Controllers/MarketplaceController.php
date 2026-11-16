<?php

namespace App\Http\Controllers;

use App\Models\{Agency, Area, District, Inquiry, Property, Province};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MarketplaceController extends Controller
{
    private function publicBase()
    {
        return Property::withoutGlobalScopes()
            ->whereHas('agency', fn ($q) => $q->where('is_active', 1)->where('marketplace_enabled', 1))
            ->whereIn('visibility', ['public', 'featured'])
            ->whereIn('status', ['available', 'active']);
    }

    private function cardRelations(): array
    {
        return ['agency:id,name,slug,logo_path,is_verified', 'coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order', 'province:id,name_en,name_fa,name_ps', 'district:id,name_en,name_fa,name_ps', 'area:id,name_en,name_fa,name_ps'];
    }

    public function home()
    {
        $payload = Cache::remember('rahnuma:marketplace:home:v9', 120, function () {
            app(\App\Services\PromotionService::class)->expire();
            $base = $this->publicBase();
            $popularRaw = (clone $base)
                ->selectRaw('province_id, COUNT(*) as listings_count')
                ->whereNotNull('province_id')
                ->groupBy('province_id')
                ->orderByDesc('listings_count')
                ->limit(8)
                ->get();

            $provinceMap = Province::whereIn('id', $popularRaw->pluck('province_id'))->get()->keyBy('id');
            $popularLocations = $popularRaw->map(fn ($x) => [
                'province' => $provinceMap->get($x->province_id),
                'count' => (int) $x->listings_count,
            ])->filter(fn ($x) => $x['province'])->values();

            $featuredAgencies = Agency::with('province:id,name_en,name_fa,name_ps')
                ->where('is_active', 1)
                ->where('marketplace_enabled', 1)
                ->withCount(['properties as public_properties_count' => fn ($q) => $q->whereIn('visibility', ['public', 'featured'])->whereIn('status', ['available', 'active'])])
                ->orderByDesc('marketplace_featured')
                ->orderByDesc('is_verified')
                ->limit(6)
                ->get();

            return [
                'sponsored' => (clone $base)->with($this->cardRelations())->where('is_sponsored', 1)->where(fn ($q) => $q->whereNull('sponsored_until')->orWhere('sponsored_until', '>', now()))->latest('sponsored_until')->limit(8)->get(),
                'featured' => (clone $base)->with($this->cardRelations())->where(fn ($q) => $q->where('visibility', 'featured')->orWhereNotNull('featured_at'))->latest('featured_at')->limit(8)->get(),
                'latest' => (clone $base)->with($this->cardRelations())->latest('published_at')->limit(8)->get(),
                'apartments' => (clone $base)->with($this->cardRelations())->where('property_type', 'apartment')->latest('published_at')->limit(8)->get(),
                'houses' => (clone $base)->with($this->cardRelations())->where('property_type', 'house')->latest('published_at')->limit(8)->get(),
                'provinces' => Province::orderBy('name_en')->get(['id','name_en','name_fa','name_ps']),
                'popularLocations' => $popularLocations,
                'featuredAgencies' => $featuredAgencies,
                'marketStats' => [
                    'properties' => (clone $base)->count(),
                    'agencies' => Agency::where('is_active', 1)->where('marketplace_enabled', 1)->count(),
                    'provinces' => (clone $base)->whereNotNull('province_id')->distinct()->count('province_id'),
                ],
            ];
        });

        return view('marketplace.home', $payload);
    }

    public function properties(Request $r)
    {
        $q = $this->publicBase()->with($this->cardRelations());

        if ($r->filled('q')) {
            $term = trim((string) $r->q);
            $q->where(fn ($x) => $x->where('title', 'like', "%{$term}%")
                ->orWhere('address', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%"));
        }

        foreach (['property_type','transaction_type','province_id','district_id','area_id','currency'] as $field) {
            if ($r->filled($field)) $q->where($field, $r->$field);
        }

        if ($r->boolean('featured')) $q->where(fn ($x) => $x->where('visibility', 'featured')->orWhereNotNull('featured_at'));
        if ($r->boolean('sponsored')) $q->where('is_sponsored', 1)->where(fn ($x) => $x->whereNull('sponsored_until')->orWhere('sponsored_until', '>', now()));
        if ($r->filled('min_price')) $q->whereRaw('COALESCE(price,monthly_rent,0) >= ?', [$r->min_price]);
        if ($r->filled('max_price')) $q->whereRaw('COALESCE(price,monthly_rent,0) <= ?', [$r->max_price]);
        if ($r->filled('bedrooms')) $q->where('bedrooms', '>=', $r->bedrooms);

        match ($r->get('sort')) {
            'price_low' => $q->orderByRaw('COALESCE(price,monthly_rent,0) asc'),
            'price_high' => $q->orderByRaw('COALESCE(price,monthly_rent,0) desc'),
            'oldest' => $q->oldest('published_at'),
            default => $q->orderByDesc('is_sponsored')->orderByRaw('featured_at is null')->latest('published_at'),
        };

        return view('marketplace.properties', [
            'properties' => $q->paginate(18)->withQueryString(),
            'provinces' => Cache::remember('rahnuma:provinces:list:v2', 3600, fn () => Province::orderBy('name_en')->get(['id','name_en','name_fa','name_ps'])),
            'districts' => $r->filled('province_id') ? District::where('province_id', $r->province_id)->orderBy('name_en')->get(['id','name_en','name_fa','name_ps']) : collect(),
            'areas' => $r->filled('district_id') ? Area::where('district_id', $r->district_id)->orderBy('name_en')->get(['id','name_en','name_fa','name_ps']) : collect(),
        ]);
    }

    public function property(Property $property)
    {
        $property = Property::withoutGlobalScopes()->with(['agency','media','province','district','area'])->findOrFail($property->id);
        $this->assertPublicProperty($property);
        $property->increment('views_count');

        $similar = $this->publicBase()->with($this->cardRelations())
            ->where('id', '!=', $property->id)
            ->where('property_type', $property->property_type)
            ->when($property->province_id, fn ($q) => $q->where('province_id', $property->province_id))
            ->limit(4)
            ->get();

        return view('marketplace.property', compact('property', 'similar'));
    }

    public function quickProperty(int $property)
    {
        $property = Property::withoutGlobalScopes()->with(['agency','coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order','province','district','area'])->findOrFail($property);
        $this->assertPublicProperty($property);
        return view('marketplace.quick-property', compact('property'));
    }

    public function agency(Agency $agency)
    {
        abort_unless($agency->is_active && $agency->marketplace_enabled, 404);

        $properties = Property::withoutGlobalScopes()
            ->with($this->cardRelations())
            ->where('agency_id', $agency->id)
            ->whereIn('visibility', ['public','featured'])
            ->whereIn('status', ['available','active'])
            ->orderByDesc('is_sponsored')
            ->latest('published_at')
            ->paginate(18);

        return view('marketplace.agency', compact('agency','properties'));
    }

    public function inquiry(Request $r, Property $property)
    {
        $p = Property::withoutGlobalScopes()->findOrFail($property->id);
        $this->assertPublicProperty($p->load('agency'));
        $data = $r->validate([
            'name' => 'required|max:120',
            'phone' => 'required|max:40',
            'whatsapp' => 'nullable|max:40',
            'email' => 'nullable|email',
            'message' => 'nullable|max:2000',
            'preferred_visit_at' => 'nullable|date',
        ]);
        Inquiry::withoutGlobalScopes()->create($data + ['agency_id' => $p->agency_id, 'property_id' => $p->id, 'status' => 'new', 'source' => 'marketplace']);
        return back()->with('success', __('Your request was sent to the Rahnama.'));
    }

    public function districts(Province $province)
    {
        return response()->json(District::where('province_id', $province->id)->orderBy('name_en')->get(['id','name_en','name_fa','name_ps'])->map(fn ($d) => ['id' => $d->id, 'text' => $d->name_en.' / '.$d->name_fa]))
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function areas(District $district)
    {
        return response()->json(Area::where('district_id', $district->id)->orderBy('name_en')->get(['id','name_en','name_fa','name_ps'])->map(fn ($a) => ['id' => $a->id, 'text' => $a->name_en.' / '.$a->name_fa]))
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function nearby(Request $r)
    {
        $data = $r->validate(['lat' => 'required|numeric', 'lng' => 'required|numeric', 'radius' => 'nullable|numeric|min:1|max:100']);
        $radius = $data['radius'] ?? 10;
        $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude)-radians(?)) + sin(radians(?))*sin(radians(latitude))))';
        $agencies = Agency::query()->where('is_active', 1)->where('marketplace_enabled', 1)->where('marketplace_show_exact_location', 1)->whereNotNull('latitude')->whereNotNull('longitude')->select('*')->selectRaw("$haversine AS distance", [$data['lat'], $data['lng'], $data['lat']])->having('distance', '<=', $radius)->orderBy('distance')->limit(50)->get();
        return response()->json($agencies);
    }

    /** Every Rahnama office that takes part in the marketplace, with a search by name and place. */
    public function agencies(Request $r)
    {
        $agencies = Agency::with('province:id,name_en,name_fa,name_ps')
            ->where('is_active', 1)
            ->where('marketplace_enabled', 1)
            ->withCount(['properties as public_properties_count' => fn ($q) => $q->whereIn('visibility', ['public', 'featured'])->whereIn('status', ['available', 'active'])])
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$r->q.'%')->orWhere('address', 'like', '%'.$r->q.'%')->orWhere('license_number', $r->q)))
            ->when($r->filled('province_id'), fn ($q) => $q->where('province_id', $r->province_id))
            ->when($r->boolean('verified'), fn ($q) => $q->where('is_verified', 1))
            ->orderByDesc('is_verified')
            ->orderByDesc('marketplace_featured')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('marketplace.agencies', [
            'agencies' => $agencies,
            'provinces' => Cache::remember('rahnuma:provinces:list:v2', 3600, fn () => Province::orderBy('name_en')->get(['id','name_en','name_fa','name_ps'])),
        ]);
    }

    /**
     * Check a transaction record by the code printed on it. Without a code: the form.
     * With a code that matches a recorded deal: the record. Otherwise the form says so.
     */
    public function verify(Request $r, ?string $code = null)
    {
        $code = strtoupper(trim((string) ($code ?? $r->query('code', ''))));
        if ($code !== '' && $r->route('code') === null) {
            return redirect()->route('verify', $code);
        }

        $tx = $code === '' ? null : \App\Models\Transaction::withoutGlobalScopes()->with(['property'])->where('verification_code', $code)->first();
        $office = $tx ? Agency::find($tx->agency_id) : null;

        return response()->view('marketplace.verify', ['tx' => $tx, 'code' => $code, 'office' => $office], $code !== '' && ! $tx ? 404 : 200);
    }

    private function assertPublicProperty(Property $property): void
    {
        abort_unless(in_array($property->visibility, ['public','featured'], true) && in_array($property->status, ['available','active'], true) && $property->agency?->marketplace_enabled, 404);
    }
}
