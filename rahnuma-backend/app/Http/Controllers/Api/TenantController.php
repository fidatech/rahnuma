<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Customer, Lead, Owner, Payment, Property, Transaction, Visit};
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function dashboard()
    {
        $propertyStats = Property::selectRaw("COUNT(*) total, SUM(CASE WHEN status IN ('available','active') THEN 1 ELSE 0 END) available_count, SUM(CASE WHEN visibility IN ('public','featured') AND status IN ('available','active') THEN 1 ELSE 0 END) public_count")->first();
        return [
            'properties' => (int) $propertyStats->total,
            'available_properties' => (int) $propertyStats->available_count,
            'public_listings' => (int) $propertyStats->public_count,
            'customers' => Customer::count(),
            'leads' => Lead::whereNotIn('status',['won','lost'])->count(),
            'transactions' => Transaction::count(),
        ];
    }

    public function properties()
    {
        return Property::with(['coverMedia:id,property_id,path,thumbnail_path,is_cover,sort_order','owner:id,name,phone'])->latest()->paginate(30);
    }

    public function storeProperty(Request $request)
    {
        $data = $request->validate([
            'title'=>'required','property_type'=>'required','transaction_type'=>'required','visibility'=>'required','status'=>'required','currency'=>'required',
            'price'=>'nullable|numeric','monthly_rent'=>'nullable|numeric','address'=>'nullable'
        ]);
        return Property::create($data + ['code'=>'PR-'.date('ymd').'-'.random_int(1000,9999),'published_at'=>in_array($data['visibility'],['public','featured'], true)?now():null]);
    }

    public function customers(){ return Customer::latest()->paginate(30); }
    public function owners(){ return Owner::latest()->paginate(30); }
    public function leads(){ return Lead::with(['customer:id,name,phone','property:id,code,title'])->latest()->paginate(30); }
    public function visits(){ return Visit::with(['customer:id,name,phone','property:id,code,title'])->latest()->paginate(30); }
    public function transactions(){ return Transaction::with('property:id,code,title')->latest()->paginate(30); }
    public function payments(){ return Payment::latest()->paginate(30); }
}
