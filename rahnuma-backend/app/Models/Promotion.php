<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One paid placement on the marketplace: a sponsored or featured property, or a featured office. */
class Promotion extends Model
{
    protected $guarded = [];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'stopped_at' => 'datetime', 'amount' => 'decimal:2'];

    public const KINDS = ['sponsored', 'featured', 'featured_office'];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class)->withoutGlobalScopes();
    }
}
