<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable { use HasApiTokens,HasFactory,Notifiable; protected $guarded=[]; protected $hidden=['password','remember_token']; protected function casts():array{return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];} public function agency(){return $this->belongsTo(Agency::class);} public function isGovernment():bool{return in_array($this->role,['government_admin','inspector']);} public function isPlatformAdmin():bool{return $this->role==='platform_admin';} }
