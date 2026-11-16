<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{Customer, Owner, Property, PropertyOwner};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuickCreateController extends Controller
{
    public function owner(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'tazkira_no' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $existing = $this->findOwnerDuplicate($data);
        $owner = $existing ?: Owner::create($data);

        return response()->json([
            'created' => !$existing,
            'message' => $existing ? 'An existing owner matched this phone/Tazkira and was selected.' : 'Owner created and selected.',
            'item' => [
                'id' => $owner->id,
                'text' => $owner->name,
                'phone' => $owner->phone,
                'tazkira_no' => $owner->tazkira_no,
            ],
        ]);
    }

    public function customer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'phone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'tazkira_no' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'address' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $existing = Customer::when(!empty($data['tazkira_no']), fn ($q) => $q->where('tazkira_no', $data['tazkira_no']))
            ->when(empty($data['tazkira_no']), fn ($q) => $q->where('phone', $data['phone']))
            ->first();

        $customer = $existing ?: Customer::create($data + ['assigned_user_id' => auth()->id()]);

        return response()->json([
            'created' => !$existing,
            'message' => $existing ? 'An existing customer matched this phone/Tazkira and was selected.' : 'Customer created and selected.',
            'item' => [
                'id' => $customer->id,
                'text' => $customer->name,
                'phone' => $customer->phone,
                'tazkira_no' => $customer->tazkira_no,
            ],
        ]);
    }

    public function property(Request $request): JsonResponse
    {
        $data = $request->validate([
            'owner_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'property_type' => ['required', Rule::in(config('rahnuma.property_types'))],
            'transaction_type' => ['required', Rule::in(config('rahnuma.transaction_types'))],
            'province_id' => ['nullable', 'integer'],
            'address' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'monthly_rent' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('rahnuma.currencies'))],
        ]);

        if (!empty($data['owner_id']) && !Owner::find($data['owner_id'])) {
            return response()->json(['message' => 'The selected owner does not belong to this Rahnama.'], 422);
        }

        $property = Property::create($data + [
            'code' => 'PR-'.date('ymd').'-'.random_int(1000, 9999),
            'status' => 'available',
            'visibility' => 'private',
        ]);

        if ($property->owner_id) {
            PropertyOwner::updateOrCreate(
                ['property_id' => $property->id, 'owner_id' => $property->owner_id],
                ['is_primary' => true, 'ownership_percent' => 100]
            );
        }

        $property->load('owner');

        return response()->json([
            'created' => true,
            'message' => 'Property created and selected. You can complete its full details later.',
            'item' => $this->propertyPayload($property),
        ]);
    }

    private function findOwnerDuplicate(array $data): ?Owner
    {
        if (!empty($data['tazkira_no'])) {
            $owner = Owner::where('tazkira_no', $data['tazkira_no'])->first();
            if ($owner) return $owner;
        }

        if (!empty($data['phone'])) {
            return Owner::where('phone', $data['phone'])->first();
        }

        return null;
    }

    private function propertyPayload(Property $property): array
    {
        return [
            'id' => $property->id,
            'text' => $property->code.' — '.$property->title,
            'code' => $property->code,
            'title' => $property->title,
            'owner_id' => $property->owner_id,
            'owner_name' => $property->owner?->name,
            'transaction_type' => $property->transaction_type,
            'price' => $property->price,
            'monthly_rent' => $property->monthly_rent,
            'deposit_amount' => $property->deposit_amount,
            'currency' => $property->currency,
            'address' => $property->address,
        ];
    }
}
