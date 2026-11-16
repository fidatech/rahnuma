{{-- New customer without leaving the page: saved in the background and chosen in the list that asked for it. --}}
<div class="modal fade" id="quick-customer-modal" tabindex="-1" aria-hidden="true" aria-labelledby="quick-customer-title">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" data-quick-form data-resource="customer" action="{{ route('tenant.quick.customer') }}" method="post">@csrf
            <div class="modal-header">
                <div class="set-head"><span class="set-icon"><x-icon name="user-plus" /></span><div><h5 class="modal-title" id="quick-customer-title">{{ __('New customer') }}</h5><small>{{ __('Buyer / tenant. Rahnuma checks existing identity details before adding another record.') }}</small></div></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" data-form-errors hidden></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">{{ __('Full name') }} <span class="req">*</span></label><input class="form-control" name="name" required autocomplete="off" placeholder="{{ __('Enter the full name of the customer') }}"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Phone') }} <span class="req">*</span></label><input class="form-control" name="phone" required autocomplete="off" placeholder="07xx xxx xxx" inputmode="tel" dir="ltr"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('WhatsApp') }}</label><input class="form-control" name="whatsapp" autocomplete="off" placeholder="{{ __('Same as phone or another number') }}" inputmode="tel" dir="ltr"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Tazkira / ID') }}</label><input class="form-control" name="tazkira_no" autocomplete="off" placeholder="{{ __('Optional identity reference') }}" dir="ltr"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Lead source') }}</label><select class="form-select" name="source">@foreach(['walk-in'=>'Walk-in','referral'=>'Referral','marketplace'=>'Rahnuma marketplace','whatsapp'=>'WhatsApp','facebook'=>'Facebook','other'=>'Other'] as $value=>$text)<option value="{{ $value }}">{{ __($text) }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">{{ __('Current address') }}</label><input class="form-control" name="address" placeholder="{{ __('Area, district or current address') }}"></div>
                    <div class="col-12"><label class="form-label">{{ __('Internal note') }}</label><textarea class="form-control" name="notes" rows="3" placeholder="{{ __('Requirement, timing, family need or relationship context') }}"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="small text-muted me-auto">{{ __('Saved only inside this Rahnama workspace.') }}</span>
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button class="btn btn-primary" type="submit"><span data-submit-label><x-icon name="check" class="me-1" /> {{ __('Save & use customer') }}</span><span class="mini-spinner spinner-border spinner-border-sm ms-1" hidden></span></button>
            </div>
        </form>
    </div>
</div>
