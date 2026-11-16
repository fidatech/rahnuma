<div class="smart-modal" id="quick-customer-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="quick-customer-title">
    <div class="smart-modal__panel">
        <form data-quick-form data-resource="customer" action="{{ route('tenant.quick.customer') }}" method="post">
            @csrf
            <div class="smart-modal__header">
                <div>
                    <div class="eyebrow">Quick create</div>
                    <h2 id="quick-customer-title">New buyer / tenant</h2>
                    <p>Create the customer without leaving the deal.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">×</button>
            </div>
            <div class="smart-modal__body">
                <div class="smart-form-grid">
                    <div class="field span-2"><label>Full name <span class="req">*</span></label><input name="name" required autocomplete="off" placeholder="Buyer / tenant full name"></div>
                    <div class="field"><label>Phone <span class="req">*</span></label><input name="phone" required autocomplete="off" placeholder="07xx xxx xxx"></div>
                    <div class="field"><label>WhatsApp</label><input name="whatsapp" autocomplete="off" placeholder="Optional"></div>
                    <div class="field"><label>Tazkira / ID number</label><input name="tazkira_no" autocomplete="off" placeholder="Optional"></div>
                    <div class="field"><label>Source</label><select name="source"><option value="walk-in">Walk-in</option><option value="referral">Referral</option><option value="marketplace">Marketplace</option><option value="whatsapp">WhatsApp</option><option value="facebook">Facebook</option><option value="other">Other</option></select></div>
                    <div class="field span-2"><label>Address</label><input name="address" placeholder="Current address"></div>
                    <div class="field span-2"><label>Notes</label><textarea name="notes" rows="2" placeholder="Requirements or important notes"></textarea></div>
                </div>
                <div class="inline-validation" data-form-errors hidden></div>
            </div>
            <div class="smart-modal__footer">
                <button type="button" class="btn secondary" data-modal-close>Cancel</button>
                <button class="btn" type="submit"><span data-submit-label>Save & select customer</span><span class="mini-spinner" hidden></span></button>
            </div>
        </form>
    </div>
</div>
