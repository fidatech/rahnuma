<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class SettaBook extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['issued_at'=>'date']; public function agency(){return $this->belongsTo(Agency::class);} }
