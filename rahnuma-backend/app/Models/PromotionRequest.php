<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** A request of an office for a paid placement, with the proof of its payment. */
class PromotionRequest extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
    protected $casts = ['reviewed_at' => 'datetime', 'amount' => 'decimal:2'];

    public const KINDS = ['sponsored', 'featured'];
    public const DAYS = [7, 15, 30];
    public const METHODS = ['hesabpay', 'aurumpay', 'mobile_money', 'cash'];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class)->withoutGlobalScopes();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
