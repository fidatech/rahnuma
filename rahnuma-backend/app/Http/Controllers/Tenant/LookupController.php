<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{Customer, Owner, Property, Transaction, User};
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function __invoke(Request $request, string $type)
    {
        abort_unless(in_array($type, ['owners','customers','properties','transactions','users'], true), 404);

        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = match ($type) {
            'owners' => Owner::query()->select(['id','name','phone','tazkira_no']),
            'customers' => Customer::query()->select(['id','name','phone','tazkira_no']),
            'properties' => Property::query()->with('owner:id,name')->select([
                'id','owner_id','code','title','transaction_type','price','monthly_rent','deposit_amount','currency','address','status'
            ])->whereIn('status', ['available','active']),
            'transactions' => Transaction::query()->with('property:id,title')->select(['id','property_id','reference_no','transaction_type','status','created_at']),
            'users' => User::query()->select(['id','name','agency_id','is_active'])->where('agency_id', auth()->user()->agency_id)->where('is_active', true),
        };

        if ($term !== '') {
            $query->where(function ($q) use ($type, $term) {
                if ($type === 'owners' || $type === 'customers') {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('tazkira_no', 'like', "%{$term}%");
                } elseif ($type === 'properties') {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('address', 'like', "%{$term}%");
                } elseif ($type === 'transactions') {
                    $q->where('reference_no', 'like', "%{$term}%")
                        ->orWhereHas('property', fn ($p) => $p->where('title', 'like', "%{$term}%"));
                } else {
                    $q->where('name', 'like', "%{$term}%");
                }
            });
        }

        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        $results = collect($paginator->items())->map(function ($item) use ($type) {
            return match ($type) {
                'owners' => [
                    'id' => $item->id,
                    'text' => $item->name.($item->phone ? ' · '.$item->phone : ''),
                ],
                'customers' => [
                    'id' => $item->id,
                    'text' => $item->name.($item->phone ? ' · '.$item->phone : ''),
                ],
                'properties' => [
                    'id' => $item->id,
                    'text' => $item->code.' — '.$item->title,
                    'code' => $item->code,
                    'title' => $item->title,
                    'owner_id' => $item->owner_id,
                    'owner_name' => $item->owner?->name,
                    'transaction_type' => $item->transaction_type,
                    'price' => $item->price,
                    'monthly_rent' => $item->monthly_rent,
                    'deposit_amount' => $item->deposit_amount,
                    'currency' => $item->currency,
                    'address' => $item->address,
                ],
                'transactions' => [
                    'id' => $item->id,
                    'text' => $item->reference_no.' · '.strtoupper($item->transaction_type).($item->property?->title ? ' · '.$item->property->title : ''),
                ],
                'users' => [
                    'id' => $item->id,
                    'text' => $item->name,
                ],
            };
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ])->header('Cache-Control', 'private, max-age=15');
    }
}
