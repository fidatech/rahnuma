<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Agency, Property};
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    private function cardRelations(): array
    {
        return [
            'agency:id,name,slug,logo_path,is_verified',
            'coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order',
            'province:id,name_en,name_fa,name_ps',
            'district:id,name_en,name_fa,name_ps',
            'area:id,name_en,name_fa,name_ps',
        ];
    }

    public function properties(Request $request)
    {
        $query = Property::withoutGlobalScopes()
            ->with($this->cardRelations())
            ->whereHas('agency', fn ($a) => $a->where('is_active', 1)->where('marketplace_enabled', 1))
            ->whereIn('visibility', ['public','featured'])
            ->whereIn('status', ['available','active']);

        foreach (['property_type','transaction_type','province_id','district_id','area_id'] as $field) {
            if ($request->filled($field)) $query->where($field, $request->input($field));
        }
        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(fn ($q) => $q->where('title','like','%'.$term.'%')->orWhere('address','like','%'.$term.'%')->orWhere('code','like','%'.$term.'%'));
        }

        return $query->orderByDesc('is_sponsored')->orderByRaw('featured_at is null')->latest('published_at')->paginate(20);
    }

    public function property(int $id)
    {
        return Property::withoutGlobalScopes()
            ->with(['agency','media:id,property_id,path,thumbnail_path,is_cover,sort_order','province','district','area'])
            ->whereHas('agency', fn ($a) => $a->where('is_active', 1)->where('marketplace_enabled', 1))
            ->whereIn('visibility', ['public','featured'])
            ->findOrFail($id);
    }

    public function agencies()
    {
        return Agency::where('is_active', 1)->where('marketplace_enabled', 1)
            ->withCount(['properties' => fn ($q) => $q->whereIn('visibility',['public','featured'])->whereIn('status',['available','active'])])
            ->orderByDesc('marketplace_featured')->orderByDesc('is_verified')->paginate(30);
    }
}
