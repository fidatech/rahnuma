(() => {
  const qs = (s, c = document) => c.querySelector(s);
  const qsa = (s, c = document) => [...c.querySelectorAll(s)];
  const csrf = qs('meta[name="csrf-token"]')?.content || '';

  const toast = (message, type = 'success') => {
    let stack = qs('.smart-toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'smart-toast-stack';
      document.body.appendChild(stack);
    }
    const item = document.createElement('div');
    item.className = `smart-toast ${type}`;
    item.textContent = message;
    stack.appendChild(item);
    setTimeout(() => item.remove(), 3600);
  };

  const openModal = (id, target = null) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.dataset.quickTarget = target || '';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    setTimeout(() => qs('input:not([type="hidden"]),select,textarea', modal)?.focus(), 40);
  };

  const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (!qs('.smart-modal.is-open')) document.body.classList.remove('modal-open');
  };

  document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');
    if (opener) {
      event.preventDefault();
      openModal(opener.dataset.modalOpen, opener.dataset.quickTarget);
      return;
    }
    const closer = event.target.closest('[data-modal-close]');
    if (closer) {
      event.preventDefault();
      closeModal(closer.closest('.smart-modal'));
      return;
    }
    if (event.target.classList.contains('smart-modal')) closeModal(event.target);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      const modals = qsa('.smart-modal.is-open');
      if (modals.length) closeModal(modals[modals.length - 1]);
    }
  });

  const appendOption = (select, item, resource) => {
    let option = [...select.options].find(o => String(o.value) === String(item.id));
    if (!option) {
      option = new Option(item.text, item.id, false, false);
      select.add(option);
    } else {
      option.textContent = item.text;
    }
    if (resource === 'property') {
      const map = ['code','title','owner_id','owner_name','transaction_type','price','monthly_rent','deposit_amount','currency','address'];
      map.forEach(key => {
        if (item[key] !== undefined && item[key] !== null) option.setAttribute(`data-${key.replace(/_/g, '-')}`, item[key]);
      });
    }
    return option;
  };

  const syncResourceSelects = (resource, item, targetId) => {
    qsa(`select[data-smart-resource="${resource}"]`).forEach(select => {
      const option = appendOption(select, item, resource);
      if (select.id === targetId) {
        select.value = String(item.id);
        select.dispatchEvent(new Event('change', { bubbles: true }));
        option.selected = true;
      }
    });
  };

  qsa('form[data-quick-form]').forEach(form => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const modal = form.closest('.smart-modal');
      const errorBox = qs('[data-form-errors]', form);
      const submit = qs('button[type="submit"]', form);
      const label = qs('[data-submit-label]', submit);
      const spinner = qs('.mini-spinner', submit);
      const resource = form.dataset.resource;
      errorBox.hidden = true;
      errorBox.innerHTML = '';
      submit.disabled = true;
      if (label) label.style.opacity = '.65';
      if (spinner) spinner.hidden = false;

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(form),
        });
        const data = await response.json();
        if (!response.ok) {
          const errors = data.errors ? Object.values(data.errors).flat() : [data.message || 'Could not save this record.'];
          errorBox.innerHTML = errors.map(e => `<div>• ${String(e)}</div>`).join('');
          errorBox.hidden = false;
          return;
        }
        syncResourceSelects(resource, data.item, modal.dataset.quickTarget);
        toast(data.message || 'Saved and selected.', data.created === false ? 'warning' : 'success');
        form.reset();
        closeModal(modal);
        document.dispatchEvent(new CustomEvent('rahnuma:quick-created', { detail: { resource, item: data.item } }));
      } catch (error) {
        errorBox.textContent = 'Network or server error. Please try again.';
        errorBox.hidden = false;
      } finally {
        submit.disabled = false;
        if (label) label.style.opacity = '1';
        if (spinner) spinner.hidden = true;
      }
    });
  });

  const propertyOffer = qs('[data-property-offer-type]');
  const updateQuickPropertyMoney = () => {
    if (!propertyOffer) return;
    const type = propertyOffer.value;
    qsa('[data-property-money]').forEach(el => {
      const kind = el.dataset.propertyMoney;
      el.classList.toggle('conditional-hidden', type === 'rent' ? kind === 'sale' : kind === 'rent');
    });
  };
  propertyOffer?.addEventListener('change', updateQuickPropertyMoney);
  updateQuickPropertyMoney();

  window.RahnumaSmart = { toast, openModal, closeModal };
})();
