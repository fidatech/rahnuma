<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class EnsureTenantAccess { public function handle(Request $request, Closure $next):Response { $a=$request->user()?->agency; if(!$a||!$a->is_active) abort(403, __('Rahnama account is inactive.')); $active=$a->activeSubscription()->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>',now()))->exists(); if(!$active && !($a->trial_ends_at && $a->trial_ends_at->isFuture())) abort(402, __('Subscription or trial has expired.')); return $next($request); } }
