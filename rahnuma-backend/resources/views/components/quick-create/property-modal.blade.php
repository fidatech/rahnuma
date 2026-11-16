{{-- New property without leaving a deal: only what is needed to continue. The rest is added in the property form. --}}
@props(['provinces' => collect()])
<div class="modal fade" id="quick-property-modal" tabindex="-1" aria-hidden="true" aria-labelledby="quick-property-title">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" data-quick-form data-resource="property" action="{{ route('tenant.quick.property') }}" method="post">@csrf
            <div class="modal-header">
                <div class="set-head"><span class="set-icon"><x-icon name="building" /></span><div><h5 class="modal-title" id="quick-property-title">{{ __('New property') }}</h5><small>{{ __('Capture only the information needed to continue the deal. Private until you choose to publish it.') }}</small></div></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" data-form-errors hidden></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">{{ __('Listing title') }} <span class="req">*</span></label><input class="form-control" name="title" required placeholder="{{ __('Example: 3-bedroom apartment in Karte 4') }}"></div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center"><label class="form-label" for="quick-property-owner">{{ __('Primary owner / landlord') }}</label><button class="btn btn-sm btn-link p-0 mb-2 text-decoration-none" type="button" data-modal-open="quick-owner-modal" data-quick-target="quick-property-owner"><x-icon name="plus" class="small" /> {{ __('New owner') }}</button></div>
                        <select class="form-select" name="owner_id" id="quick-property-owner" data-smart-resource="owner" data-remote-url="{{ route('tenant.lookup','owners') }}" data-placeholder="{{ __('Search existing owner') }}"><option value=""></option></select>
                    </div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Property type') }} <span class="req">*</span></label><select class="form-select" name="property_type" required>@foreach(config('rahnuma.property_types') as $type)<option value="{{ $type }}">{{ enum_label($type) }}</option>@endforeach</select></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Offer') }} <span class="req">*</span></label><select class="form-select" name="transaction_type" required data-property-offer-type><option value="sale">{{ __('For sale') }}</option><option value="rent">{{ __('For rent') }}</option><option value="bay_wafa">{{ __('Mortgage / Bay-e-Wafa') }}</option></select></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Province') }}</label><select class="form-select" name="province_id" data-placeholder="{{ __('Select province') }}"><option value="">{{ __('Select province') }}</option>@foreach($provinces as $province)<option value="{{ $province->id }}">{{ loc_name($province) }}</option>@endforeach</select></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Currency') }}</label><select class="form-select" name="currency">@foreach(config('rahnuma.currencies') as $currency)<option value="{{ $currency }}">{{ $currency }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">{{ __('Address / neighborhood') }}</label><input class="form-control" name="address" placeholder="{{ __('District, neighborhood, street or landmark') }}"></div>
                    <div class="col-12 col-md-6" data-property-money="sale"><label class="form-label">{{ __('Sale / mortgage amount') }}</label><input class="form-control" name="price" type="number" step="any" min="0" placeholder="0" dir="ltr"></div>
                    <div class="col-12 col-md-6" data-property-money="rent"><label class="form-label">{{ __('Monthly rent') }}</label><input class="form-control" name="monthly_rent" type="number" step="any" min="0" placeholder="0" dir="ltr"></div>
                    <div class="col-12 col-md-6" data-property-money="rent"><label class="form-label">{{ __('Deposit') }}</label><input class="form-control" name="deposit_amount" type="number" step="any" min="0" placeholder="0" dir="ltr"></div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="small text-muted me-auto">{{ __('Finish photos, amenities and publishing from the property form.') }}</span>
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button class="btn btn-primary" type="submit"><span data-submit-label><x-icon name="check" class="me-1" /> {{ __('Create & use property') }}</span><span class="mini-spinner spinner-border spinner-border-sm ms-1" hidden></span></button>
            </div>
        </form>
    </div>
</div>
