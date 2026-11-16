<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash;
class AuthController extends Controller { public function login(Request $r){$d=$r->validate(['email'=>'required|email','password'=>'required']);$u=\App\Models\User::where('email',$d['email'])->first();if(!$u||!Hash::check($d['password'],$u->password)||!$u->is_active)return response()->json(['message'=>'Invalid credentials'],422);$u->tokens()->delete();return ['token'=>$u->createToken('mobile')->plainTextToken,'user'=>$u->load('agency')];} public function me(Request $r){return $r->user()->load('agency');} public function logout(Request $r){$r->user()->currentAccessToken()?->delete();return ['ok'=>true];} }
