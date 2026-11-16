<?php
namespace App\Services;
use App\Models\Transaction; use Illuminate\Support\Str;
class VerificationService { public function assign(Transaction $tx):string { if(!$tx->verification_code){$tx->verification_code='RHM-'.strtoupper(Str::random(12));$tx->save();} return $tx->verification_code; } }
