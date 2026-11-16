<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, AuditLog, PlatformEntry, Property, Subscription, Transaction, User};

class DashboardController extends Controller
{
    public function __invoke()
    {
        $month = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        $money = fn (string $direction) => PlatformEntry::where('direction', $direction)->whereBetween('entry_date', $month)
            ->selectRaw('currency, SUM(amount) as total')->groupBy('currency')->pluck('total', 'currency');

        return view('platform.dashboard', [
            'stats' => [
                'agencies' => Agency::count(),
                'active_agencies' => Agency::where('is_active', 1)->count(),
                'users' => User::count(),
                'properties' => Property::withoutGlobalScopes()->count(),
                'transactions' => Transaction::withoutGlobalScopes()->count(),
                'subscriptions' => Subscription::withoutGlobalScopes()->where('status', 'active')->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->count(),
            ],
            'moneyIn' => $money('in'),
            'moneyOut' => $money('out'),
            'expiring' => Subscription::withoutGlobalScopes()->with(['agency:id,name,slug', 'plan:id,name'])->where('status', 'active')
                ->whereNotNull('ends_at')->whereBetween('ends_at', [now(), now()->addDays(30)])->orderBy('ends_at')->limit(8)->get(),
            'trials' => Agency::whereNotNull('trial_ends_at')->whereBetween('trial_ends_at', [now(), now()->addDays(14)])->orderBy('trial_ends_at')->limit(8)->get(),
            'agencies' => Agency::with('plan')->latest()->limit(8)->get(),
            'activity' => AuditLog::withoutGlobalScopes()->with('user:id,name')->latest()->limit(10)->get(),
        ]);
    }
}
