<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class TransactionParty extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['is_verified'=>'boolean']; }
