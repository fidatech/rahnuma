@extends('layouts.workspace')
@section('title','Settings — Rahnuma')
@section('workspace')
<div class="settings-head">
    <div>
        <div class="eyebrow">Workspace configuration</div>
        <h1>Settings</h1>
        <p>Manage your Rahnama identity, marketplace presence, defaults and personal profile from one place.</p>
    </div>
    <a class="btn secondary" href="{{ route('marketplace.agency',$agency) }}" target="_blank">View public profile ↗</a>
</div>

<div class="settings-nav" data-settings-nav>
    <a href="#company" class="active">Company profile</a>
    <a href="#marketplace">Marketplace & system</a>
    <a href="#profile">My profile</a>
    <a href="#security">Security</a>
</div>

<div class="settings-layout">
    <main class="settings-main">
        <section class="settings-section" id="company">
            <div class="settings-section__head">
                <div><span>Company identity</span><h2>How your Rahnama appears publicly</h2><p>This information powers your marketplace profile and property attribution.</p></div>
                <div class="settings-status {{ $agency->profile_completed_at?'complete':'' }}"><i></i>{{ $agency->profile_completed_at?'Profile configured':'Needs setup' }}</div>
            </div>
            <form method="post" enctype="multipart/form-data" action="{{ route('tenant.settings.company') }}">@csrf
                <div class="brand-settings-card">
                    <div class="brand-cover-preview" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif>
                        <label class="brand-cover-action">Change cover<input type="file" name="cover" accept="image/*"></label>
                    </div>
                    <div class="brand-identity-row">
                        <label class="brand-logo-uploader">
                            @if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}" alt="{{ $agency->name }}">@else<span>{{ strtoupper(substr($agency->name,0,1)) }}</span>@endif
                            <b>Change logo</b><input type="file" name="logo" accept="image/*">
                        </label>
                        <div><strong>{{ $agency->name }}</strong><small>{{ $agency->tagline ?: 'Add a short tagline for your public profile' }}</small></div>
                    </div>
                </div>

                <div class="settings-form-card">
                    <div class="settings-form-card__head"><div><b>Business details</b><span>Primary information customers use to recognize your office.</span></div></div>
                    <div class="settings-grid">
                        <div class="field span-2"><label>Rahnama / company name</label><input name="name" required value="{{ old('name',$agency->name) }}"></div>
                        <div class="field span-2"><label>Public tagline</label><input name="tagline" value="{{ old('tagline',$agency->tagline) }}" placeholder="Trusted property guidance in Kabul"></div>
                        <div class="field"><label>License number</label><input name="license_number" value="{{ old('license_number',$agency->license_number) }}"></div>
                        <div class="field"><label>Province</label><select name="province_id" data-placeholder="Choose province"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('province_id',$agency->province_id)==$p->id)>{{ $p->name_en }} / {{ $p->name_fa }}</option>@endforeach</select></div>
                        <div class="field span-4"><label>Company introduction</label><textarea name="description" rows="5" placeholder="Tell property seekers about your office, specialties and service areas.">{{ old('description',$agency->description) }}</textarea></div>
                    </div>
                </div>

                <div class="settings-form-card">
                    <div class="settings-form-card__head"><div><b>Contact & office</b><span>Customers can use these details from your public profile.</span></div></div>
                    <div class="settings-grid">
                        <div class="field"><label>Primary phone</label><input name="phone" value="{{ old('phone',$agency->phone) }}"></div>
                        <div class="field"><label>Secondary phone</label><input name="secondary_phone" value="{{ old('secondary_phone',$agency->secondary_phone) }}"></div>
                        <div class="field"><label>WhatsApp</label><input name="whatsapp" value="{{ old('whatsapp',$agency->whatsapp) }}"></div>
                        <div class="field"><label>Public email</label><input type="email" name="email" value="{{ old('email',$agency->email) }}"></div>
                        <div class="field span-2"><label>Office address</label><input name="address" value="{{ old('address',$agency->address) }}"></div>
                        <div class="field"><label>Office hours</label><input name="office_hours" value="{{ old('office_hours',$agency->office_hours) }}" placeholder="Sat–Thu, 8:00–17:00"></div>
                        <div class="field"><label>Website</label><input type="url" name="website" value="{{ old('website',$agency->website) }}" placeholder="https://"></div>
                    </div>
                </div>

                <div class="settings-form-card">
                    <div class="settings-form-card__head"><div><b>Languages & social presence</b><span>Help customers know how they can communicate with your team.</span></div></div>
                    <div class="settings-grid">
                        <div class="field span-2"><label>Languages spoken</label><select name="languages[]" multiple data-placeholder="Select languages">@foreach(['fa'=>'Dari','ps'=>'Pashto','en'=>'English','uz'=>'Uzbek'] as $k=>$v)<option value="{{ $k }}" @selected(in_array($k,old('languages',$agency->languages?:['fa','ps'])))>{{ $v }}</option>@endforeach</select></div>
                        <div class="field span-2"><label>Facebook</label><input type="url" name="facebook_url" value="{{ old('facebook_url',$agency->facebook_url) }}" placeholder="https://facebook.com/..."></div>
                        <div class="field span-2"><label>Instagram</label><input type="url" name="instagram_url" value="{{ old('instagram_url',$agency->instagram_url) }}" placeholder="https://instagram.com/..."></div>
                        <div class="field span-2"><label>Telegram</label><input type="url" name="telegram_url" value="{{ old('telegram_url',$agency->telegram_url) }}" placeholder="https://t.me/..."></div>
                    </div>
                </div>
                <div class="settings-savebar"><div><b>Company profile</b><span>Changes are reflected on the marketplace immediately.</span></div><button class="btn">Save company profile</button></div>
            </form>
        </section>

        <section class="settings-section" id="marketplace">
            <div class="settings-section__head"><div><span>Marketplace & defaults</span><h2>Control exposure and workspace behavior</h2><p>Choose what the public can see and the defaults your team works with.</p></div></div>
            <form method="post" action="{{ route('tenant.settings.marketplace') }}">@csrf
                <div class="settings-form-card">
                    <div class="settings-form-card__head"><div><b>Public profile privacy</b><span>These switches never expose landlord/customer private data.</span></div></div>
                    <div class="settings-switch-list">
                        @foreach([
                            'marketplace_enabled'=>['Publish Rahnama profile','Allow this company and its public listings to appear in the marketplace.'],
                            'marketplace_show_phone'=>['Show phone number','Display the company phone on public pages.'],
                            'marketplace_show_whatsapp'=>['Show WhatsApp','Display a direct WhatsApp contact action.'],
                            'marketplace_show_address'=>['Show office address','Display the office address on the Rahnama profile.'],
                            'marketplace_show_license'=>['Show license number','Display the license reference provided by your office.'],
                            'marketplace_show_exact_location'=>['Allow precise office location','Allow future map experiences to use the exact office coordinates.'],
                        ] as $key=>$copy)
                        <label class="settings-switch"><div><b>{{ $copy[0] }}</b><span>{{ $copy[1] }}</span></div><input type="checkbox" name="{{ $key }}" value="1" @checked(old($key,$agency->$key))><i></i></label>
                        @endforeach
                    </div>
                </div>
                <div class="settings-form-card">
                    <div class="settings-form-card__head"><div><b>Workspace defaults</b><span>Used as sensible defaults when your team creates new records.</span></div></div>
                    <div class="settings-grid">
                        <div class="field"><label>Default currency</label><select name="default_currency">@foreach(config('rahnuma.currencies') as $c)<option value="{{ $c }}" @selected(old('default_currency',$agency->default_currency?:'AFN')===$c)>{{ $c }}</option>@endforeach</select></div>
                        <div class="field"><label>Default language</label><select name="default_locale">@foreach(['fa'=>'Dari','ps'=>'Pashto','en'=>'English','uz'=>'Uzbek'] as $k=>$v)<option value="{{ $k }}" @selected(old('default_locale',$agency->default_locale?:'fa')===$k)>{{ $v }}</option>@endforeach</select></div>
                        <div class="field span-2"><label>Timezone</label><select name="timezone"><option value="Asia/Kabul" @selected(($agency->timezone?:'Asia/Kabul')==='Asia/Kabul')>Asia/Kabul (Afghanistan)</option><option value="Asia/Dubai" @selected($agency->timezone==='Asia/Dubai')>Asia/Dubai</option></select></div>
                    </div>
                </div>
                <div class="settings-savebar"><div><b>Marketplace & system</b><span>Keep these settings aligned with how your office works.</span></div><button class="btn">Save preferences</button></div>
            </form>
        </section>

        <section class="settings-section" id="profile">
            <div class="settings-section__head"><div><span>Personal profile</span><h2>Your workspace identity</h2><p>Used inside Rahnuma for ownership, assignments and audit trails.</p></div></div>
            <form method="post" enctype="multipart/form-data" action="{{ route('tenant.settings.profile') }}">@csrf
                <div class="settings-form-card">
                    <div class="profile-settings-row">
                        <label class="profile-avatar-uploader">@if($user->avatar_path)<img src="{{ asset('storage/'.$user->avatar_path) }}">@else<span>{{ strtoupper(substr($user->name,0,1)) }}</span>@endif<b>Change photo</b><input type="file" name="avatar" accept="image/*"></label>
                        <div class="settings-grid profile-settings-fields">
                            <div class="field"><label>Full name</label><input name="name" required value="{{ old('name',$user->name) }}"></div>
                            <div class="field"><label>Job title</label><input name="job_title" value="{{ old('job_title',$user->job_title) }}" placeholder="Agency owner"></div>
                            <div class="field"><label>Email</label><input type="email" name="email" required value="{{ old('email',$user->email) }}"></div>
                            <div class="field"><label>Phone</label><input name="phone" value="{{ old('phone',$user->phone) }}"></div>
                            <div class="field span-2"><label>My interface language</label><select name="locale">@foreach(['fa'=>'Dari','ps'=>'Pashto','en'=>'English','uz'=>'Uzbek'] as $k=>$v)<option value="{{ $k }}" @selected(old('locale',$user->locale)===$k)>{{ $v }}</option>@endforeach</select></div>
                        </div>
                    </div>
                </div>
                <div class="settings-savebar"><div><b>Personal profile</b><span>Used for internal attribution and communication.</span></div><button class="btn">Save my profile</button></div>
            </form>
        </section>

        <section class="settings-section" id="security">
            <div class="settings-section__head"><div><span>Security</span><h2>Change password</h2><p>Choose a strong password unique to your Rahnuma account.</p></div></div>
            <form method="post" action="{{ route('tenant.settings.password') }}">@csrf
                <div class="settings-form-card"><div class="settings-grid">
                    <div class="field span-2"><label>Current password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
                    <div class="field"><label>New password</label><input type="password" name="password" required autocomplete="new-password"></div>
                    <div class="field"><label>Confirm new password</label><input type="password" name="password_confirmation" required autocomplete="new-password"></div>
                </div></div>
                <div class="settings-savebar"><div><b>Account security</b><span>Your active session remains signed in after changing the password.</span></div><button class="btn">Change password</button></div>
            </form>
        </section>
    </main>

    <aside class="settings-preview-rail">
        <div class="public-profile-preview">
            <div class="public-profile-preview__cover" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif></div>
            <div class="public-profile-preview__logo">@if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}">@else{{ strtoupper(substr($agency->name,0,1)) }}@endif</div>
            <span>Marketplace preview</span><h3>{{ $agency->name }}</h3><p>{{ $agency->tagline ?: 'Your company tagline will appear here.' }}</p>
            <div class="public-profile-preview__facts"><div><b>{{ number_format($agency->properties()->whereIn('visibility',['public','featured'])->count()) }}</b><small>Public listings</small></div><div><b>{{ $agency->is_verified?'Verified':'Active' }}</b><small>Profile status</small></div></div>
            <a href="{{ route('marketplace.agency',$agency) }}" target="_blank">Open public profile ↗</a>
        </div>
        <div class="settings-help-card"><span>PUBLIC DATA RULE</span><p>Company information can be public. Landlord, customer, transaction and accounting data remain private to your Rahnama workspace.</p></div>
    </aside>
</div>
@endsection
