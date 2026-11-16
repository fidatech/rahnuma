<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, Plan, PlatformEntry, Subscription};
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index(Request $r)
    {
        $base = Subscription::withoutGlobalScopes();
        $subscriptions = (clone $base)->with(['agency:id,name,slug', 'plan:id,name'])
            ->when($r->filled('agency_id'), fn ($q) => $q->where('agency_id', $r->agency_id))
            ->when($r->filled('plan_id'), fn ($q) => $q->where('plan_id', $r->plan_id))
            ->when($r->status === 'active', fn ($q) => $q->where('status', 'active')->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>', now())))
            ->when($r->status === 'expiring', fn ($q) => $q->where('status', 'active')->whereBetween('ends_at', [now(), now()->addDays(30)]))
            ->when($r->status === 'expired', fn ($q) => $q->where('ends_at', '<', now()))
            ->when($r->status === 'unpaid', fn ($q) => $q->whereNull('paid_at')->where('status', '!=', 'cancelled'))
            ->when($r->status === 'cancelled', fn ($q) => $q->where('status', 'cancelled'))
            ->latest('starts_at')->paginate(25)->withQueryString();

        return view('platform.subscriptions', [
            'subscriptions' => $subscriptions,
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
            'plans' => Plan::where('is_active', 1)->orderBy('monthly_price')->get(),
            'counts' => [
                'active' => (clone $base)->where('status', 'active')->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>', now()))->count(),
                'expiring' => (clone $base)->where('status', 'active')->whereBetween('ends_at', [now(), now()->addDays(30)])->count(),
                'expired' => (clone $base)->where('ends_at', '<', now())->count(),
                'unpaid' => (clone $base)->whereNull('paid_at')->where('status', '!=', 'cancelled')->count(),
            ],
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'agency_id' => 'required|exists:agencies,id', 'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,annual', 'starts_at' => 'required|date',
            'amount' => 'nullable|numeric|min:0', 'currency' => ['required', Rule::in(config('rahnuma.currencies'))],
            'notes' => 'nullable|max:1000',
        ]);
        $plan = Plan::findOrFail($d['plan_id']);
        $start = Carbon::parse($d['starts_at'])->startOfDay();

        $subscription = Subscription::create([
            'agency_id' => $d['agency_id'], 'plan_id' => $plan->id, 'status' => 'active', 'billing_cycle' => $d['billing_cycle'],
            'starts_at' => $start, 'ends_at' => $d['billing_cycle'] === 'annual' ? $start->copy()->addYear() : $start->copy()->addMonth(),
            'amount' => $d['amount'] ?? ($d['billing_cycle'] === 'annual' ? $plan->annual_price : $plan->monthly_price),
            'currency' => $d['currency'], 'notes' => $d['notes'] ?? null,
        ]);
        Agency::whereKey($d['agency_id'])->update(['plan_id' => $plan->id]);
        if ($r->boolean('paid')) $this->markPaid($subscription);
        Audit::log('subscription.created', $subscription, [], $subscription->only(['plan_id', 'billing_cycle', 'amount', 'currency']));

        return back()->with('success', __('Subscription added.'));
    }

    /** The payment arrived: note the day and write it to money in. */
    public function paid(int $subscription)
    {
        $subscription = Subscription::withoutGlobalScopes()->findOrFail($subscription);
        if (! $subscription->paid_at) {
            $this->markPaid($subscription);
            Audit::log('subscription.paid', $subscription, [], ['amount' => $subscription->amount, 'currency' => $subscription->currency]);
        }

        return back()->with('success', __('Payment recorded.'));
    }

    /** A new period of the same length, starting where the last one ends. */
    public function renew(int $subscription)
    {
        $old = Subscription::withoutGlobalScopes()->findOrFail($subscription);
        $start = $old->ends_at && $old->ends_at->isFuture() ? $old->ends_at->copy() : now()->startOfDay();
        $new = Subscription::create([
            'agency_id' => $old->agency_id, 'plan_id' => $old->plan_id, 'status' => 'active', 'billing_cycle' => $old->billing_cycle,
            'starts_at' => $start, 'ends_at' => $old->billing_cycle === 'annual' ? $start->copy()->addYear() : $start->copy()->addMonth(),
            'amount' => $old->amount, 'currency' => $old->currency,
        ]);
        Audit::log('subscription.renewed', $new);

        return back()->with('success', __('Subscription renewed.'));
    }

    public function cancel(int $subscription)
    {
        $subscription = Subscription::withoutGlobalScopes()->findOrFail($subscription);
        $subscription->update(['status' => 'cancelled', 'ends_at' => now()]);
        Audit::log('subscription.cancelled', $subscription);

        return back()->with('success', __('Subscription cancelled.'));
    }

    public function destroy(int $subscription)
    {
        $subscription = Subscription::withoutGlobalScopes()->findOrFail($subscription);
        Audit::log('subscription.deleted', $subscription, $subscription->only(['agency_id', 'plan_id', 'amount', 'currency']));
        $subscription->delete();

        return back()->with('success', __('Subscription deleted.'));
    }

    private function markPaid(Subscription $subscription): void
    {
        $subscription->update(['paid_at' => now()]);
        if ((float) $subscription->amount <= 0) return;

        PlatformEntry::create([
            'direction' => 'in', 'category' => 'subscription', 'amount' => $subscription->amount, 'currency' => $subscription->currency,
            'entry_date' => now()->toDateString(), 'agency_id' => $subscription->agency_id, 'subscription_id' => $subscription->id,
            'created_by' => auth()->id(),
        ]);
    }
}
