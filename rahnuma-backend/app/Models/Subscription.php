<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Subscription extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','paid_at'=>'datetime']; public function agency(){return $this->belongsTo(Agency::class);} public function plan(){return $this->belongsTo(Plan::class);} }
