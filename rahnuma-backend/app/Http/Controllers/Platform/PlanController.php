<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, Plan, Subscription};
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        return view('platform.plans', [
            'plans' => Plan::orderBy('monthly_price')->get(),
            'offices' => Agency::selectRaw('plan_id, COUNT(*) as total')->whereNotNull('plan_id')->groupBy('plan_id')->pluck('total', 'plan_id'),
        ]);
    }

    public function store(Request $r)
    {
        $plan = Plan::create($this->validated($r));
        Audit::log('plan.created', $plan, [], $plan->only(['name', 'code', 'monthly_price', 'annual_price']));

        return back()->with('success', __('Plan created.'));
    }

    public function update(Request $r, Plan $plan)
    {
        $old = $plan->only(['name', 'code', 'monthly_price', 'annual_price', 'user_limit', 'property_limit', 'is_active']);
        $plan->update($this->validated($r, $plan));
        Audit::log('plan.updated', $plan, $old, $plan->only(array_keys($old)));

        return back()->with('success', __('Plan updated.'));
    }

    public function destroy(Plan $plan)
    {
        if (Subscription::withoutGlobalScopes()->where('plan_id', $plan->id)->exists()) {
            return back()->withErrors(['plan' => __('This plan has subscriptions. Switch it off instead of deleting it.')]);
        }
        Audit::log('plan.deleted', $plan, $plan->only(['name', 'code']));
        $plan->delete();

        return back()->with('success', __('Plan deleted.'));
    }

    private function validated(Request $r, ?Plan $plan = null): array
    {
        $d = $r->validate([
            'name' => 'required|max:120',
            'code' => ['required', 'max:40', 'alpha_dash', Rule::unique('plans', 'code')->ignore($plan?->id)],
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
            'user_limit' => 'nullable|integer|min:1',
            'property_limit' => 'nullable|integer|min:1',
            'features' => 'nullable|string|max:2000',
        ]);
        // one feature per line
        $d['features'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($d['features'] ?? '')))));
        $d['is_active'] = $r->boolean('is_active');

        return $d;
    }
}
