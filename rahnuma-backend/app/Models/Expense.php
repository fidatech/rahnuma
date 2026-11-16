<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class Expense extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['expense_date'=>'date']; }
