@extends('layouts.workspace')
@section('title', __('Settings'))
@section('workspace')
@php
    $languages = ['fa' => 'Dari', 'ps' => 'Pashto', 'en' => 'English', 'uz' => 'Uzbek'];
@endphp
<x-page-header :title="__('Settings')" icon="gear" :subtitle="__('Manage your Rahnama identity, marketplace presence, defaults and personal profile from one place.')">
    <a class="btn btn-outline-soft" href="{{ route('marketplace.agency',$agency) }}" target="_blank" rel="noopener"><x-icon name="arrow-up-right-from-square" class="me-1" /> {{ __('View public profile') }}</a>
</x-page-header>

<div class="seg mb-3" data-settings-nav>
    <a href="#company" class="active" data-native>{{ __('Company profile') }}</a>
    <a href="#marketplace" data-native>{{ __('Marketplace & system') }}</a>
    <a href="#profile" data-native>{{ __('My profile') }}</a>
    <a href="#security" data-native>{{ __('Security') }}</a>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <section id="company" class="rh-anchor">
            <form method="post" enctype="multipart/form-data" action="{{ route('tenant.settings.company') }}">@csrf
                <div class="card overflow-hidden">
                    <div class="profile-cover brand-cover-preview" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif>
                        <label class="btn btn-sm btn-light-secondary brand-cover-action"><x-icon name="camera" class="me-1" /> {{ __('Change cover') }}<input type="file" name="cover" accept="image/*" hidden></label>
                    </div>
                    <div class="profile-head">
                        <label class="avatar avatar-xl rounded-3 avatar-soft-primary brand-logo-uploader cursor-pointer" title="{{ __('Change logo') }}">
                            @if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}" alt="{{ $agency->name }}">@else<span>{{ initial($agency->name) }}</span>@endif
                            <input type="file" name="logo" accept="image/*" hidden>
                        </label>
                        <div class="ph-meta"><h3>{{ $agency->name }}</h3><span class="text-muted">{{ $agency->tagline ?: __('Add a short tagline for your public profile') }}</span></div>
                        <span class="badge-soft {{ $agency->profile_completed_at ? 'success' : 'warning' }} mb-2"><span class="dot"></span>{{ $agency->profile_completed_at ? __('Profile configured') : __('Needs setup') }}</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="building" /></span><div><h5>{{ __('Business details') }}</h5><small>{{ __('Primary information customers use to recognize your office.') }}</small></div></div></div>
                    <div class="card-body"><div class="row g-3">
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Rahnama / company name') }} <span class="req">*</span></label><input class="form-control" name="name" required value="{{ old('name',$agency->name) }}"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Public tagline') }}</label><input class="form-control" name="tagline" value="{{ old('tagline',$agency->tagline) }}" placeholder="{{ __('Trusted property guidance in Kabul') }}"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('License number') }}</label><input class="form-control" name="license_number" value="{{ old('license_number',$agency->license_number) }}" dir="ltr"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Province') }}</label><select class="form-select" name="province_id" data-placeholder="{{ __('Select province') }}"><option value=""></option>@foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('province_id',$agency->province_id)==$p->id)>{{ loc_name($p) }}</option>@endforeach</select></div>
                        <div class="col-12"><label class="form-label">{{ __('Company introduction') }}</label><textarea class="form-control" name="description" rows="5" placeholder="{{ __('Tell property seekers about your office, specialties and service areas.') }}">{{ old('description',$agency->description) }}</textarea></div>
                    </div></div>
                </div>

                <div class="card">
                    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="phone" /></span><div><h5>{{ __('Contact & office') }}</h5><small>{{ __('Customers can use these details from your public profile.') }}</small></div></div></div>
                    <div class="card-body"><div class="row g-3">
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Primary phone') }}</label><input class="form-control" name="phone" value="{{ old('phone',$agency->phone) }}" inputmode="tel" dir="ltr"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Secondary phone') }}</label><input class="form-control" name="secondary_phone" value="{{ old('secondary_phone',$agency->secondary_phone) }}" inputmode="tel" dir="ltr"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('WhatsApp') }}</label><input class="form-control" name="whatsapp" value="{{ old('whatsapp',$agency->whatsapp) }}" inputmode="tel" dir="ltr"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Public email') }}</label><input class="form-control" type="email" name="email" value="{{ old('email',$agency->email) }}" dir="ltr"></div>
                        <div class="col-12"><label class="form-label">{{ __('Office address') }}</label><input class="form-control" name="address" value="{{ old('address',$agency->address) }}"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Office hours') }}</label><input class="form-control" name="office_hours" value="{{ old('office_hours',$agency->office_hours) }}" placeholder="{{ __('Sat–Thu, 8:00–17:00') }}"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Website') }}</label><input class="form-control" type="url" name="website" value="{{ old('website',$agency->website) }}" placeholder="https://" dir="ltr"></div>
                    </div></div>
                </div>

                <div class="card">
                    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="globe" /></span><div><h5>{{ __('Languages & social presence') }}</h5><small>{{ __('Help customers know how they can communicate with your team.') }}</small></div></div></div>
                    <div class="card-body"><div class="row g-3">
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Languages spoken') }}</label><select class="form-select" name="languages[]" multiple data-placeholder="{{ __('Select languages') }}">@foreach($languages as $k=>$v)<option value="{{ $k }}" @selected(in_array($k,old('languages',$agency->languages?:['fa','ps'])))>{{ __($v) }}</option>@endforeach</select></div>
                        <div class="col-12 col-md-6"><label class="form-label">Facebook</label><input class="form-control" type="url" name="facebook_url" value="{{ old('facebook_url',$agency->facebook_url) }}" placeholder="https://facebook.com/..." dir="ltr"></div>
                        <div class="col-12 col-md-6"><label class="form-label">Instagram</label><input class="form-control" type="url" name="instagram_url" value="{{ old('instagram_url',$agency->instagram_url) }}" placeholder="https://instagram.com/..." dir="ltr"></div>
                        <div class="col-12 col-md-6"><label class="form-label">Telegram</label><input class="form-control" type="url" name="telegram_url" value="{{ old('telegram_url',$agency->telegram_url) }}" placeholder="https://t.me/..." dir="ltr"></div>
                    </div></div>
                    <div class="card-footer d-flex justify-content-between align-items-center gap-2 flex-wrap"><span class="small text-muted">{{ __('Changes are reflected on the marketplace immediately.') }}</span><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save company profile') }}</button></div>
                </div>
            </form>
        </section>

        <section id="marketplace" class="rh-anchor">
            <form method="post" action="{{ route('tenant.settings.marketplace') }}">@csrf
                <div class="card">
                    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="store" /></span><div><h5>{{ __('Public profile privacy') }}</h5><small>{{ __('These switches never expose landlord/customer private data.') }}</small></div></div></div>
                    <div class="card-body"><div class="row g-2">
                        @foreach([
                            'marketplace_enabled'=>['Publish Rahnama profile','Allow this company and its public listings to appear in the marketplace.'],
                            'marketplace_show_phone'=>['Show phone number','Display the company phone on public pages.'],
                            'marketplace_show_whatsapp'=>['Show WhatsApp','Display a direct WhatsApp contact action.'],
                            'marketplace_show_address'=>['Show office address','Display the office address on the Rahnama profile.'],
                            'marketplace_show_license'=>['Show license number','Display the license reference provided by your office.'],
                            'marketplace_show_exact_location'=>['Allow precise office location','Allow future map experiences to use the exact office coordinates.'],
                        ] as $key=>$copy)
                        <div class="col-12 col-md-6"><label class="switch-row form-switch h-100"><input class="form-check-input" type="checkbox" role="switch" name="{{ $key }}" value="1" @checked(old($key,$agency->$key))><div><strong>{{ __($copy[0]) }}</strong><span>{{ __($copy[1]) }}</span></div></label></div>
                        @endforeach
                    </div></div>
                </div>
                <div class="card">
                    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="sliders" /></span><div><h5>{{ __('Workspace defaults') }}</h5><small>{{ __('Used as sensible defaults when your team creates new records.') }}</small></div></div></div>
                    <div class="card-body"><div class="row g-3">
                        <div class="col-12 col-md-4"><label class="form-label">{{ __('Default currency') }}</label><select class="form-select" name="default_currency">@foreach(config('rahnuma.currencies') as $c)<option value="{{ $c }}" @selected(old('default_currency',$agency->default_currency?:'AFN')===$c)>{{ $c }}</option>@endforeach</select></div>
                        <div class="col-12 col-md-4"><label class="form-label">{{ __('Default language') }}</label><select class="form-select" name="default_locale">@foreach($languages as $k=>$v)<option value="{{ $k }}" @selected(old('default_locale',$agency->default_locale?:'fa')===$k)>{{ __($v) }}</option>@endforeach</select></div>
                        <div class="col-12 col-md-4"><label class="form-label">{{ __('Timezone') }}</label><select class="form-select" name="timezone"><option value="Asia/Kabul" @selected(($agency->timezone?:'Asia/Kabul')==='Asia/Kabul')>Asia/Kabul ({{ __('Afghanistan') }})</option><option value="Asia/Dubai" @selected($agency->timezone==='Asia/Dubai')>Asia/Dubai</option></select></div>
                    </div></div>
                    <div class="card-footer d-flex justify-content-between align-items-center gap-2 flex-wrap"><span class="small text-muted">{{ __('Keep these settings aligned with how your office works.') }}</span><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save preferences') }}</button></div>
                </div>
            </form>
        </section>

        <section id="profile" class="rh-anchor">
            <form method="post" enctype="multipart/form-data" action="{{ route('tenant.settings.profile') }}">@csrf
                <div class="card">
                    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="user" /></span><div><h5>{{ __('Personal profile') }}</h5><small>{{ __('Used inside Rahnuma for ownership, assignments and audit trails.') }}</small></div></div></div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-4">
                            <label class="avatar avatar-xl avatar-soft-primary profile-avatar-uploader cursor-pointer" title="{{ __('Change photo') }}">@if($user->avatar_path)<img src="{{ asset('storage/'.$user->avatar_path) }}" alt="">@else<span>{{ initial($user->name) }}</span>@endif<input type="file" name="avatar" accept="image/*" hidden></label>
                            <div class="row g-3 flex-fill">
                                <div class="col-12 col-md-6"><label class="form-label">{{ __('Full name') }} <span class="req">*</span></label><input class="form-control" name="name" required value="{{ old('name',$user->name) }}"></div>
                                <div class="col-12 col-md-6"><label class="form-label">{{ __('Job title') }}</label><input class="form-control" name="job_title" value="{{ old('job_title',$user->job_title) }}" placeholder="{{ __('Agency owner') }}"></div>
                                <div class="col-12 col-md-6"><label class="form-label">{{ __('Email') }} <span class="req">*</span></label><input class="form-control" type="email" name="email" required value="{{ old('email',$user->email) }}" dir="ltr"></div>
                                <div class="col-12 col-md-6"><label class="form-label">{{ __('Phone') }}</label><input class="form-control" name="phone" value="{{ old('phone',$user->phone) }}" inputmode="tel" dir="ltr"></div>
                                <div class="col-12"><label class="form-label">{{ __('My interface language') }}</label><select class="form-select" name="locale">@foreach($languages as $k=>$v)<option value="{{ $k }}" @selected(old('locale',$user->locale)===$k)>{{ __($v) }}</option>@endforeach</select></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center gap-2 flex-wrap"><span class="small text-muted">{{ __('Used for internal attribution and communication.') }}</span><button class="btn btn-primary"><x-icon name="check" class="me-1" /> {{ __('Save my profile') }}</button></div>
                </div>
            </form>
        </section>

        <section id="security" class="rh-anchor">
            <form method="post" action="{{ route('tenant.settings.password') }}">@csrf
                <div class="card">
                    <div class="card-header"><div class="set-head"><span class="set-icon"><x-icon name="key" /></span><div><h5>{{ __('Change password') }}</h5><small>{{ __('Choose a strong password unique to your Rahnuma account.') }}</small></div></div></div>
                    <div class="card-body"><div class="row g-3">
                        <div class="col-12"><label class="form-label">{{ __('Current password') }} <span class="req">*</span></label><input class="form-control" type="password" name="current_password" required autocomplete="current-password"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('New password') }} <span class="req">*</span></label><input class="form-control" type="password" name="password" required autocomplete="new-password"></div>
                        <div class="col-12 col-md-6"><label class="form-label">{{ __('Confirm new password') }} <span class="req">*</span></label><input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password"></div>
                    </div></div>
                    <div class="card-footer d-flex justify-content-between align-items-center gap-2 flex-wrap"><span class="small text-muted">{{ __('Your active session remains signed in after changing the password.') }}</span><button class="btn btn-primary"><x-icon name="key" class="me-1" /> {{ __('Change password') }}</button></div>
                </div>
            </form>
        </section>
    </div>

    <aside class="col-12 col-xl-4">
        <div class="rh-sticky">
            <div class="card overflow-hidden">
                <div class="profile-cover rh-cover-sm" @if($agency->cover_path) style="background-image:url('{{ asset('storage/'.$agency->cover_path) }}')" @endif></div>
                <div class="card-body">
                    <span class="avatar avatar-lg rounded-3 avatar-soft-primary rh-cover-logo">@if($agency->logo_path)<img src="{{ asset('storage/'.$agency->logo_path) }}" alt="">@else{{ initial($agency->name) }}@endif</span>
                    <div class="small text-muted text-uppercase fw-semibold mt-2">{{ __('Marketplace preview') }}</div>
                    <h5 class="mb-1">{{ $agency->name }}</h5>
                    <p class="small text-muted">{{ $agency->tagline ?: __('Your company tagline will appear here.') }}</p>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><div class="metric-box"><span>{{ __('Public listings') }}</span><strong>{{ number_format($agency->properties()->whereIn('visibility',['public','featured'])->count()) }}</strong></div></div>
                        <div class="col-6"><div class="metric-box"><span>{{ __('Profile status') }}</span><strong>{{ $agency->is_verified ? __('Verified') : __('Active') }}</strong></div></div>
                    </div>
                    <a class="btn btn-outline-soft w-100" href="{{ route('marketplace.agency',$agency) }}" target="_blank" rel="noopener"><x-icon name="arrow-up-right-from-square" class="me-1" /> {{ __('Open public profile') }}</a>
                </div>
            </div>
            <div class="alert alert-primary"><strong class="d-block mb-1">{{ __('Public data rule') }}</strong>{{ __('Company information can be public. Landlord, customer, transaction and accounting data remain private to your Rahnama workspace.') }}</div>
        </div>
    </aside>
</div>
@endsection
