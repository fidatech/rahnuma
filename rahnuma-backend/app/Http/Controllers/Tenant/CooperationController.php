<?php
namespace App\Http\Controllers\Tenant;
use App\Http\Controllers\Controller;
use App\Models\{CooperationRequest,Customer,Property};
use Illuminate\Http\Request;
class CooperationController extends Controller {
 public function index(){
   $agencyId=(int)auth()->user()->agency_id;
   $requests=CooperationRequest::withoutGlobalScopes()->where(fn($q)=>$q->where('agency_id',$agencyId)->orWhere('target_agency_id',$agencyId))->with(['property:id,agency_id,code,title'])->latest()->paginate(25);
   return view('tenant.cooperation',[
     'requests'=>$requests,
     'publicPropertyCount'=>Property::withoutGlobalScopes()->where('agency_id','!=',$agencyId)->whereIn('visibility',['public','featured'])->whereIn('status',['available','active'])->count(),
     'customerCount'=>Customer::count(),
   ]);
 }
 public function lookupProperties(Request $r){
   $agencyId=(int)auth()->user()->agency_id;$term=trim((string)$r->get('q',''));
   $q=Property::withoutGlobalScopes()->with('agency:id,name')->where('agency_id','!=',$agencyId)->whereIn('visibility',['public','featured'])->whereIn('status',['available','active']);
   if($term!=='')$q->where(fn($x)=>$x->where('code','like','%'.$term.'%')->orWhere('title','like','%'.$term.'%')->orWhere('address','like','%'.$term.'%'));
   $p=$q->orderByDesc('published_at')->paginate(20);
   return response()->json(['results'=>collect($p->items())->map(fn($x)=>['id'=>$x->id,'text'=>$x->code.' — '.$x->title.' · '.($x->agency?->name?:'Partner Rahnama')])->values(),'pagination'=>['more'=>$p->hasMorePages()]]);
 }
 public function store(Request $r){$d=$r->validate(['property_id'=>'required|integer','customer_id'=>'required|integer','requesting_share_percent'=>'required|numeric|min:0|max:100','message'=>'nullable|max:1000']);$p=Property::withoutGlobalScopes()->findOrFail($d['property_id']);abort_if($p->agency_id===auth()->user()->agency_id,422);abort_unless(Customer::find($d['customer_id']),422,'Invalid customer.');CooperationRequest::create($d+['target_agency_id'=>$p->agency_id,'status'=>'pending','target_share_percent'=>100-$d['requesting_share_percent']]);return back()->with('success','Cooperation request sent.');}
 public function respond(Request $r,int $id){$c=CooperationRequest::withoutGlobalScopes()->findOrFail($id);abort_unless($c->target_agency_id===auth()->user()->agency_id,403);$r->validate(['status'=>'required|in:accepted,rejected']);$c->update(['status'=>$r->status,'responded_at'=>now()]);return back();}
}
