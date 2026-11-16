<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
class JournalEntry extends Model { use BelongsToTenant; protected $guarded=[]; protected $casts=['entry_date'=>'date']; }
