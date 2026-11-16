<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function primaryProperties(){ return $this->hasMany(Property::class, 'owner_id'); }
    public function propertyOwnerships(){ return $this->hasMany(PropertyOwner::class); }
    public function properties(){ return $this->belongsToMany(Property::class, 'property_owners')->withPivot(['ownership_percent','is_primary'])->withTimestamps(); }
}
