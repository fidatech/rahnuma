<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{Owner, Property, PropertyMedia, PropertyOwner, Province};
use App\Services\{ImageOptimizationService, MatchingService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with([
            'owner:id,name,phone',
            'coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order',
            'province:id,name_en,name_fa',
        ]);

        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('address', 'like', "%{$term}%"));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('transaction_type')) $query->where('transaction_type', $request->transaction_type);
        if ($request->filled('visibility')) $query->where('visibility', $request->visibility);

        $agg = Property::selectRaw("COUNT(*) as total, SUM(CASE WHEN status IN ('available','active') THEN 1 ELSE 0 END) as available_count, SUM(CASE WHEN visibility IN ('public','featured') THEN 1 ELSE 0 END) as public_count, SUM(CASE WHEN owner_id IS NULL THEN 1 ELSE 0 END) as unowned_count")->first();

        return view('tenant.properties.index', [
            'properties' => $query->latest()->paginate(18)->withQueryString(),
            'stats' => [
                'total' => (int) $agg->total,
                'available' => (int) $agg->available_count,
                'public' => (int) $agg->public_count,
                'unowned' => (int) $agg->unowned_count,
            ],
        ]);
    }

    public function create()
    {
        return $this->formView(new Property);
    }

    public function store(Request $request)
    {
        $agency = auth()->user()->agency;
        $limit = $agency->plan?->property_limit;
        if ($limit && Property::count() >= $limit) {
            return back()->withErrors(['plan' => 'Your plan property limit has been reached.'])->withInput();
        }

        $data = $this->validated($request);
        $this->ensureOwnerBelongsToTenant($data['owner_id'] ?? null);

        $property = Property::create($data + [
            'code' => 'PR-'.date('ymd').'-'.random_int(1000, 9999),
            'published_at' => in_array($request->visibility, ['public', 'featured'], true) ? now() : null,
        ]);

        $this->syncPrimaryOwner($property);
        $this->media($request, $property);
        Cache::forget('rahnuma:marketplace:home:v8');

        return redirect()->route('tenant.properties.edit', $property)->with('success', 'Property created. Its primary owner is linked to this property record.');
    }

    public function edit(Property $property)
    {
        return $this->formView($property->load('owner:id,name,phone'));
    }

    public function update(Request $request, Property $property)
    {
        $data = $this->validated($request);
        $this->ensureOwnerBelongsToTenant($data['owner_id'] ?? null);

        $property->update($data + [
            'published_at' => in_array($request->visibility, ['public', 'featured'], true) ? ($property->published_at ?? now()) : null,
        ]);

        $this->syncPrimaryOwner($property);
        $this->media($request, $property);
        Cache::forget('rahnuma:marketplace:home:v8');

        return back()->with('success', 'Property updated.');
    }

    public function destroy(Property $property)
    {
        $property->delete();
        Cache::forget('rahnuma:marketplace:home:v8');
        return back()->with('success', 'Property deleted.');
    }

    public function quick(Property $property)
    {
        $property->load(['owner:id,name,phone,whatsapp','coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order','province:id,name_en,name_fa','district:id,name_en,name_fa','area:id,name_en,name_fa']);
        return view('tenant.properties.quick', compact('property'));
    }

    public function matches(Property $property, MatchingService $matching)
    {
        return view('tenant.properties.matches', ['property' => $property, 'requirements' => $matching->customersForProperty($property)]);
    }

    private function formView(Property $property)
    {
        return view('tenant.properties.form', [
            'property' => $property,
            'selectedOwner' => session()->hasOldInput('owner_id') ? Owner::find(session()->getOldInput('owner_id')) : $property->owner,
            'provinces' => Cache::remember('rahnuma:provinces:list', 3600, fn () => Province::orderBy('name_en')->get(['id','name_en','name_fa'])),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'owner_id' => 'nullable|integer',
            'title' => 'required|max:180',
            'description' => 'nullable',
            'property_type' => 'required',
            'transaction_type' => 'required',
            'status' => 'required',
            'visibility' => 'required',
            'province_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'area_id' => 'nullable|integer',
            'address' => 'nullable|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'monthly_rent' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|max:5',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'kitchens' => 'nullable|integer',
            'floors' => 'nullable|integer',
            'floor_number' => 'nullable|integer',
            'total_area' => 'nullable|numeric',
            'area_unit' => 'nullable|max:20',
            'parking' => 'nullable|boolean',
            'electricity' => 'nullable|boolean',
            'solar' => 'nullable|boolean',
            'water' => 'nullable|boolean',
            'well' => 'nullable|boolean',
            'heating' => 'nullable|boolean',
            'cooling' => 'nullable|boolean',
            'furnished' => 'nullable|boolean',
            'internet' => 'nullable|boolean',
            'security' => 'nullable|boolean',
            'elevator' => 'nullable|boolean',
            'balcony' => 'nullable|boolean',
            'garden' => 'nullable|boolean',
            'basement' => 'nullable|boolean',
            'storage' => 'nullable|boolean',
            'property_age' => 'nullable|integer',
            'road_width' => 'nullable|numeric',
            'documents_status' => 'nullable|max:40',
        ]);
    }

    private function ensureOwnerBelongsToTenant(?int $ownerId): void
    {
        if ($ownerId && !Owner::find($ownerId)) abort(422, 'The selected owner does not belong to this Rahnama.');
    }

    private function syncPrimaryOwner(Property $property): void
    {
        PropertyOwner::where('property_id', $property->id)->update(['is_primary' => false]);
        if (!$property->owner_id) return;

        $otherOwners = PropertyOwner::where('property_id', $property->id)->where('owner_id', '!=', $property->owner_id)->exists();
        PropertyOwner::updateOrCreate(
            ['property_id' => $property->id, 'owner_id' => $property->owner_id],
            ['is_primary' => true, 'ownership_percent' => $otherOwners ? null : 100]
        );
    }

    private function media(Request $request, Property $property): void
    {
        if (!$request->hasFile('photos')) return;

        $optimizer = app(ImageOptimizationService::class);
        $hasCover = $property->media()->where('is_cover', true)->exists();

        foreach ($request->file('photos') as $index => $file) {
            $path = $file->store('properties', 'public');
            $thumbnail = $optimizer->thumbnail($path);

            PropertyMedia::create([
                'property_id' => $property->id,
                'type' => 'image',
                'path' => $path,
                'thumbnail_path' => $thumbnail,
                'sort_order' => $index,
                'is_cover' => !$hasCover && $index === 0,
            ]);
        }
    }
}
