<?php
namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\{Customer,CustomerRequirement};
use App\Services\MatchingService;
use Illuminate\Http\Request;
class RequirementController extends Controller {
 public function index(){return view('tenant.requirements',['requirements'=>CustomerRequirement::with('customer:id,name,phone')->latest()->paginate(25),'customerCount'=>Customer::count()]);}
 public function store(Request $r){$data=$r->validate(['customer_id'=>'required|integer','transaction_type'=>'required','property_type'=>'required','province_id'=>'nullable|integer','district_id'=>'nullable|integer','area_id'=>'nullable|integer','min_price'=>'nullable|numeric','max_price'=>'nullable|numeric','currency'=>'required','min_bedrooms'=>'nullable|integer','max_bedrooms'=>'nullable|integer','min_area'=>'nullable|numeric','max_area'=>'nullable|numeric','notes'=>'nullable']);abort_unless(Customer::find($data['customer_id']),422,__('Invalid customer.'));CustomerRequirement::create($data+['is_active'=>1]);return back()->with('success', __('Requirement saved.'));}
 public function matches(CustomerRequirement $requirement,MatchingService $matching){return view('tenant.requirement_matches',['requirement'=>$requirement,'properties'=>$matching->propertiesForRequirement($requirement)]);}
}
