<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{Owner, Property, PropertyMedia, PropertyOwner, Province};
use App\Services\MatchingService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['owner', 'media']);
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$request->q.'%')->orWhere('code', 'like', '%'.$request->q.'%'));
        }
        if ($request->filled('status')) $query->where('status', $request->status);

        return view('tenant.properties.index', ['properties' => $query->latest()->paginate(25)->withQueryString()]);
    }

    public function create()
    {
        return view('tenant.properties.form', [
            'property' => new Property,
            'owners' => Owner::orderBy('name')->get(),
            'provinces' => Province::all(),
        ]);
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

        return redirect()->route('tenant.properties.edit', $property)->with('success', 'Property created. Its primary owner is linked to this property record.');
    }

    public function edit(Property $property)
    {
        return view('tenant.properties.form', [
            'property' => $property,
            'owners' => Owner::orderBy('name')->get(),
            'provinces' => Province::all(),
        ]);
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

        return back()->with('success', 'Property updated.');
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return back()->with('success', 'Property deleted.');
    }

    public function matches(Property $property, MatchingService $matching)
    {
        return view('tenant.properties.matches', ['property' => $property, 'requirements' => $matching->customersForProperty($property)]);
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
        foreach ($request->file('photos') as $index => $file) {
            $path = $file->store('properties', 'public');
            PropertyMedia::create([
                'property_id' => $property->id,
                'type' => 'image',
                'path' => $path,
                'sort_order' => $index,
                'is_cover' => $property->media()->count() === 0 && $index === 0,
            ]);
        }
    }
}
