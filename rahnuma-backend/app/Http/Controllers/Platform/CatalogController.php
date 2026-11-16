<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, Property, Transaction};
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Every listing and every deal of every office, in one place. */
class CatalogController extends Controller
{
    public function properties(Request $r)
    {
        $base = Property::withoutGlobalScopes();
        $properties = (clone $base)->with(['agency:id,name,slug', 'coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order', 'province:id,name_en,name_fa,name_ps'])
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', '%'.$r->q.'%')->orWhere('code', 'like', '%'.$r->q.'%')))
            ->when($r->filled('agency_id'), fn ($q) => $q->where('agency_id', $r->agency_id))
            ->when($r->filled('visibility'), fn ($q) => $q->where('visibility', $r->visibility))
            ->when($r->filled('status'), fn ($q) => $q->where('status', $r->status))
            ->when($r->filled('transaction_type'), fn ($q) => $q->where('transaction_type', $r->transaction_type))
            ->latest()->paginate(25)->withQueryString();

        return view('platform.properties', [
            'properties' => $properties,
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
            'counts' => [
                'all' => (clone $base)->count(),
                'public' => (clone $base)->whereIn('visibility', ['public', 'featured'])->count(),
                'private' => (clone $base)->where('visibility', 'private')->count(),
                'sponsored' => (clone $base)->where('is_sponsored', 1)->count(),
            ],
        ]);
    }

    public function visibility(Request $r, Property $property)
    {
        // a featured placement is paid for: it is given on the promotions page, not here
        $d = $r->validate(['visibility' => 'required|in:private,public']);
        $old = $property->only(['visibility']);
        $property->update([
            'visibility' => $d['visibility'],
            'featured_at' => null,
            'featured_until' => null,
            'published_at' => $d['visibility'] === 'private' ? $property->published_at : ($property->published_at ?: now()),
        ]);
        Cache::forget('rahnuma:marketplace:home:v9');
        Audit::log('property.visibility', $property, $old, ['visibility' => $d['visibility']]);

        return back()->with('success', __('Listing updated.'));
    }

    public function destroyProperty(Property $property)
    {
        Audit::log('property.deleted', $property, $property->only(['code', 'title', 'agency_id']));
        try {
            $property->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['property' => __('This property has deals recorded. Hide it instead of deleting it.')]);
        }
        Cache::forget('rahnuma:marketplace:home:v9');

        return back()->with('success', __('Property deleted.'));
    }

    public function transactions(Request $r)
    {
        $base = Transaction::withoutGlobalScopes();
        $transactions = (clone $base)->with(['agency:id,name,slug', 'property' => fn ($q) => $q->withoutGlobalScopes()->select('id', 'title', 'code')])
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('reference_no', 'like', '%'.$r->q.'%')->orWhere('verification_code', 'like', '%'.$r->q.'%')->orWhere('setta_number', 'like', '%'.$r->q.'%')))
            ->when($r->filled('agency_id'), fn ($q) => $q->where('agency_id', $r->agency_id))
            ->when($r->filled('transaction_type'), fn ($q) => $q->where('transaction_type', $r->transaction_type))
            ->when($r->filled('status'), fn ($q) => $q->where('status', $r->status))
            ->latest()->paginate(30)->withQueryString();

        return view('platform.transactions', [
            'transactions' => $transactions,
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
            'counts' => [
                'all' => (clone $base)->count(),
                'sale' => (clone $base)->where('transaction_type', 'sale')->count(),
                'rent' => (clone $base)->where('transaction_type', 'rent')->count(),
                'bay_wafa' => (clone $base)->where('transaction_type', 'bay_wafa')->count(),
            ],
            'statuses' => (clone $base)->select('status')->distinct()->pluck('status'),
        ]);
    }
}
