@extends('layouts.workspace')
@section('title', __('New deal'))
@section('workspace')
@php
    $section = fn (int $n, string $title, string $text) => '<div class="set-head"><span class="set-icon">'.sprintf('%02d', $n).'</span><div><h5>'.e(__($title)).'</h5><small>'.e(__($text)).'</small></div></div>';
    $types = [
        'sale' => ['key', 'Sale', 'Property ownership transfer'],
        'rent' => ['house-user', 'Rent', 'Lease / tenancy agreement'],
        'bay_wafa' => ['file-contract', 'Mortgage', 'Bay-e-Wafa / Bay-e-Jayezi'],
    ];
@endphp
<x-page-header :title="__('New deal')" icon="handshake" :crumbs="[[__('Transactions'), route('tenant.transactions.index')], [__('Draft')]]" :subtitle="__('Capture the property, parties and commercial terms. Rahnuma keeps the ownership and accounting links in sync.')">
    <a class="btn btn-light-secondary" href="{{ route('tenant.transactions.index') }}">{{ __('Cancel') }}</a>
    <button class="btn btn-primary" type="submit" form="transaction-form"><x-icon name="check" class="me-1" /> {{ __('Save transaction') }}</button>
</x-page-header>

<div class="row g-3">
    <div class="col-12 col-xl-8">
    <form method="post" id="transaction-form" action="{{ route('tenant.transactions.store') }}">@csrf
        <div class="card">
            <div class="card-header">{!! $section(1, 'What is the deal?', 'Choose the transaction type first. The rest of the form adapts automatically.') !!}</div>
            <div class="card-body">
                <div class="row g-2" role="radiogroup" aria-label="{{ __('Transaction type') }}">
                    @foreach ($types as $value => [$icon, $label, $help])
                        <div class="col-12 col-md-4">
                            <label class="form-check-card h-100">
                                <input class="form-check-input" type="radio" name="transaction_type_ui" value="{{ $value }}" data-type-radio>
                                <span class="d-flex align-items-center gap-2"><span class="sc-icon icon-soft primary rh-choice-icon"><x-icon :name="$icon" /></span><span><strong class="d-block text-heading">{{ __($label) }}</strong><small class="text-muted">{{ __($help) }}</small></span></span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <select name="transaction_type" id="transaction_type" class="sr-only-select" aria-hidden="true" tabindex="-1">
                    <option value="sale" @selected(old('transaction_type')==='sale')>sale</option>
                    <option value="rent" @selected(old('transaction_type')==='rent')>rent</option>
                    <option value="bay_wafa" @selected(old('transaction_type')==='bay_wafa')>bay_wafa</option>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-header">{!! $section(2, 'Property', 'Select an existing property. If it is new, register it here without leaving the deal.') !!}<button class="btn btn-sm btn-soft-primary" type="button" data-modal-open="quick-property-modal" data-quick-target="property_id"><x-icon name="plus" class="me-1" /> {{ __('New property') }}</button></div>
            <div class="card-body">
                <label class="form-label" for="property_id">{{ __('Property') }} <span class="req">*</span></label>
                <select class="form-select" name="property_id" id="property_id" data-smart-resource="property" data-remote-url="{{ route('tenant.lookup','properties') }}" data-placeholder="{{ __('Search by listing code, title or address') }}" required>
                    <option value=""></option>
                    @if($selectedProperty)
                        <option value="{{ $selectedProperty->id }}" selected
                            data-code="{{ $selectedProperty->code }}"
                            data-title="{{ $selectedProperty->title }}"
                            data-owner-id="{{ $selectedProperty->owner_id }}"
                            data-owner-name="{{ $selectedProperty->owner?->name }}"
                            data-transaction-type="{{ $selectedProperty->transaction_type }}"
                            data-price="{{ $selectedProperty->price }}"
                            data-monthly-rent="{{ $selectedProperty->monthly_rent }}"
                            data-deposit-amount="{{ $selectedProperty->deposit_amount }}"
                            data-currency="{{ $selectedProperty->currency }}"
                            data-address="{{ $selectedProperty->address }}">{{ $selectedProperty->code }} — {{ $selectedProperty->title }}</option>
                    @endif
                </select>
                <div class="form-text" id="property-picker-meta">{{ __('No property selected yet.') }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">{!! $section(3, 'Parties', 'The primary owner of the property is selected automatically when available.') !!}</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="avatar avatar-md avatar-soft-primary">A</span>
                                <div class="flex-fill min-w-0"><span class="small text-muted d-block" id="party-a-kicker">{{ __('Owner side') }}</span><h6 class="mb-0" id="party-a-title">{{ __('Seller / owner') }}</h6></div>
                                <button class="btn btn-sm btn-soft-primary" type="button" data-modal-open="quick-owner-modal" data-quick-target="seller_owner_id" title="{{ __('New owner') }}" aria-label="{{ __('New owner') }}"><x-icon name="plus" /></button>
                            </div>
                            <label for="seller_owner_id" class="visually-hidden">{{ __('Seller / landlord') }}</label>
                            <select class="form-select" name="seller_owner_id" id="seller_owner_id" data-smart-resource="owner" data-remote-url="{{ route('tenant.lookup','owners') }}" data-placeholder="{{ __('Search owner / landlord') }}">
                                <option value=""></option>
                                @if($selectedOwner)<option value="{{ $selectedOwner->id }}" selected>{{ $selectedOwner->name }}{{ $selectedOwner->phone ? ' · '.$selectedOwner->phone : '' }}</option>@endif
                            </select>
                            <div class="form-text text-success" id="owner-auto-hint" hidden><x-icon name="link" class="me-1" />{{ __('Linked from the selected property') }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="avatar avatar-md avatar-soft-purple">B</span>
                                <div class="flex-fill min-w-0"><span class="small text-muted d-block" id="party-b-kicker">{{ __('Customer side') }}</span><h6 class="mb-0" id="party-b-title">{{ __('Buyer') }}</h6></div>
                                <button class="btn btn-sm btn-soft-primary" type="button" data-modal-open="quick-customer-modal" data-quick-target="buyer_customer_id" title="{{ __('New customer') }}" aria-label="{{ __('New customer') }}"><x-icon name="plus" /></button>
                            </div>
                            <label for="buyer_customer_id" class="visually-hidden">{{ __('Buyer / tenant') }}</label>
                            <select class="form-select" name="buyer_customer_id" id="buyer_customer_id" data-smart-resource="customer" data-remote-url="{{ route('tenant.lookup','customers') }}" data-placeholder="{{ __('Search buyer / tenant') }}">
                                <option value=""></option>
                                @if($selectedCustomer)<option value="{{ $selectedCustomer->id }}" selected>{{ $selectedCustomer->name }}{{ $selectedCustomer->phone ? ' · '.$selectedCustomer->phone : '' }}</option>@endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">{!! $section(4, 'Commercial terms', 'Use the negotiated figures. Suggested values are pulled from the property and remain editable.') !!}</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6" data-deal-field="capital">
                        <label class="form-label" for="amount"><span id="amount-label">{{ __('Transaction value') }}</span> <span class="req">*</span></label>
                        <div class="input-group" dir="ltr"><span class="input-group-text" id="amount-currency-prefix">AFN</span><input class="form-control" type="number" step="any" min="0" name="amount" id="amount" value="{{ old('amount') }}" placeholder="0"></div>
                    </div>
                    <div class="col-12 col-md-6" data-deal-field="rent">
                        <label class="form-label" for="monthly_rent">{{ __('Monthly rent') }} <span class="req">*</span></label>
                        <div class="input-group" dir="ltr"><span class="input-group-text" id="rent-currency-prefix">AFN</span><input class="form-control" type="number" step="any" min="0" name="monthly_rent" id="monthly_rent" value="{{ old('monthly_rent') }}" placeholder="0"></div>
                    </div>
                    <div class="col-12 col-md-6" data-deal-field="rent">
                        <label class="form-label" for="deposit_amount">{{ __('Deposit / advance') }}</label>
                        <div class="input-group" dir="ltr"><span class="input-group-text" id="deposit-currency-prefix">AFN</span><input class="form-control" type="number" step="any" min="0" name="deposit_amount" id="deposit_amount" value="{{ old('deposit_amount') }}" placeholder="0"></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="currency">{{ __('Currency') }}</label>
                        <select class="form-select" name="currency" id="currency">@foreach(config('rahnuma.currencies') as $x)<option value="{{ $x }}" @selected(old('currency','AFN')===$x)>{{ $x }}</option>@endforeach</select>
                    </div>
                    <div class="col-12 col-md-6" data-deal-field="rent"><label class="form-label">{{ __('Lease starts') }}</label><input class="form-control" type="date" name="start_date" value="{{ old('start_date') }}"></div>
                    <div class="col-12 col-md-6" data-deal-field="rent"><label class="form-label">{{ __('Lease ends') }}</label><input class="form-control" type="date" name="end_date" value="{{ old('end_date') }}"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Exchange rate to AFN') }}</label><input class="form-control" type="number" step="any" min="0" name="exchange_rate_to_afn" value="{{ old('exchange_rate_to_afn',1) }}" dir="ltr"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Setta number') }}</label><input class="form-control" name="setta_number" value="{{ old('setta_number') }}" placeholder="{{ __('Optional in private mode') }}" dir="ltr"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">{!! $section(5, 'Notes', 'Only record operational details that matter to this deal.') !!}</div>
            <div class="card-body">
                <textarea class="form-control" name="notes" rows="4" placeholder="{{ __('Special conditions, payment terms, witness notes or internal remarks') }}">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="save-bar">
            <a class="btn btn-light-secondary" href="{{ route('tenant.transactions.index') }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save transaction') }}</button>
        </div>
    </form>
    </div>

    <aside class="col-12 col-xl-4">
        <div class="rh-sticky">
            <div class="card">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold mb-2">{{ __('Deal preview') }}</div>
                    <span class="badge-code mb-2" id="context-code">{{ __('No property') }}</span>
                    <h5 id="context-name">{{ __('Select a property') }}</h5>
                    <p class="small text-muted" id="context-address">{{ __('The selected listing and ownership details will appear here.') }}</p>
                    <dl class="facts">
                        <div><dt>{{ __('Owner') }}</dt><dd id="context-owner">—</dd></div>
                        <div><dt>{{ __('Offer') }}</dt><dd id="context-offer">—</dd></div>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-muted text-uppercase fw-semibold mb-1">{{ __('Commission') }}</div>
                            <div class="rh-prop-price" dir="ltr"><span id="commission-total">0.00</span> <small id="commission-currency">AFN</small></div>
                        </div>
                        <span class="badge-soft success">{{ __('Auto') }}</span>
                    </div>
                    <p class="small text-muted" id="commission-rule">{{ __('Enter the deal value to calculate commission.') }}</p>
                    <dl class="facts">
                        <div><dt id="commission-a-label">{{ __('Seller / owner') }}</dt><dd id="commission-a" dir="ltr">0.00</dd></div>
                        <div><dt id="commission-b-label">{{ __('Buyer') }}</dt><dd id="commission-b" dir="ltr">0.00</dd></div>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold mb-2">{{ __('Data integrity') }}</div>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><x-icon name="circle-check" class="text-success me-1" /> {{ __('Property ownership remains linked') }}</li>
                        <li><x-icon name="circle-check" class="text-success me-1" /> {{ __('Existing people are reused when matched') }}</li>
                        <li><x-icon name="circle-check" class="text-success me-1" /> {{ __('Financial fields adapt to the deal') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>
@endsection

@push('modals')
<x-quick-create.owner-modal />
<x-quick-create.customer-modal />
<x-quick-create.property-modal :provinces="$provinces" />
@endpush

@push('scripts')
<script>
(() => {
    const property = document.getElementById('property_id');
    const type = document.getElementById('transaction_type');
    if (!property || !type) return;
    const text = {{ Illuminate\Support\Js::from([
        'sale' => __('Sale'), 'rent' => __('Rent'), 'mortgage' => __('Mortgage / Bay-e-Wafa'),
        'landlord' => __('Landlord'), 'sellerOwner' => __('Seller / owner'), 'tenant' => __('Tenant'), 'buyer' => __('Buyer'),
        'propertyOwner' => __('Property owner'), 'sellerSide' => __('Seller side'), 'tenantSide' => __('Tenant side'), 'buyerSide' => __('Buyer side'),
        'mortgageAmount' => __('Mortgage amount'), 'transactionValue' => __('Transaction value'),
        'rentRule' => __(':percent% of the rent of one month, divided between landlord and tenant.'),
        'saleRule' => __(':percent% of transaction value, divided between both parties.'),
        'property' => __('Property'), 'noProperty' => __('No property'), 'selectProperty' => __('Select a property'),
        'addressMissing' => __('Address not completed'), 'previewHint' => __('The selected listing and ownership details will appear here.'),
        'notAssigned' => __('Not assigned'), 'noneYet' => __('No property selected yet.'), 'ownerOfProperty' => __('Property owner'),
    ]) }};
    const typeRadios = [...document.querySelectorAll('[data-type-radio]')];
    const owner = document.getElementById('seller_owner_id');
    const amount = document.getElementById('amount');
    const rent = document.getElementById('monthly_rent');
    const deposit = document.getElementById('deposit_amount');
    const currency = document.getElementById('currency');
    const saleRate = {{ (float) config('rahnuma.sale_baywafa_rate') }};
    const rentMultiplier = {{ (float) config('rahnuma.rent_multiplier') }};

    const money = value => Number(value || 0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    const percent = value => String(Math.round(value * 10000) / 100);
    const txName = value => value === 'rent' ? text.rent : (value === 'bay_wafa' ? text.mortgage : text.sale);
    const setCurrency = value => { currency.value = value; window.jQuery?.(currency).trigger('change.select2'); };

    const setType = (value) => {
        type.value = value;
        typeRadios.forEach(r => r.checked = r.value === value);
        refreshType();
    };

    const refreshType = () => {
        const isRent = type.value === 'rent';
        document.getElementById('party-a-title').textContent = isRent ? text.landlord : text.sellerOwner;
        document.getElementById('party-b-title').textContent = isRent ? text.tenant : text.buyer;
        document.getElementById('party-a-kicker').textContent = isRent ? text.propertyOwner : text.sellerSide;
        document.getElementById('party-b-kicker').textContent = isRent ? text.tenantSide : text.buyerSide;
        document.getElementById('commission-a-label').textContent = isRent ? text.landlord : text.sellerOwner;
        document.getElementById('commission-b-label').textContent = isRent ? text.tenant : text.buyer;
        document.getElementById('amount-label').textContent = type.value === 'bay_wafa' ? text.mortgageAmount : text.transactionValue;
        document.querySelectorAll('[data-deal-field="rent"]').forEach(el => el.classList.toggle('conditional-hidden', !isRent));
        document.querySelectorAll('[data-deal-field="capital"]').forEach(el => el.classList.toggle('conditional-hidden', isRent));
        refreshCommission();
    };

    const refreshCurrency = () => {
        ['amount-currency-prefix','rent-currency-prefix','deposit-currency-prefix'].forEach(id => {
            const el = document.getElementById(id); if (el) el.textContent = currency.value;
        });
        refreshCommission();
    };

    const refreshCommission = () => {
        const base = type.value === 'rent' ? Number(rent.value || 0) : Number(amount.value || 0);
        const total = type.value === 'rent' ? base * rentMultiplier : base * saleRate;
        document.getElementById('commission-total').textContent = money(total);
        document.getElementById('commission-a').textContent = money(total / 2);
        document.getElementById('commission-b').textContent = money(total / 2);
        document.getElementById('commission-currency').textContent = currency.value;
        document.getElementById('commission-rule').textContent = type.value === 'rent'
            ? text.rentRule.replace(':percent', percent(rentMultiplier))
            : text.saleRule.replace(':percent', percent(saleRate));
    };

    const applyProperty = (forceCommercialTerms = true) => {
        const option = property.options[property.selectedIndex];
        const hasProperty = !!property.value;
        const code = hasProperty ? (option.dataset.code || text.property) : text.noProperty;
        const title = hasProperty ? (option.dataset.title || option.textContent) : text.selectProperty;
        const address = hasProperty ? (option.dataset.address || text.addressMissing) : text.previewHint;
        const ownerName = hasProperty ? (option.dataset.ownerName || text.notAssigned) : '—';
        const offer = hasProperty ? txName(option.dataset.transactionType) : '—';

        document.getElementById('context-code').textContent = code;
        document.getElementById('context-name').textContent = title;
        document.getElementById('context-address').textContent = address;
        document.getElementById('context-owner').textContent = ownerName;
        document.getElementById('context-offer').textContent = offer;
        document.getElementById('property-picker-meta').textContent = hasProperty ? `${code} · ${address}` : text.noneYet;

        if (!hasProperty) return;
        if (option.dataset.ownerId) {
            let ownerOption = [...owner.options].find(o => o.value === option.dataset.ownerId);
            if (!ownerOption) {
                ownerOption = new Option(option.dataset.ownerName || text.ownerOfProperty, option.dataset.ownerId, true, true);
                owner.add(ownerOption);
            }
            owner.value = option.dataset.ownerId;
            window.jQuery?.(owner).val(option.dataset.ownerId).trigger('change.select2');
            const hint = document.getElementById('owner-auto-hint');
            hint.hidden = false;
            clearTimeout(window.__ownerHintTimer);
            window.__ownerHintTimer = setTimeout(() => hint.hidden = true, 3200);
        }
        if (forceCommercialTerms) {
            if (option.dataset.transactionType) setType(option.dataset.transactionType);
            if (option.dataset.currency) setCurrency(option.dataset.currency);
            if (option.dataset.price) amount.value = option.dataset.price;
            if (option.dataset.monthlyRent) rent.value = option.dataset.monthlyRent;
            if (option.dataset.depositAmount) deposit.value = option.dataset.depositAmount;
        }
        refreshCurrency();
        refreshType();
    };

    typeRadios.forEach(radio => radio.addEventListener('change', () => setType(radio.value)));
    property.addEventListener('change', () => applyProperty(true));
    [amount,rent].forEach(el => el.addEventListener('input', refreshCommission));
    currency.addEventListener('change', refreshCurrency);
    if (window.__rahnumaTransactionQuickCreated) document.removeEventListener('rahnuma:quick-created', window.__rahnumaTransactionQuickCreated);
    window.__rahnumaTransactionQuickCreated = event => {
        if (event.detail.resource === 'property') setTimeout(() => applyProperty(true), 0);
    };
    document.addEventListener('rahnuma:quick-created', window.__rahnumaTransactionQuickCreated);

    setType(type.value || 'sale');
    refreshCurrency();
    if (property.value) applyProperty(true);
})();
</script>
@endpush
