<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model;
class CustomerRequirement extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['is_active'=>'boolean']; public function customer(){return $this->belongsTo(Customer::class);} }
