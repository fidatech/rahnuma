<div class="smart-modal smart-sheet" id="quick-owner-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="quick-owner-title">
    <div class="smart-modal__panel smart-sheet__panel">
        <form data-quick-form data-resource="owner" action="{{ route('tenant.quick.owner') }}" method="post">@csrf
            <div class="smart-sheet__accent" aria-hidden="true"></div>
            <div class="smart-sheet__header">
                <div class="smart-sheet__heading">
                    <div class="smart-sheet__icon smart-sheet__icon--warm" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5.5 19c.7-3.4 3-5.2 6.5-5.2s5.8 1.8 6.5 5.2" stroke-linecap="round"/></svg></div>
                    <div><div class="sheet-kicker">Quick create · Ownership</div><h2 id="quick-owner-title">Owner / landlord</h2><p>Add the primary identity now. Property ownership, documents and additional details can be enriched later.</p></div>
                </div>
                <button type="button" class="sheet-close" data-modal-close aria-label="Close"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg></button>
            </div>
            <div class="smart-sheet__body">
                <section class="sheet-form-card">
                    <div class="sheet-form-card__head"><div><span>Primary identity</span><strong>Who owns or represents the property?</strong></div><small>Reusable across every property and deal.</small></div>
                    <div class="smart-form-grid">
                        <div class="field span-2"><label>Full name <span class="req">*</span></label><input name="name" required autocomplete="off" placeholder="Full legal name"></div>
                        <div class="field"><label>Phone</label><input name="phone" autocomplete="off" placeholder="07xx xxx xxx" inputmode="tel"></div>
                        <div class="field"><label>WhatsApp</label><input name="whatsapp" autocomplete="off" placeholder="Optional" inputmode="tel"></div>
                        <div class="field"><label>Tazkira / ID</label><input name="tazkira_no" autocomplete="off" placeholder="Optional identity reference"></div>
                        <div class="field"><label>Email</label><input type="email" name="email" autocomplete="off" placeholder="Optional"></div>
                    </div>
                </section>
                <section class="sheet-form-card sheet-form-card--quiet">
                    <div class="sheet-form-card__head"><div><span>Context</span><strong>Contact & representation notes</strong></div><small>Keep private office context here.</small></div>
                    <div class="smart-form-grid"><div class="field span-2"><label>Address</label><input name="address" placeholder="Current address"></div><div class="field span-2"><label>Internal note</label><textarea name="notes" rows="3" placeholder="Representative, co-owner, authorization or contact note"></textarea></div></div>
                </section>
                <div class="inline-validation" data-form-errors hidden></div>
            </div>
            <div class="smart-sheet__footer"><div class="sheet-footer-note">Private ownership data is never published by default.</div><button type="button" class="btn secondary" data-modal-close>Cancel</button><button class="btn sheet-primary" type="submit"><span data-submit-label>Save & use owner</span><span class="mini-spinner" hidden></span></button></div>
        </form>
    </div>
</div>
