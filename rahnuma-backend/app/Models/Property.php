<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
    protected $casts = [
        'parking' => 'boolean', 'electricity' => 'boolean', 'solar' => 'boolean', 'water' => 'boolean',
        'well' => 'boolean', 'heating' => 'boolean', 'cooling' => 'boolean', 'furnished' => 'boolean',
        'internet' => 'boolean', 'security' => 'boolean', 'elevator' => 'boolean', 'balcony' => 'boolean',
        'garden' => 'boolean', 'basement' => 'boolean', 'storage' => 'boolean',
        'featured_at' => 'datetime', 'featured_until' => 'datetime', 'published_at' => 'datetime', 'is_sponsored' => 'boolean', 'sponsored_until' => 'datetime',
    ];

    public function agency(){ return $this->belongsTo(Agency::class); }
    public function owner(){ return $this->belongsTo(Owner::class); }
    public function ownerships(){ return $this->hasMany(PropertyOwner::class); }
    public function owners(){ return $this->belongsToMany(Owner::class, 'property_owners')->withPivot(['ownership_percent','is_primary'])->withTimestamps(); }
    public function media(){ return $this->hasMany(PropertyMedia::class)->orderBy('sort_order'); }
    public function coverMedia(){ return $this->hasOne(PropertyMedia::class)->where('is_cover', true)->orderBy('sort_order'); }
    public function area(){ return $this->belongsTo(Area::class); }
    public function district(){ return $this->belongsTo(District::class); }
    public function province(){ return $this->belongsTo(Province::class); }
}
