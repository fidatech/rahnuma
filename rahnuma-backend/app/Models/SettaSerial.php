<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class SettaSerial extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['assigned_at'=>'datetime','used_at'=>'datetime']; }
