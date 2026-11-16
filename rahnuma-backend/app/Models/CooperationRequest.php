<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class CooperationRequest extends Model {
    use BelongsToTenant;
    protected $guarded=[];
    protected $casts=['responded_at'=>'datetime','closed_at'=>'datetime'];
    public function property(){ return $this->belongsTo(Property::class)->withoutGlobalScopes(); }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function targetAgency(){ return $this->belongsTo(Agency::class,'target_agency_id'); }
}
