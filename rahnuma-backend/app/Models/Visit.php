<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Visit extends Model {
    use BelongsToTenant;
    protected $guarded=[];
    protected $casts=['scheduled_at'=>'datetime'];
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function property(){ return $this->belongsTo(Property::class); }
    public function assignedUser(){ return $this->belongsTo(User::class,'assigned_user_id'); }
}
