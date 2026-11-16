<?php
namespace App\Http\Controllers\Platform;
use App\Http\Controllers\Controller; use App\Models\{Agency,Plan,Property,Subscription,Transaction,User};
class DashboardController extends Controller { public function __invoke(){return view('platform.dashboard',['stats'=>['agencies'=>Agency::count(),'active_agencies'=>Agency::where('is_active',1)->count(),'users'=>User::whereNotNull('agency_id')->count(),'properties'=>Property::withoutGlobalScopes()->count(),'transactions'=>Transaction::withoutGlobalScopes()->count(),'mrr'=>Subscription::where('status','active')->where('billing_cycle','monthly')->sum('amount')],'agencies'=>Agency::with('plan')->latest()->limit(20)->get(),'plans'=>Plan::all()]);} }
