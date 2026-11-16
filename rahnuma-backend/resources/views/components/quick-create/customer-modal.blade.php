<div class="smart-modal smart-sheet" id="quick-customer-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="quick-customer-title">
    <div class="smart-modal__panel smart-sheet__panel">
        <form data-quick-form data-resource="customer" action="{{ route('tenant.quick.customer') }}" method="post">@csrf
            <div class="smart-sheet__accent" aria-hidden="true"></div>
            <div class="smart-sheet__header">
                <div class="smart-sheet__heading">
                    <div class="smart-sheet__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5.5 19c.7-3.4 3-5.2 6.5-5.2s5.8 1.8 6.5 5.2" stroke-linecap="round"/></svg></div>
                    <div><div class="sheet-kicker">Quick create · Customer</div><h2 id="quick-customer-title">Buyer / tenant</h2><p>Create the relationship without leaving the deal. Rahnuma checks existing identity details before adding another record.</p></div>
                </div>
                <button type="button" class="sheet-close" data-modal-close aria-label="Close"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg></button>
            </div>
            <div class="smart-sheet__body">
                <section class="sheet-form-card">
                    <div class="sheet-form-card__head"><div><span>Identity & contact</span><strong>Who is this customer?</strong></div><small>Required fields are intentionally minimal.</small></div>
                    <div class="smart-form-grid">
                        <div class="field span-2"><label>Full name <span class="req">*</span></label><input name="name" required autocomplete="off" placeholder="Enter customer's full name"></div>
                        <div class="field"><label>Phone <span class="req">*</span></label><input name="phone" required autocomplete="off" placeholder="07xx xxx xxx" inputmode="tel"></div>
                        <div class="field"><label>WhatsApp</label><input name="whatsapp" autocomplete="off" placeholder="Same as phone or another number" inputmode="tel"></div>
                        <div class="field"><label>Tazkira / ID</label><input name="tazkira_no" autocomplete="off" placeholder="Optional identity reference"></div>
                        <div class="field"><label>Lead source</label><select name="source"><option value="walk-in">Walk-in</option><option value="referral">Referral</option><option value="marketplace">Rahnuma marketplace</option><option value="whatsapp">WhatsApp</option><option value="facebook">Facebook</option><option value="other">Other</option></select></div>
                    </div>
                </section>
                <section class="sheet-form-card sheet-form-card--quiet">
                    <div class="sheet-form-card__head"><div><span>Context</span><strong>Useful details for the team</strong></div><small>Can be completed later.</small></div>
                    <div class="smart-form-grid">
                        <div class="field span-2"><label>Current address</label><input name="address" placeholder="Area, district or current address"></div>
                        <div class="field span-2"><label>Internal note</label><textarea name="notes" rows="3" placeholder="Requirement, timing, family need or relationship context"></textarea></div>
                    </div>
                </section>
                <div class="inline-validation" data-form-errors hidden></div>
            </div>
            <div class="smart-sheet__footer"><div class="sheet-footer-note">Saved only inside this Rahnama workspace.</div><button type="button" class="btn secondary" data-modal-close>Cancel</button><button class="btn sheet-primary" type="submit"><span data-submit-label>Save & use customer</span><span class="mini-spinner" hidden></span></button></div>
        </form>
    </div>
</div>
