<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Inquiry extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['preferred_visit_at'=>'datetime']; }
