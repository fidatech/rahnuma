@props(['owners' => collect(), 'provinces' => collect()])
<div class="smart-modal smart-modal--wide" id="quick-property-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="quick-property-title">
    <div class="smart-modal__panel">
        <form data-quick-form data-resource="property" action="{{ route('tenant.quick.property') }}" method="post">
            @csrf
            <div class="smart-modal__header">
                <div>
                    <div class="eyebrow">Quick property</div>
                    <h2 id="quick-property-title">Register property inside this deal</h2>
                    <p>Capture the essentials now; detailed specs, photos and documents can be completed from Property Inventory later.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">×</button>
            </div>
            <div class="smart-modal__body">
                <div class="smart-form-grid">
                    <div class="field span-2"><label>Listing title <span class="req">*</span></label><input name="title" required placeholder="e.g. 3-bedroom apartment in Karte 4"></div>
                    <div class="field"><label>Primary owner / landlord</label>
                        <div class="smart-input-row">
                            <select name="owner_id" id="quick-property-owner" data-smart-resource="owner"><option value="">Select owner</option>@foreach($owners as $owner)<option value="{{ $owner->id }}">{{ $owner->name }}{{ $owner->phone ? ' · '.$owner->phone : '' }}</option>@endforeach</select>
                            <button class="smart-add" type="button" data-modal-open="quick-owner-modal" data-quick-target="quick-property-owner" title="Create owner">+</button>
                        </div>
                    </div>
                    <div class="field"><label>Property type <span class="req">*</span></label><select name="property_type" required>@foreach(config('rahnuma.property_types') as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></div>
                    <div class="field"><label>Offer type <span class="req">*</span></label><select name="transaction_type" required data-property-offer-type><option value="sale">For sale</option><option value="rent">For rent</option><option value="bay_wafa">Mortgage / بیع وفا</option></select></div>
                    <div class="field"><label>Province</label><select name="province_id"><option value="">Select province</option>@foreach($provinces as $province)<option value="{{ $province->id }}">{{ $province->name_en }} / {{ $province->name_fa }}</option>@endforeach</select></div>
                    <div class="field span-2"><label>Address</label><input name="address" placeholder="District, neighborhood, street, landmark"></div>
                    <div class="field" data-property-money="sale"><label>Sale / mortgage amount</label><input name="price" type="number" step="any" min="0"></div>
                    <div class="field" data-property-money="rent"><label>Monthly rent</label><input name="monthly_rent" type="number" step="any" min="0"></div>
                    <div class="field" data-property-money="rent"><label>Deposit</label><input name="deposit_amount" type="number" step="any" min="0"></div>
                    <div class="field"><label>Currency</label><select name="currency">@foreach(config('rahnuma.currencies') as $currency)<option value="{{ $currency }}">{{ $currency }}</option>@endforeach</select></div>
                </div>
                <div class="inline-validation" data-form-errors hidden></div>
            </div>
            <div class="smart-modal__footer">
                <div class="smart-modal__hint">Private by default · Full details can be completed later</div>
                <button type="button" class="btn secondary" data-modal-close>Cancel</button>
                <button class="btn" type="submit"><span data-submit-label>Create & use property</span><span class="mini-spinner" hidden></span></button>
            </div>
        </form>
    </div>
</div>
