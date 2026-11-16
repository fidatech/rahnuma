<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function showLogin(){return view('auth.login');}
 public function login(Request $r){$data=$r->validate(['email'=>'required|email','password'=>'required']); if(!Auth::attempt($data,$r->boolean('remember'))) return back()->withErrors(['email'=>'Invalid credentials'])->onlyInput('email'); $r->session()->regenerate(); $u=$r->user(); return redirect()->intended($u->isPlatformAdmin()?route('platform.dashboard'):($u->isGovernment()?route('government.dashboard'):route('tenant.dashboard')));}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect('/login');}
 public function locale(string $locale){abort_unless(in_array($locale,['en','fa','ps']),404);session(['locale'=>$locale]);return back();}
}
