<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['old_values'=>'array','new_values'=>'array']; public function user(){return $this->belongsTo(User::class);} public function agency(){return $this->belongsTo(Agency::class);} }
