<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Hash};
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('tenant.settings', [
            'agency' => auth()->user()->agency,
            'user' => auth()->user(),
            'provinces' => Cache::remember('rahnuma:provinces:list', 3600, fn () => Province::orderBy('name_en')->get(['id','name_en','name_fa'])),
        ]);
    }

    public function updateCompany(Request $request)
    {
        $agency = auth()->user()->agency;
        $data = $request->validate([
            'name' => 'required|max:180',
            'tagline' => 'nullable|max:180',
            'description' => 'nullable|max:5000',
            'license_number' => 'nullable|max:120',
            'phone' => 'nullable|max:50',
            'secondary_phone' => 'nullable|max:50',
            'whatsapp' => 'nullable|max:50',
            'email' => 'nullable|email|max:180',
            'website' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'telegram_url' => 'nullable|url|max:255',
            'address' => 'nullable|max:1000',
            'province_id' => 'nullable|exists:provinces,id',
            'office_hours' => 'nullable|max:180',
            'languages' => 'nullable|array',
            'languages.*' => 'in:fa,ps,en,uz',
            'logo' => 'nullable|image|max:4096',
            'cover' => 'nullable|image|max:8192',
        ]);

        unset($data['logo'], $data['cover']);
        if ($request->hasFile('logo')) $data['logo_path'] = $request->file('logo')->store('agencies/'.$agency->id.'/brand', 'public');
        if ($request->hasFile('cover')) $data['cover_path'] = $request->file('cover')->store('agencies/'.$agency->id.'/brand', 'public');
        $data['profile_completed_at'] = now();
        $agency->update($data);
        Cache::forget('rahnuma:marketplace:home:v8');

        return back()->with('success', 'Company profile updated. The public marketplace profile now uses these details.');
    }

    public function updateMarketplace(Request $request)
    {
        $data = $request->validate([
            'default_currency' => ['required', Rule::in(config('rahnuma.currencies'))],
            'default_locale' => ['required', Rule::in(['fa','ps','en','uz'])],
            'timezone' => 'required|max:80',
            'marketplace_enabled' => 'nullable|boolean',
            'marketplace_show_phone' => 'nullable|boolean',
            'marketplace_show_whatsapp' => 'nullable|boolean',
            'marketplace_show_address' => 'nullable|boolean',
            'marketplace_show_license' => 'nullable|boolean',
            'marketplace_show_exact_location' => 'nullable|boolean',
        ]);

        foreach (['marketplace_enabled','marketplace_show_phone','marketplace_show_whatsapp','marketplace_show_address','marketplace_show_license','marketplace_show_exact_location'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        auth()->user()->agency->update($data);
        Cache::forget('rahnuma:marketplace:home:v8');

        return back()->with('success', 'System and marketplace preferences updated.');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|max:150',
            'email' => ['required','email','max:180',Rule::unique('users','email')->ignore($user->id)],
            'phone' => 'nullable|max:50',
            'job_title' => 'nullable|max:120',
            'locale' => ['required', Rule::in(['fa','ps','en','uz'])],
            'avatar' => 'nullable|image|max:3072',
        ]);
        unset($data['avatar']);
        if ($request->hasFile('avatar')) $data['avatar_path'] = $request->file('avatar')->store('users/'.$user->id, 'public');
        $user->update($data);
        return back()->with('success', 'Your profile was updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required','current_password'],
            'password' => ['required','confirmed','min:8'],
        ]);
        auth()->user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }
}
