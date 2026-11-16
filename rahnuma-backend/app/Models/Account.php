<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Account extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['is_system'=>'boolean','is_active'=>'boolean']; }
