@extends('layouts.workspace')
@section('workspace')
<div class="page-header">
    <div class="page-header__copy">
        <div class="eyebrow">Smart deal composer</div>
        <h1>New Transaction</h1>
        <p class="page-subtitle">Build a sale, rental or Mortgage / بیع وفا transaction without leaving this screen.</p>
    </div>
    <div class="page-actions"><a class="btn secondary" href="{{ route('tenant.transactions.index') }}">Back to register</a></div>
</div>

<div class="smart-stepbar">
    <div class="smart-step"><div class="smart-step__num">1</div><div><b>Property & parties</b><span>Select existing or create instantly</span></div></div>
    <div class="smart-step"><div class="smart-step__num">2</div><div><b>Commercial terms</b><span>Smart fields follow deal type</span></div></div>
    <div class="smart-step"><div class="smart-step__num">3</div><div><b>Complete & record</b><span>Commission + audit trail generated</span></div></div>
</div>

<div class="deal-grid">
    <form method="post" class="card" id="transaction-form">@csrf
        <div class="form-section" style="padding-top:0">
            <div class="form-section__title">Property & deal identity</div>
            <div class="grid">
                <div class="g8 field">
                    <label>Property <span class="req">*</span></label>
                    <div class="smart-input-row">
                        <select name="property_id" id="property_id" data-smart-resource="property" required>
                            <option value="">Choose a property</option>
                            @foreach($properties as $p)
                                <option value="{{ $p->id }}"
                                    @selected(old('property_id')==$p->id)
                                    data-code="{{ $p->code }}"
                                    data-title="{{ $p->title }}"
                                    data-owner-id="{{ $p->owner_id }}"
                                    data-owner-name="{{ $p->owner?->name }}"
                                    data-transaction-type="{{ $p->transaction_type }}"
                                    data-price="{{ $p->price }}"
                                    data-monthly-rent="{{ $p->monthly_rent }}"
                                    data-deposit-amount="{{ $p->deposit_amount }}"
                                    data-currency="{{ $p->currency }}"
                                    data-address="{{ $p->address }}">{{ $p->code }} — {{ $p->title }}</option>
                            @endforeach
                        </select>
                        <button class="smart-add" type="button" data-modal-open="quick-property-modal" data-quick-target="property_id" title="Register a new property">+</button>
                    </div>
                    <div class="smart-field-meta"><span class="smart-chip"><span class="smart-badge-dot"></span> New property can be registered without leaving this transaction</span></div>
                </div>
                <div class="g4 field">
                    <label>Transaction type <span class="req">*</span></label>
                    <select name="transaction_type" id="transaction_type" required>
                        <option value="sale" @selected(old('transaction_type')==='sale')>Sale</option>
                        <option value="rent" @selected(old('transaction_type')==='rent')>Rent</option>
                        <option value="bay_wafa" @selected(old('transaction_type')==='bay_wafa')>Mortgage / بیع وفا</option>
                    </select>
                </div>
                <div class="g4 field">
                    <label>Setta number</label>
                    <input name="setta_number" value="{{ old('setta_number') }}" placeholder="Optional in private mode">
                    <div class="help">Tracked/official serial validation can be enforced when government mode is enabled.</div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section__title">Parties</div>
            <div class="grid">
                <div class="g6 field">
                    <label id="party-a-label">Seller / Landlord <span class="req">*</span></label>
                    <div class="smart-input-row">
                        <select name="seller_owner_id" id="seller_owner_id" data-smart-resource="owner">
                            <option value="">Select owner / landlord</option>
                            @foreach($owners as $o)<option value="{{ $o->id }}" @selected(old('seller_owner_id')==$o->id)>{{ $o->name }}{{ $o->phone ? ' · '.$o->phone : '' }}</option>@endforeach
                        </select>
                        <button class="smart-add" type="button" data-modal-open="quick-owner-modal" data-quick-target="seller_owner_id" title="Create owner / landlord">+</button>
                    </div>
                    <div class="smart-owner-hint" id="owner-auto-hint" hidden>The selected property's primary owner has been selected automatically.</div>
                </div>
                <div class="g6 field">
                    <label id="party-b-label">Buyer / Tenant <span class="req">*</span></label>
                    <div class="smart-input-row">
                        <select name="buyer_customer_id" id="buyer_customer_id" data-smart-resource="customer">
                            <option value="">Select buyer / tenant</option>
                            @foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('buyer_customer_id')==$c->id)>{{ $c->name }}{{ $c->phone ? ' · '.$c->phone : '' }}</option>@endforeach
                        </select>
                        <button class="smart-add" type="button" data-modal-open="quick-customer-modal" data-quick-target="buyer_customer_id" title="Create buyer / tenant">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section__title">Financial terms</div>
            <div class="grid">
                <div class="g4 field" data-deal-field="capital">
                    <label id="amount-label">Sale / mortgage amount <span class="req">*</span></label>
                    <input type="number" step="any" min="0" name="amount" id="amount" value="{{ old('amount') }}" placeholder="0.00">
                </div>
                <div class="g4 field" data-deal-field="rent">
                    <label>Monthly rent <span class="req">*</span></label>
                    <input type="number" step="any" min="0" name="monthly_rent" id="monthly_rent" value="{{ old('monthly_rent') }}" placeholder="0.00">
                </div>
                <div class="g4 field" data-deal-field="rent">
                    <label>Deposit / advance</label>
                    <input type="number" step="any" min="0" name="deposit_amount" id="deposit_amount" value="{{ old('deposit_amount') }}" placeholder="0.00">
                </div>
                <div class="g4 field">
                    <label>Currency <span class="req">*</span></label>
                    <select name="currency" id="currency">@foreach(config('rahnuma.currencies') as $x)<option value="{{ $x }}" @selected(old('currency','AFN')===$x)>{{ $x }}</option>@endforeach</select>
                </div>
                <div class="g4 field" data-deal-field="rent"><label>Start date</label><input type="date" name="start_date" value="{{ old('start_date') }}"></div>
                <div class="g4 field" data-deal-field="rent"><label>End date</label><input type="date" name="end_date" value="{{ old('end_date') }}"></div>
                <div class="g4 field"><label>Exchange rate to AFN</label><input type="number" step="any" min="0" name="exchange_rate_to_afn" value="{{ old('exchange_rate_to_afn',1) }}"></div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section__title">Internal notes</div>
            <textarea name="notes" placeholder="Special conditions, payment notes, witnesses or operational remarks">{{ old('notes') }}</textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:9px;padding-top:18px"><a class="btn secondary" href="{{ route('tenant.transactions.index') }}">Cancel</a><button class="btn" type="submit">Complete transaction</button></div>
    </form>

    <aside class="deal-sidebar">
        <div class="smart-context" id="property-context">
            <div class="smart-context__title">Selected property</div>
            <div class="smart-context__name" id="context-name">No property selected</div>
            <div class="smart-context__sub" id="context-address">Choose a property or create one from this transaction.</div>
            <div class="smart-context__facts">
                <span class="smart-chip" id="context-owner">Owner: —</span>
                <span class="smart-chip" id="context-offer">Offer: —</span>
            </div>
        </div>
        <div style="height:12px"></div>
        <div class="commission-preview">
            <div class="commission-preview__eyebrow">Live commission preview</div>
            <div class="commission-preview__amount"><span id="commission-total">0.00</span> <small id="commission-currency" style="font-size:12px;color:#aab6c7">AFN</small></div>
            <div class="commission-preview__sub" id="commission-rule">Select deal terms to calculate commission.</div>
            <div class="commission-split">
                <div><b id="commission-a">0.00</b><span id="commission-a-label">Seller / Landlord</span></div>
                <div><b id="commission-b">0.00</b><span id="commission-b-label">Buyer / Tenant</span></div>
            </div>
        </div>
        <div class="card" style="margin-top:12px;padding:15px">
            <div class="smart-context__title">Smart safeguards</div>
            <div class="activity-list">
                <div class="activity-row" style="grid-template-columns:30px 1fr"><div class="activity-icon" style="width:28px;height:28px">01</div><div><div class="activity-title">Owner follows property</div><div class="activity-sub">Primary owner is auto-selected when available.</div></div></div>
                <div class="activity-row" style="grid-template-columns:30px 1fr"><div class="activity-icon" style="width:28px;height:28px">02</div><div><div class="activity-title">Duplicate-aware quick create</div><div class="activity-sub">Existing owner/customer is reused when phone or Tazkira matches.</div></div></div>
                <div class="activity-row" style="grid-template-columns:30px 1fr"><div class="activity-icon" style="width:28px;height:28px">03</div><div><div class="activity-title">Relevant fields only</div><div class="activity-sub">Rent and capital fields adapt to the selected transaction type.</div></div></div>
            </div>
        </div>
    </aside>
