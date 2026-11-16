@php
    $user = auth()->user();
    $agency = $user->agency;
    $is = fn (string ...$patterns) => request()->routeIs(...$patterns);
    $in = fn (string $resource) => request()->is('workspace/'.$resource.'*');

    // the menu of each panel: [heading] or [label, icon, url, current]
    $menus = [
        'workspace' => [
            ['heading' => 'Overview'],
            ['Dashboard', 'gauge-high', route('tenant.dashboard'), $is('tenant.dashboard')],
            ['heading' => 'Property & CRM'],
            ['Properties', 'building', route('tenant.properties.index'), $is('tenant.properties.*')],
            ['Landlords & Owners', 'user-tie', route('tenant.resource.index', 'owners'), $in('owners')],
            ['Customers', 'users', route('tenant.resource.index', 'customers'), $in('customers')],
            ['Requirements & Matching', 'bullseye', route('tenant.requirements.index'), $is('tenant.requirements.*')],
            ['Leads', 'filter', route('tenant.resource.index', 'leads'), $in('leads')],
            ['Visits & Follow-ups', 'calendar-check', route('tenant.resource.index', 'visits'), $in('visits')],
            ['heading' => 'Deals & Finance'],
            ['Sales / Rent / Mortgage', 'handshake', route('tenant.transactions.index'), $is('tenant.transactions.*')],
            ['Payments', 'money-bill-wave', route('tenant.resource.index', 'payments'), $in('payments')],
            ['Expenses', 'receipt', route('tenant.resource.index', 'expenses'), $in('expenses')],
            ['Accounting & Reports', 'chart-column', route('tenant.accounting'), $is('tenant.accounting')],
            ['General Ledger', 'book', route('tenant.ledger'), $is('tenant.ledger')],
            ['heading' => 'Network & Administration'],
            ['Cross-Rahnama Cooperation', 'people-arrows', route('tenant.cooperation.index'), $is('tenant.cooperation.*')],
            ['Team & Roles', 'users-gear', route('tenant.staff.index'), $is('tenant.staff.*')],
            ['System & Company Settings', 'gear', route('tenant.settings.edit'), $is('tenant.settings.*')],
        ],
        'platform' => [
            ['heading' => 'Platform'],
            ['Dashboard', 'gauge-high', route('platform.dashboard'), $is('platform.dashboard')],
            ['Rahnama Tenants', 'building-user', route('platform.agencies'), $is('platform.agencies')],
            ['Marketplace Promotions', 'bullhorn', route('platform.marketplace'), $is('platform.marketplace')],
        ],
        'government' => [
            ['heading' => 'Regulatory'],
            ['Dashboard', 'gauge-high', route('government.dashboard'), $is('government.dashboard')],
            ['Agency Licenses', 'id-badge', route('government.licenses'), $is('government.licenses')],
            ['Setta Inventory', 'book', route('government.setta'), $is('government.setta')],
            ['Inspections', 'clipboard-check', route('government.inspections'), $is('government.inspections')],
            ['Violations', 'triangle-exclamation', route('government.violations'), $is('government.violations')],
        ],
    ];
    $brandName = $shell === 'workspace' ? ($agency?->name ?: config('app.name')) : config('app.name');
@endphp
<aside class="app-sidebar" id="appSidebar" aria-label="{{ __('Menu') }}">
    <div class="sidebar-brand">
        <a href="{{ $home }}" class="brand-link">
            @if ($shell === 'workspace' && $agency?->logo_path)
                <img src="{{ asset('storage/'.$agency->logo_path) }}" alt="" style="height:32px;width:32px;object-fit:cover;border-radius:9px">
            @else
                <x-brand-logo />
            @endif
            <span class="brand-text">{{ $brandName }}</span>
        </a>
    </div>
    <div class="sidebar-scroll" id="sidebarScroll">
        <div class="drawer-profile">
            <span class="avatar avatar-md avatar-soft-primary">@if ($user->avatar_path)<img src="{{ asset('storage/'.$user->avatar_path) }}" alt="">@else{{ initial($user->name) }}@endif</span>
            <div class="min-w-0"><strong>{{ $user->name }}</strong><small>{{ $user->job_title ?: enum_label($user->role) }}</small></div>
        </div>
        <div class="drawer-quick">
            <button type="button" data-theme-toggle><x-icon name="moon" /><span>{{ __('Theme') }}</span></button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#searchModal"><x-icon name="magnifying-glass" /><span>{{ __('Search') }}</span></button>
            <button type="submit" form="logoutForm"><x-icon name="right-from-bracket" /><span>{{ __('Sign out') }}</span></button>
        </div>
        <ul class="sidebar-menu" id="sidebarMenu">
            @foreach ($menus[$shell] as $item)
                @if (isset($item['heading']))
                    <li class="menu-heading"><span>{{ __($item['heading']) }}</span></li>
                @else
                    <li class="menu-item {{ $item[3] ? 'active' : '' }}">
                        <a class="menu-link" href="{{ $item[2] }}"><span class="menu-icon"><x-icon :name="$item[1]" /></span><span class="menu-text">{{ __($item[0]) }}</span></a>
                    </li>
                @endif
            @endforeach
            <li class="menu-heading"><span>{{ __('Marketplace') }}</span></li>
            <li class="menu-item">
                <a class="menu-link" href="{{ route('home') }}"><span class="menu-icon"><x-icon name="store" /></span><span class="menu-text">{{ __('Property marketplace') }}</span></a>
            </li>
        </ul>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
