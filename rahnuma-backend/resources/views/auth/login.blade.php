@extends('layouts.app')
@section('title','Sign in — Rahnuma')
@section('content')
<div class="login-shell"><div class="card login-card"><div class="login-mark">R</div><div class="eyebrow">Secure access</div><h1 style="font-size:27px;margin-bottom:8px">Sign in to Rahnuma</h1><p class="muted" style="margin-top:0;margin-bottom:22px">Access your Rahnama workspace, platform administration, or regulatory module.</p><form method="post">@csrf<div class="field"><label>Email address</label><input type="email" name="email" required autofocus value="{{ old('email') }}" placeholder="you@example.com"></div><div class="field"><label>Password</label><input type="password" name="password" required placeholder="••••••••"></div><label style="display:flex;align-items:center;gap:8px;color:var(--muted);font-size:12px;margin:5px 0 18px"><input style="width:auto" type="checkbox" name="remember" value="1"> Keep me signed in</label><button class="btn block">Sign in</button></form></div></div>
@endsection
