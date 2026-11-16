<?php
namespace App\Models;
use App\Models\Concerns\BelongsToTenant; use Illuminate\Database\Eloquent\Model;
class JournalLine extends Model { use BelongsToTenant; protected $guarded=[]; public function account(){return $this->belongsTo(Account::class);} public function journalEntry(){return $this->belongsTo(JournalEntry::class);} }
