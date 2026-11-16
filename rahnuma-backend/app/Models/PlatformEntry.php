<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Money in or money out of the platform: a subscription payment, a sponsorship fee, hosting, salaries. */
class PlatformEntry extends Model
{
    protected $guarded = [];
    protected $casts = ['entry_date' => 'date', 'amount' => 'decimal:2'];

    public const IN = ['subscription', 'sponsorship', 'setup_fee', 'other_income'];
    public const OUT = ['hosting', 'salary', 'marketing', 'office', 'tax', 'other_expense'];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
