<?php
use Illuminate\Support\Facades\Schedule; use App\Models\{AgencyLicense,RentSchedule};
Schedule::call(function(){RentSchedule::withoutGlobalScopes()->where('status','upcoming')->whereDate('due_date','<=',now()->toDateString())->update(['status'=>'due']);RentSchedule::withoutGlobalScopes()->where('status','due')->whereDate('due_date','<',now()->subDays(7)->toDateString())->update(['status'=>'overdue']);AgencyLicense::where('status','active')->whereDate('expiry_date','<',now()->toDateString())->update(['status'=>'expired']);})->dailyAt('00:10');
