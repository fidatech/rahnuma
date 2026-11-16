<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Violation extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['detected_at'=>'datetime','resolved_at'=>'datetime']; public function agency(){return $this->belongsTo(Agency::class);} }
