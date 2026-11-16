<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, AuditLog, Setting, User};
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Artisan, Cache, DB, Hash, Storage};
use Illuminate\Validation\Rule;

/** The audit log, the settings of the platform, the state of the system and the administrator's own account. */
class SystemController extends Controller
{
    public function audit(Request $r)
    {
        $logs = AuditLog::withoutGlobalScopes()->with(['user:id,name,email', 'agency:id,name,slug'])
            ->when($r->filled('q'), fn ($q) => $q->where('event', 'like', '%'.$r->q.'%'))
            ->when($r->filled('user_id'), fn ($q) => $q->where('user_id', $r->user_id))
            ->when($r->filled('agency_id'), fn ($q) => $q->where('agency_id', $r->agency_id))
            ->when($r->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $r->date('from')))
            ->when($r->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $r->date('to')))
            ->latest()->paginate(40)->withQueryString();

        return view('platform.audit', [
            'logs' => $logs,
            'actors' => User::whereIn('id', AuditLog::withoutGlobalScopes()->select('user_id')->distinct())->orderBy('name')->get(['id', 'name']),
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function settings()
    {
        return view('platform.settings', ['saved' => Setting::map()]);
    }

    public function saveSettings(Request $r)
    {
        $d = $r->validate([
            'contact_phone' => 'nullable|max:60', 'contact_email' => 'nullable|email|max:180',
            'contact_address' => 'nullable|max:300', 'contact_hours' => 'nullable|max:200',
            'sale_baywafa_rate' => 'required|numeric|min:0|max:100', 'rent_multiplier' => 'required|numeric|min:0|max:12',
            'emblem' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:1024',
        ]);

        foreach (['contact_phone', 'contact_email', 'contact_address', 'contact_hours'] as $key) Setting::put($key, $d[$key] ?? null);
        // the rate is typed as a percentage and kept as a fraction
        Setting::put('sale_baywafa_rate', (string) ((float) $d['sale_baywafa_rate'] / 100));
        Setting::put('rent_multiplier', (string) (float) $d['rent_multiplier']);
        Setting::put('government_mode', $r->boolean('government_mode') ? '1' : '0');

        if ($r->hasFile('emblem')) {
            Setting::put('official_emblem', 'storage/'.$r->file('emblem')->store('platform', 'public'));
        } elseif ($r->boolean('remove_emblem')) {
            Setting::put('official_emblem', null);
        }

        Cache::forget('rahnuma:marketplace:home:v9');
        Audit::log('settings.updated', null, [], ['government_mode' => $r->boolean('government_mode')]);

        return back()->with('success', __('Settings saved.'));
    }

    public function system()
    {
        $tables = [];
        foreach (['agencies', 'users', 'properties', 'property_media', 'transactions', 'subscriptions', 'audit_logs', 'failed_jobs'] as $table) {
            try { $tables[$table] = DB::table($table)->count(); } catch (\Throwable) { $tables[$table] = null; }
        }

        $photos = 0;
        try {
            foreach (Storage::disk('public')->allFiles('properties') as $file) $photos += Storage::disk('public')->size($file);
        } catch (\Throwable) { $photos = null; }

        return view('platform.system', [
            'tables' => $tables,
            'photos' => $photos,
            'info' => [
                'Laravel' => app()->version(), 'PHP' => PHP_VERSION, 'Environment' => app()->environment(),
                'Debug mode' => config('app.debug') ? 'on' : 'off', 'Database' => config('database.default'),
                'Cache' => config('cache.default'), 'Sessions' => config('session.driver'), 'Queue' => config('queue.default'),
                'Time zone' => config('app.timezone'),
            ],
            'checks' => [
                'Storage link' => is_link(public_path('storage')) || is_dir(public_path('storage')),
                'Photo compression (GD with WebP)' => function_exists('imagewebp'),
                'Debug mode is off' => ! config('app.debug'),
            ],
        ]);
    }

    public function clearCache()
    {
        Cache::flush();
        try { Artisan::call('view:clear'); } catch (\Throwable) { /* the data cache is what matters */ }
        Audit::log('system.cache_cleared');

        return back()->with('success', __('Cache cleared.'));
    }

    public function account()
    {
        return view('platform.account', ['user' => auth()->user()]);
    }

    public function saveAccount(Request $r)
    {
        $user = $r->user();
        $d = $r->validate([
            'name' => 'required|max:180',
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'locale' => 'required|in:en,fa,ps',
        ]);
        $user->update($d);
        session(['locale' => $d['locale']]);
        Audit::log('account.updated', $user);

        return back()->with('success', __('Account updated.'));
    }

    public function savePassword(Request $r)
    {
        $d = $r->validate(['current_password' => 'required|current_password', 'password' => 'required|min:8|confirmed']);
        $r->user()->forceFill(['password' => Hash::make($d['password'])])->save();
        Audit::log('account.password_changed', $r->user());

        return back()->with('success', __('Password changed.'));
    }
}
