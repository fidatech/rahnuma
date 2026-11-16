@props(['provinces' => collect()])
<div class="smart-modal smart-sheet smart-sheet--wide" id="quick-property-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="quick-property-title">
    <div class="smart-modal__panel smart-sheet__panel smart-sheet__panel--wide">
        <form data-quick-form data-resource="property" action="{{ route('tenant.quick.property') }}" method="post">@csrf
            <div class="smart-sheet__accent" aria-hidden="true"></div>
            <div class="smart-sheet__header">
                <div class="smart-sheet__heading">
                    <div class="smart-sheet__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 11.5 12 5l8 6.5V20H4v-8.5Z" stroke-linejoin="round"/><path d="M9.5 20v-5h5v5"/></svg></div>
                    <div><div class="sheet-kicker">Quick create · Property</div><h2 id="quick-property-title">Register a deal-ready property</h2><p>Capture only the information needed to continue the deal. Complete media, documents and specifications later in Property Studio.</p></div>
                </div>
                <button type="button" class="sheet-close" data-modal-close aria-label="Close"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg></button>
            </div>
            <div class="smart-sheet__body">
                <section class="sheet-form-card">
                    <div class="sheet-form-card__head"><div><span>Listing identity</span><strong>Property & ownership</strong></div><small>Private until you choose to publish it.</small></div>
                    <div class="smart-form-grid">
                        <div class="field span-2"><label>Listing title <span class="req">*</span></label><input name="title" required placeholder="Example: 3-bedroom apartment in Karte 4"></div>
                        <div class="field span-2"><label>Primary owner / landlord</label><div class="inline-create-field"><select name="owner_id" id="quick-property-owner" data-smart-resource="owner" data-remote-url="{{ route('tenant.lookup','owners') }}" data-placeholder="Search existing owner"><option value=""></option></select><button class="inline-create-link" type="button" data-modal-open="quick-owner-modal" data-quick-target="quick-property-owner"><span>＋</span> New owner</button></div></div>
                        <div class="field"><label>Property type <span class="req">*</span></label><select name="property_type" required>@foreach(config('rahnuma.property_types') as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></div>
                        <div class="field"><label>Offer <span class="req">*</span></label><select name="transaction_type" required data-property-offer-type><option value="sale">For sale</option><option value="rent">For rent</option><option value="bay_wafa">Mortgage / بیع وفا</option></select></div>
                    </div>
                </section>
                <section class="sheet-form-card sheet-form-card--quiet">
                    <div class="sheet-form-card__head"><div><span>Commercial & location</span><strong>Where is it and what is the asking term?</strong></div><small>The final transaction can use a negotiated amount.</small></div>
                    <div class="smart-form-grid">
                        <div class="field"><label>Province</label><select name="province_id"><option value="">Select province</option>@foreach($provinces as $province)<option value="{{ $province->id }}">{{ $province->name_en }} / {{ $province->name_fa }}</option>@endforeach</select></div>
                        <div class="field"><label>Currency</label><select name="currency">@foreach(config('rahnuma.currencies') as $currency)<option value="{{ $currency }}">{{ $currency }}</option>@endforeach</select></div>
                        <div class="field span-2"><label>Address / neighborhood</label><input name="address" placeholder="District, neighborhood, street or landmark"></div>
                        <div class="field" data-property-money="sale"><label>Sale / mortgage amount</label><input name="price" type="number" step="any" min="0" placeholder="0"></div>
                        <div class="field" data-property-money="rent"><label>Monthly rent</label><input name="monthly_rent" type="number" step="any" min="0" placeholder="0"></div>
                        <div class="field" data-property-money="rent"><label>Deposit</label><input name="deposit_amount" type="number" step="any" min="0" placeholder="0"></div>
                    </div>
                </section>
                <div class="inline-validation" data-form-errors hidden></div>
            </div>
            <div class="smart-sheet__footer"><div class="sheet-footer-note">Finish photos, amenities, documents and publishing from Property Studio.</div><button type="button" class="btn secondary" data-modal-close>Cancel</button><button class="btn sheet-primary" type="submit"><span data-submit-label>Create & use property</span><span class="mini-spinner" hidden></span></button></div>
        </form>
    </div>
</div>
