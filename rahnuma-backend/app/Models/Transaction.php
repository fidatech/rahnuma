<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Transaction extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['start_date'=>'date','end_date'=>'date','completed_at'=>'datetime']; public function property(){return $this->belongsTo(Property::class);} public function parties(){return $this->hasMany(TransactionParty::class);} public function commissions(){return $this->hasMany(Commission::class);} public function rentSchedules(){return $this->hasMany(RentSchedule::class);} public function agency(){return $this->belongsTo(Agency::class);} }
