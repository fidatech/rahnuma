@extends('layouts.workspace')
@section('workspace')
<div class="deal-page-head">
    <div>
        <div class="deal-page-head__back"><a href="{{ route('tenant.transactions.index') }}">← Transactions</a></div>
        <div class="deal-title-row">
            <h1>New deal</h1>
            <span class="draft-chip">Draft</span>
        </div>
        <p>Capture the property, parties and commercial terms. Rahnuma keeps the ownership and accounting links in sync.</p>
    </div>
    <div class="deal-head-actions">
        <a class="btn secondary" href="{{ route('tenant.transactions.index') }}">Cancel</a>
        <button class="btn" type="submit" form="transaction-form">Save transaction</button>
    </div>
</div>

<div class="deal-shell-v4">
    <form method="post" id="transaction-form" class="deal-canvas">@csrf
        <section class="deal-section-v4">
            <div class="deal-section-v4__head">
                <div>
                    <span class="deal-section-index">01</span>
                    <h2>What is the deal?</h2>
                    <p>Choose the transaction type first. The rest of the form adapts automatically.</p>
                </div>
            </div>
            <div class="deal-type-switch" role="radiogroup" aria-label="Transaction type">
                <label class="deal-type-option">
                    <input type="radio" name="transaction_type_ui" value="sale" data-type-radio>
                    <span class="deal-type-option__icon">S</span>
                    <span><b>Sale</b><small>Property ownership transfer</small></span>
                </label>
                <label class="deal-type-option">
                    <input type="radio" name="transaction_type_ui" value="rent" data-type-radio>
                    <span class="deal-type-option__icon">R</span>
                    <span><b>Rent</b><small>Lease / tenancy agreement</small></span>
                </label>
                <label class="deal-type-option">
                    <input type="radio" name="transaction_type_ui" value="bay_wafa" data-type-radio>
                    <span class="deal-type-option__icon">M</span>
                    <span><b>Mortgage</b><small>بیع وفا / بیع جایزی</small></span>
                </label>
            </div>
            <select name="transaction_type" id="transaction_type" class="sr-only-select" aria-hidden="true" tabindex="-1">
                <option value="sale" @selected(old('transaction_type')==='sale')>Sale</option>
                <option value="rent" @selected(old('transaction_type')==='rent')>Rent</option>
                <option value="bay_wafa" @selected(old('transaction_type')==='bay_wafa')>Mortgage / بیع وفا</option>
            </select>
        </section>

        <section class="deal-section-v4">
            <div class="deal-section-v4__head">
                <div>
                    <span class="deal-section-index">02</span>
                    <h2>Property</h2>
                    <p>Select an existing property. If it is new, register it here without leaving the deal.</p>
                </div>
                <button class="text-action" type="button" data-modal-open="quick-property-modal" data-quick-target="property_id">
                    <span>＋</span> New property
                </button>
            </div>

            <div class="entity-picker entity-picker--property">
                <div class="entity-picker__visual">P</div>
                <div class="entity-picker__main">
                    <label for="property_id">Property <span class="req">*</span></label>
                    <select name="property_id" id="property_id" data-smart-resource="property" data-remote-url="{{ route('tenant.lookup','properties') }}" data-placeholder="Search by listing code, title or address" required>
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
                    <div class="entity-picker__meta" id="property-picker-meta">No property selected yet.</div>
                </div>
            </div>
        </section>

        <section class="deal-section-v4">
            <div class="deal-section-v4__head">
                <div>
                    <span class="deal-section-index">03</span>
                    <h2>Parties</h2>
                    <p>The property's primary owner is selected automatically when available.</p>
                </div>
            </div>
            <div class="party-grid-v4">
                <div class="party-card-v4">
                    <div class="party-card-v4__top">
                        <div class="party-avatar">A</div>
                        <div>
                            <span class="party-kicker" id="party-a-kicker">Owner side</span>
                            <h3 id="party-a-title">Seller / owner</h3>
                        </div>
                        <button class="icon-action" type="button" data-modal-open="quick-owner-modal" data-quick-target="seller_owner_id" title="Create owner">＋</button>
                    </div>
                    <label for="seller_owner_id" class="sr-label">Seller / landlord</label>
                    <select name="seller_owner_id" id="seller_owner_id" data-smart-resource="owner" data-remote-url="{{ route('tenant.lookup','owners') }}" data-placeholder="Search owner / landlord">
                        <option value=""></option>
                        @if($selectedOwner)<option value="{{ $selectedOwner->id }}" selected>{{ $selectedOwner->name }}{{ $selectedOwner->phone ? ' · '.$selectedOwner->phone : '' }}</option>@endif
                    </select>
                    <div class="owner-autofill" id="owner-auto-hint" hidden>Linked from the selected property</div>
                </div>

                <div class="party-card-v4">
                    <div class="party-card-v4__top">
                        <div class="party-avatar party-avatar--secondary">B</div>
                        <div>
                            <span class="party-kicker" id="party-b-kicker">Customer side</span>
                            <h3 id="party-b-title">Buyer</h3>
                        </div>
                        <button class="icon-action" type="button" data-modal-open="quick-customer-modal" data-quick-target="buyer_customer_id" title="Create customer">＋</button>
                    </div>
                    <label for="buyer_customer_id" class="sr-label">Buyer / tenant</label>
                    <select name="buyer_customer_id" id="buyer_customer_id" data-smart-resource="customer" data-remote-url="{{ route('tenant.lookup','customers') }}" data-placeholder="Search buyer / tenant">
                        <option value=""></option>
                        @if($selectedCustomer)<option value="{{ $selectedCustomer->id }}" selected>{{ $selectedCustomer->name }}{{ $selectedCustomer->phone ? ' · '.$selectedCustomer->phone : '' }}</option>@endif
                    </select>
                </div>
            </div>
        </section>

        <section class="deal-section-v4">
            <div class="deal-section-v4__head">
                <div>
                    <span class="deal-section-index">04</span>
                    <h2>Commercial terms</h2>
                    <p>Use the negotiated figures. Suggested values are pulled from the property and remain editable.</p>
                </div>
            </div>
            <div class="terms-grid-v4">
                <div class="field" data-deal-field="capital">
                    <label id="amount-label">Transaction value <span class="req">*</span></label>
                    <div class="money-field"><span id="amount-currency-prefix">AFN</span><input type="number" step="any" min="0" name="amount" id="amount" value="{{ old('amount') }}" placeholder="0"></div>
                </div>
                <div class="field" data-deal-field="rent">
                    <label>Monthly rent <span class="req">*</span></label>
                    <div class="money-field"><span id="rent-currency-prefix">AFN</span><input type="number" step="any" min="0" name="monthly_rent" id="monthly_rent" value="{{ old('monthly_rent') }}" placeholder="0"></div>
                </div>
                <div class="field" data-deal-field="rent">
                    <label>Deposit / advance</label>
                    <div class="money-field"><span id="deposit-currency-prefix">AFN</span><input type="number" step="any" min="0" name="deposit_amount" id="deposit_amount" value="{{ old('deposit_amount') }}" placeholder="0"></div>
                </div>
                <div class="field">
                    <label>Currency</label>
                    <select name="currency" id="currency">@foreach(config('rahnuma.currencies') as $x)<option value="{{ $x }}" @selected(old('currency','AFN')===$x)>{{ $x }}</option>@endforeach</select>
                </div>
                <div class="field" data-deal-field="rent"><label>Lease starts</label><input type="date" name="start_date" value="{{ old('start_date') }}"></div>
                <div class="field" data-deal-field="rent"><label>Lease ends</label><input type="date" name="end_date" value="{{ old('end_date') }}"></div>
                <div class="field"><label>Exchange rate to AFN</label><input type="number" step="any" min="0" name="exchange_rate_to_afn" value="{{ old('exchange_rate_to_afn',1) }}"></div>
                <div class="field"><label>Setta number</label><input name="setta_number" value="{{ old('setta_number') }}" placeholder="Optional in private mode"></div>
            </div>
        </section>

        <section class="deal-section-v4 deal-section-v4--last">
            <div class="deal-section-v4__head">
                <div>
                    <span class="deal-section-index">05</span>
                    <h2>Notes</h2>
                    <p>Only record operational details that matter to this deal.</p>
                </div>
            </div>
            <textarea name="notes" rows="4" placeholder="Special conditions, payment terms, witness notes or internal remarks">{{ old('notes') }}</textarea>
        </section>

        <div class="deal-mobile-actions">
            <a class="btn secondary" href="{{ route('tenant.transactions.index') }}">Cancel</a>
            <button class="btn" type="submit">Save transaction</button>
        </div>
    </form>

    <aside class="deal-rail-v4">
        <div class="deal-rail-card deal-rail-card--property">
            <div class="rail-label">Deal preview</div>
            <div class="rail-property-code" id="context-code">No property</div>
            <h3 id="context-name">Select a property</h3>
            <p id="context-address">The selected listing and ownership details will appear here.</p>
            <div class="rail-facts">
                <div><span>Owner</span><b id="context-owner">—</b></div>
                <div><span>Offer</span><b id="context-offer">—</b></div>
            </div>
        </div>

        <div class="deal-rail-card">
            <div class="rail-card-head">
                <div>
                    <div class="rail-label">Commission</div>
                    <h3><span id="commission-total">0.00</span> <small id="commission-currency">AFN</small></h3>
                </div>
                <span class="auto-chip">Auto</span>
            </div>
            <p class="rail-note" id="commission-rule">Enter the deal value to calculate commission.</p>
            <div class="commission-lines-v4">
                <div><span id="commission-a-label">Seller / owner</span><b id="commission-a">0.00</b></div>
                <div><span id="commission-b-label">Buyer</span><b id="commission-b">0.00</b></div>
            </div>
        </div>

        <div class="deal-rail-card deal-rail-card--quiet">
            <div class="rail-label">Data integrity</div>
            <ul class="integrity-list">
                <li><span>✓</span> Property ownership remains linked</li>
                <li><span>✓</span> Existing people are reused when matched</li>
                <li><span>✓</span> Financial fields adapt to the deal</li>
            </ul>
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
    const typeRadios = [...document.querySelectorAll('[data-type-radio]')];
    const owner = document.getElementById('seller_owner_id');
    const amount = document.getElementById('amount');
    const rent = document.getElementById('monthly_rent');
    const deposit = document.getElementById('deposit_amount');
    const currency = document.getElementById('currency');
    const saleRate = {{ (float) config('rahnuma.sale_baywafa_rate') }};
    const rentMultiplier = {{ (float) config('rahnuma.rent_multiplier') }};

    const money = value => Number(value || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    const txName = value => value === 'rent' ? 'Rent' : (value === 'bay_wafa' ? 'Mortgage / بیع وفا' : 'Sale');

    const setType = (value) => {
        type.value = value;
        typeRadios.forEach(r => r.checked = r.value === value);
        refreshType();
    };

    const refreshType = () => {
        const isRent = type.value === 'rent';
        document.getElementById('party-a-title').textContent = isRent ? 'Landlord' : 'Seller / owner';
        document.getElementById('party-b-title').textContent = isRent ? 'Tenant' : 'Buyer';
        document.getElementById('party-a-kicker').textContent = isRent ? 'Property owner' : 'Seller side';
        document.getElementById('party-b-kicker').textContent = isRent ? 'Tenant side' : 'Buyer side';
        document.getElementById('commission-a-label').textContent = isRent ? 'Landlord' : 'Seller / owner';
        document.getElementById('commission-b-label').textContent = isRent ? 'Tenant' : 'Buyer';
        document.getElementById('amount-label').innerHTML = `${type.value === 'bay_wafa' ? 'Mortgage amount' : 'Transaction value'} <span class="req">*</span>`;
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
            ? `${rentMultiplier * 100}% of one month's rent, divided between landlord and tenant.`
            : `${saleRate * 100}% of transaction value, divided between both parties.`;
    };

    const applyProperty = (forceCommercialTerms = true) => {
        const option = property.options[property.selectedIndex];
        const hasProperty = !!property.value;
        const code = hasProperty ? (option.dataset.code || 'Property') : 'No property';
        const title = hasProperty ? (option.dataset.title || option.textContent) : 'Select a property';
        const address = hasProperty ? (option.dataset.address || 'Address not completed') : 'The selected listing and ownership details will appear here.';
        const ownerName = hasProperty ? (option.dataset.ownerName || 'Not assigned') : '—';
        const offer = hasProperty ? txName(option.dataset.transactionType) : '—';

        document.getElementById('context-code').textContent = code;
        document.getElementById('context-name').textContent = title;
        document.getElementById('context-address').textContent = address;
        document.getElementById('context-owner').textContent = ownerName;
        document.getElementById('context-offer').textContent = offer;
        document.getElementById('property-picker-meta').textContent = hasProperty ? `${code} · ${address}` : 'No property selected yet.';

        if (!hasProperty) return;
        if (option.dataset.ownerId) {
            let ownerOption = [...owner.options].find(o => o.value === option.dataset.ownerId);
            if (!ownerOption) {
                ownerOption = new Option(option.dataset.ownerName || 'Property owner', option.dataset.ownerId, true, true);
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
            if (option.dataset.currency) currency.value = option.dataset.currency;
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
