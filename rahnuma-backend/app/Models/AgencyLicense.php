<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AgencyLicense extends Model { protected $guarded=[]; protected $casts=['issue_date'=>'date','expiry_date'=>'date','verified_at'=>'datetime']; public function agency(){return $this->belongsTo(Agency::class);} }
