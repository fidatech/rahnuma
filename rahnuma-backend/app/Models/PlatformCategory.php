<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A kind of money in or money out, chosen when an entry is recorded. */
class PlatformCategory extends Model
{
    protected $guarded = [];
    protected $casts = ['is_system' => 'boolean'];
}
