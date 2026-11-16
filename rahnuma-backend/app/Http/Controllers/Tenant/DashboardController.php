<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{Commission,Customer,Lead,Property,RentSchedule,Subscription,Transaction,Visit};

class DashboardController extends Controller
{
    public function __invoke()
    {
        $propertyAgg = Property::selectRaw("COUNT(*) as total, SUM(CASE WHEN status IN ('available','active') THEN 1 ELSE 0 END) as available_count, SUM(CASE WHEN visibility IN ('public','featured') AND status IN ('available','active') THEN 1 ELSE 0 END) as public_count")->first();
        $commissionAgg = Commission::selectRaw("COALESCE(SUM(CASE WHEN status != 'paid' THEN amount ELSE 0 END),0) as due_amount, SUM(CASE WHEN status != 'paid' THEN 1 ELSE 0 END) as due_count")->first();
        $rentAgg = RentSchedule::selectRaw("COALESCE(SUM(CASE WHEN status IN ('due','overdue') THEN amount ELSE 0 END),0) as due_amount, SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count")->first();

        // the paid period that runs now, or the trial when there is none
        $agency = auth()->user()->agency;
        $subscription = Subscription::with('plan:id,name')->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->orderByDesc('ends_at')->first();
        $until = $subscription?->ends_at ?? ($subscription ? null : $agency?->trial_ends_at);
        $from = $subscription?->starts_at ?? $agency?->created_at;
        $daysLeft = $until ? max(0, (int) ceil(now()->diffInHours($until, false) / 24)) : null;
        $daysAll = ($until && $from) ? max(1, (int) round($from->diffInDays($until))) : null;

        return view('tenant.dashboard', [
            'agency' => $agency,
            'subscription' => $subscription,
            'period' => ['until' => $until, 'left' => $daysLeft, 'all' => $daysAll, 'trial' => ! $subscription],

            'stats' => [
                'properties' => (int) $propertyAgg->total,
                'available_properties' => (int) $propertyAgg->available_count,
                'public_listings' => (int) $propertyAgg->public_count,
                'customers' => Customer::count(),
                'active_leads' => Lead::whereNotIn('status',['won','lost'])->count(),
                'transactions' => Transaction::count(),
                'commission_due' => (float) $commissionAgg->due_amount,
                'rent_due' => (float) $rentAgg->due_amount,
            ],
            'recent' => Property::with('owner:id,name')->latest()->limit(7)->get(['id','owner_id','code','title','transaction_type','monthly_rent','price','currency','status','visibility','created_at']),
            'recentTransactions' => Transaction::with('property:id,title')->latest()->limit(6)->get(['id','property_id','reference_no','transaction_type','amount','monthly_rent','currency','created_at']),
            'followUps' => Lead::with(['customer:id,name','property:id,title'])
                ->whereNotIn('status',['won','lost'])
                ->whereNotNull('next_follow_up_at')
                ->orderBy('next_follow_up_at')
                ->limit(5)->get(['id','customer_id','property_id','status','next_follow_up_at']),
            'upcomingVisits' => Visit::with(['customer:id,name','property:id,title'])
                ->whereIn('status',['scheduled','confirmed'])
                ->orderBy('scheduled_at')
                ->limit(5)->get(['id','customer_id','property_id','scheduled_at','status']),
            'overdueRentCount' => (int) $rentAgg->overdue_count,
            'dueCommissionCount' => (int) $commissionAgg->due_count,
        ]);
    }
}
