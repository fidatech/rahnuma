<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Commission extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['due_at'=>'date','paid_at'=>'datetime']; }
