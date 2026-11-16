<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
/**
 * Shows the system in English, Dari or Pashto. The language picked in the top bar (kept in the
 * session) comes first, then the language of the signed-in user, then APP_LOCALE.
 */
class SetLocale { public function handle(Request $request, Closure $next):Response { $known=array_keys(config('app.locales',['en'=>'English','fa'=>'دری','ps'=>'پښتو'])); $locale=($request->hasSession()?$request->session()->get('locale'):null) ?: ($request->user()?->locale ?: config('app.locale')); if(in_array($locale,$known,true)) app()->setLocale($locale); return $next($request); } }
