<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
    use BelongsToTenant;
    protected $guarded=[];
    protected $casts=['paid_at'=>'datetime'];
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function owner(){ return $this->belongsTo(Owner::class); }
    public function transaction(){ return $this->belongsTo(Transaction::class); }
    public function createdBy(){ return $this->belongsTo(User::class,'created_by'); }
}
