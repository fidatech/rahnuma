<div class="smart-modal" id="quick-owner-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="quick-owner-title">
    <div class="smart-modal__panel">
        <form data-quick-form data-resource="owner" action="{{ route('tenant.quick.owner') }}" method="post">
            @csrf
            <div class="smart-modal__header">
                <div>
                    <div class="eyebrow">Quick create</div>
                    <h2 id="quick-owner-title">New owner / landlord</h2>
                    <p>Add the essential identity now. You can enrich the profile later.</p>
                </div>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">×</button>
            </div>
            <div class="smart-modal__body">
                <div class="smart-form-grid">
                    <div class="field span-2"><label>Full name <span class="req">*</span></label><input name="name" required autocomplete="off" placeholder="Owner / landlord full name"></div>
                    <div class="field"><label>Phone</label><input name="phone" autocomplete="off" placeholder="07xx xxx xxx"></div>
                    <div class="field"><label>WhatsApp</label><input name="whatsapp" autocomplete="off" placeholder="Optional"></div>
                    <div class="field"><label>Tazkira / ID number</label><input name="tazkira_no" autocomplete="off" placeholder="Optional"></div>
                    <div class="field"><label>Email</label><input type="email" name="email" autocomplete="off" placeholder="Optional"></div>
                    <div class="field span-2"><label>Address</label><input name="address" placeholder="Current address"></div>
                    <div class="field span-2"><label>Notes</label><textarea name="notes" rows="2" placeholder="Ownership notes, representative details, etc."></textarea></div>
                </div>
                <div class="inline-validation" data-form-errors hidden></div>
            </div>
            <div class="smart-modal__footer">
                <button type="button" class="btn secondary" data-modal-close>Cancel</button>
                <button class="btn" type="submit"><span data-submit-label>Save & select owner</span><span class="mini-spinner" hidden></span></button>
            </div>
        </form>
    </div>
</div>
