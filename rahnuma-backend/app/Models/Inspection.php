<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Inspection extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['inspected_at'=>'datetime']; public function agency(){return $this->belongsTo(Agency::class);} }
