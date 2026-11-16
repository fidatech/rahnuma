<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class PropertyOwner extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['is_primary'=>'boolean']; }
