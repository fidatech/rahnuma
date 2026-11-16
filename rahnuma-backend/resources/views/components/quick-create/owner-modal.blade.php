{{-- New owner or landlord without leaving the page. --}}
<div class="modal fade" id="quick-owner-modal" tabindex="-1" aria-hidden="true" aria-labelledby="quick-owner-title">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" data-quick-form data-resource="owner" action="{{ route('tenant.quick.owner') }}" method="post">@csrf
            <div class="modal-header">
                <div class="set-head"><span class="set-icon"><x-icon name="user-tie" /></span><div><h5 class="modal-title" id="quick-owner-title">{{ __('New owner') }}</h5><small>{{ __('Owner / landlord. Add the primary identity now; documents and details can be added later.') }}</small></div></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" data-form-errors hidden></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">{{ __('Full name') }} <span class="req">*</span></label><input class="form-control" name="name" required autocomplete="off" placeholder="{{ __('Full legal name') }}"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Phone') }}</label><input class="form-control" name="phone" autocomplete="off" placeholder="07xx xxx xxx" inputmode="tel" dir="ltr"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('WhatsApp') }}</label><input class="form-control" name="whatsapp" autocomplete="off" placeholder="{{ __('Optional') }}" inputmode="tel" dir="ltr"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Tazkira / ID') }}</label><input class="form-control" name="tazkira_no" autocomplete="off" placeholder="{{ __('Optional identity reference') }}" dir="ltr"></div>
                    <div class="col-12 col-md-6"><label class="form-label">{{ __('Email') }}</label><input class="form-control" type="email" name="email" autocomplete="off" placeholder="{{ __('Optional') }}" dir="ltr"></div>
                    <div class="col-12"><label class="form-label">{{ __('Address') }}</label><input class="form-control" name="address" placeholder="{{ __('Current address') }}"></div>
                    <div class="col-12"><label class="form-label">{{ __('Internal note') }}</label><textarea class="form-control" name="notes" rows="3" placeholder="{{ __('Representative, co-owner, authorization or contact note') }}"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="small text-muted me-auto">{{ __('Private ownership data is never published by default.') }}</span>
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button class="btn btn-primary" type="submit"><span data-submit-label><x-icon name="check" class="me-1" /> {{ __('Save & use owner') }}</span><span class="mini-spinner spinner-border spinner-border-sm ms-1" hidden></span></button>
            </div>
        </form>
    </div>
</div>
