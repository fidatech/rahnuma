<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, Plan, Property, Province, Transaction, User};
use App\Services\AccountingService;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Hash};
use Illuminate\Support\Str;

class AgencyController extends Controller
{
    public function index(Request $r)
    {
        $agencies = Agency::with(['plan:id,name', 'province:id,name_en,name_fa,name_ps'])
            ->withCount(['users', 'properties' => fn ($q) => $q->withoutGlobalScopes()])
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$r->q.'%')->orWhere('license_number', 'like', '%'.$r->q.'%')->orWhere('phone', 'like', '%'.$r->q.'%')->orWhere('email', 'like', '%'.$r->q.'%')))
            ->when($r->filled('plan_id'), fn ($q) => $q->where('plan_id', $r->plan_id))
            ->when($r->status === 'active', fn ($q) => $q->where('is_active', 1))
            ->when($r->status === 'disabled', fn ($q) => $q->where('is_active', 0))
            ->when($r->status === 'verified', fn ($q) => $q->where('is_verified', 1))
            ->when($r->status === 'unverified', fn ($q) => $q->where('is_verified', 0))
            ->latest()->paginate(25)->withQueryString();

        return view('platform.agencies', [
            'agencies' => $agencies,
            'plans' => Plan::orderBy('monthly_price')->get(),
            'counts' => ['all' => Agency::count(), 'active' => Agency::where('is_active', 1)->count(), 'verified' => Agency::where('is_verified', 1)->count(), 'disabled' => Agency::where('is_active', 0)->count()],
        ]);
    }

    public function store(Request $r, AccountingService $acct)
    {
        $d = $r->validate([
            'name' => 'required|max:180', 'owner_name' => 'required|max:180', 'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|max:40', 'license_number' => 'nullable|max:80', 'plan_id' => 'nullable|exists:plans,id',
            'password' => 'required|min:8', 'trial_days' => 'nullable|integer|min:0|max:365',
        ]);

        $agency = Agency::create([
            'name' => $d['name'], 'slug' => Str::slug($d['name']).'-'.strtolower(Str::random(4)), 'phone' => $d['phone'] ?? null,
            'license_number' => $d['license_number'] ?? null, 'license_status' => 'unverified', 'plan_id' => $d['plan_id'] ?? null,
            'is_active' => 1, 'trial_ends_at' => now()->addDays((int) ($d['trial_days'] ?? 30)),
        ]);
        User::create(['agency_id' => $agency->id, 'name' => $d['owner_name'], 'email' => $d['email'], 'password' => Hash::make($d['password']), 'role' => 'agency_owner', 'locale' => 'fa', 'is_active' => 1]);
        $acct->ensureDefaultAccounts($agency);
        Audit::log('office.created', $agency, [], ['name' => $agency->name, 'owner' => $d['email']]);

        return redirect()->route('platform.agencies.show', $agency)->with('success', __('Rahnama created.'));
    }

    public function show(Agency $agency)
    {
        $agency->load(['plan', 'province', 'district', 'area']);

        return view('platform.agency', [
            'agency' => $agency,
            'users' => User::where('agency_id', $agency->id)->orderBy('name')->get(),
            'subscriptions' => $agency->subscriptions()->withoutGlobalScopes()->with('plan:id,name')->latest('starts_at')->limit(10)->get(),
            'plans' => Plan::orderBy('monthly_price')->get(),
            'provinces' => Province::orderBy('name_en')->get(['id', 'name_en', 'name_fa', 'name_ps']),
            'stats' => [
                'properties' => Property::withoutGlobalScopes()->where('agency_id', $agency->id)->count(),
                'public' => Property::withoutGlobalScopes()->where('agency_id', $agency->id)->whereIn('visibility', ['public', 'featured'])->count(),
                'transactions' => Transaction::withoutGlobalScopes()->where('agency_id', $agency->id)->count(),
                'users' => User::where('agency_id', $agency->id)->count(),
            ],
        ]);
    }

    public function update(Request $r, Agency $agency)
    {
        $d = $r->validate([
            'name' => 'required|max:180', 'phone' => 'nullable|max:40', 'whatsapp' => 'nullable|max:40', 'email' => 'nullable|email|max:180',
            'address' => 'nullable|max:500', 'province_id' => 'nullable|exists:provinces,id', 'plan_id' => 'nullable|exists:plans,id',
            'license_number' => 'nullable|max:80', 'license_status' => 'required|in:unverified,pending,verified,suspended,expired',
            'license_expires_at' => 'nullable|date', 'trial_ends_at' => 'nullable|date',
        ]);
        $d['is_verified'] = $r->boolean('is_verified');
        $d['is_active'] = $r->boolean('is_active');
        $d['marketplace_enabled'] = $r->boolean('marketplace_enabled');
        $d['marketplace_featured'] = $r->boolean('marketplace_featured');

        $old = $agency->only(array_keys($d));
        $agency->update($d);
        $this->refresh();
        Audit::log('office.updated', $agency, $old, $agency->only(array_keys($d)));

        return back()->with('success', __('Office updated.'));
    }

    public function toggle(Agency $agency)
    {
        $agency->update(['is_active' => ! $agency->is_active]);
        $this->refresh();
        Audit::log($agency->is_active ? 'office.enabled' : 'office.disabled', $agency);

        return back()->with('success', $agency->is_active ? __('Office enabled.') : __('Office disabled.'));
    }

    public function verify(Agency $agency)
    {
        $agency->update(['is_verified' => ! $agency->is_verified, 'license_status' => $agency->is_verified ? 'unverified' : 'verified']);
        $this->refresh();
        Audit::log($agency->is_verified ? 'office.verified' : 'office.unverified', $agency);

        return back()->with('success', $agency->is_verified ? __('Office marked as verified.') : __('Verified mark removed.'));
    }

    public function destroy(Request $r, Agency $agency)
    {
        // the name must be typed: everything the office has entered goes with it
        if (trim((string) $r->input('confirm_name')) !== $agency->name) {
            return back()->withErrors(['confirm_name' => __('Type the exact name of the office to delete it.')]);
        }

        Audit::log('office.deleted', $agency, ['name' => $agency->name, 'license_number' => $agency->license_number]);
        $agency->delete();
        $this->refresh();

        return redirect()->route('platform.agencies')->with('success', __('Office and all its records deleted.'));
    }

    private function refresh(): void
    {
        Cache::forget('rahnuma:marketplace:home:v9');
    }
}
