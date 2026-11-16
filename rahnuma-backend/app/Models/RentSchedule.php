<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class RentSchedule extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['due_date'=>'date','paid_at'=>'datetime']; }