</div>
@endsection

@push('modals')
<x-quick-create.owner-modal />
<x-quick-create.customer-modal />
<x-quick-create.property-modal :owners="$owners" :provinces="$provinces" />
@endpush

@push('scripts')
<script>
(() => {
    const property = document.getElementById('property_id');
    const type = document.getElementById('transaction_type');
    const owner = document.getElementById('seller_owner_id');
    const amount = document.getElementById('amount');
    const rent = document.getElementById('monthly_rent');
    const deposit = document.getElementById('deposit_amount');
    const currency = document.getElementById('currency');
    const saleRate = {{ (float) config('rahnuma.sale_baywafa_rate') }};
    const rentMultiplier = {{ (float) config('rahnuma.rent_multiplier') }};

    const money = value => Number(value || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    const txName = value => value === 'rent' ? 'Rent' : (value === 'bay_wafa' ? 'Mortgage / بیع وفا' : 'Sale');

    const refreshLabels = () => {
        const isRent = type.value === 'rent';
        document.getElementById('party-a-label').innerHTML = `${isRent ? 'Landlord' : 'Seller / Owner'} <span class="req">*</span>`;
        document.getElementById('party-b-label').innerHTML = `${isRent ? 'Tenant' : 'Buyer'} <span class="req">*</span>`;
        document.getElementById('commission-a-label').textContent = isRent ? 'Landlord' : 'Seller / Owner';
        document.getElementById('commission-b-label').textContent = isRent ? 'Tenant' : 'Buyer';
        document.querySelectorAll('[data-deal-field="rent"]').forEach(el => el.classList.toggle('conditional-hidden', !isRent));
        document.querySelectorAll('[data-deal-field="capital"]').forEach(el => el.classList.toggle('conditional-hidden', isRent));
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
            ? `${rentMultiplier * 100}% of one month's rent, split between both parties.`
            : `${saleRate * 100}% of transaction value, split between both parties.`;
    };

    const applyProperty = (forceCommercialTerms = true) => {
        const option = property.options[property.selectedIndex];
        const hasProperty = !!property.value;
        document.getElementById('context-name').textContent = hasProperty ? (option.dataset.title || option.textContent) : 'No property selected';
        document.getElementById('context-address').textContent = hasProperty ? (option.dataset.address || 'Address not completed yet') : 'Choose a property or create one from this transaction.';
        document.getElementById('context-owner').textContent = `Owner: ${hasProperty ? (option.dataset.ownerName || 'Not assigned') : '—'}`;
        document.getElementById('context-offer').textContent = `Offer: ${hasProperty ? txName(option.dataset.transactionType) : '—'}`;
        if (!hasProperty) return;

        if (option.dataset.ownerId && [...owner.options].some(o => o.value === option.dataset.ownerId)) {
            owner.value = option.dataset.ownerId;
            const hint = document.getElementById('owner-auto-hint');
            hint.hidden = false;
            clearTimeout(window.__ownerHintTimer);
            window.__ownerHintTimer = setTimeout(() => hint.hidden = true, 3500);
        }
        if (forceCommercialTerms) {
            if (option.dataset.transactionType) type.value = option.dataset.transactionType;
            if (option.dataset.currency) currency.value = option.dataset.currency;
            if (option.dataset.price) amount.value = option.dataset.price;
            if (option.dataset.monthlyRent) rent.value = option.dataset.monthlyRent;
            if (option.dataset.depositAmount) deposit.value = option.dataset.depositAmount;
        }
        refreshLabels();
    };

    property.addEventListener('change', () => applyProperty(true));
    type.addEventListener('change', refreshLabels);
    [amount,rent,currency].forEach(el => el.addEventListener('input', refreshCommission));
    [amount,rent,currency].forEach(el => el.addEventListener('change', refreshCommission));
    document.addEventListener('rahnuma:quick-created', event => {
        if (event.detail.resource === 'property') setTimeout(() => applyProperty(true), 0);
    });

    refreshLabels();
    if (property.value) applyProperty(false);
})();
</script>
@endpush
