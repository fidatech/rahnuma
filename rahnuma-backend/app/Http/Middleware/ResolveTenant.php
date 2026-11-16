<?php
namespace App\Http\Middleware;
use App\Support\TenantContext; use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class ResolveTenant { public function handle(Request $request, Closure $next):Response { $user=$request->user(); if(!$user || !$user->agency_id) abort(403, __('A Rahnama workspace is required.')); if(!$user->is_active) abort(403, __('Account disabled.')); TenantContext::set((int)$user->agency_id); try{return $next($request);} finally{TenantContext::clear();} } }
