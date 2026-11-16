(() => {
  const qs = (s, c = document) => c.querySelector(s);
  const qsa = (s, c = document) => [...c.querySelectorAll(s)];
  const csrf = () => qs('meta[name="csrf-token"]')?.content || '';

  const T = window.RAHNUMA_TEXT || {};

  // messages use the toasts of the template (app.js)
  const toast = (message, type = 'success') => {
    if (window.ERP?.toast) window.ERP.toast(message, type);
  };

  const openModal = (id, target = null) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.dataset.quickTarget = target || '';
    if (modal.classList.contains('show')) return;
    const openCount = qsa('.modal.show').length;
    window.ERP.showModal(modal);
    // a pop-up opened from another pop-up sits above it
    modal.style.zIndex = String(1055 + (openCount * 10));
    if (modal._back) modal._back.style.zIndex = String(1050 + (openCount * 10));
  };

  const closeModal = (modal) => {
    if (!modal) return;
    window.ERP.hideModal(modal);
  };

  const applyPropertyMeta = (option, item) => {
    if (!option || !item) return;
    ['code','title','owner_id','owner_name','transaction_type','price','monthly_rent','deposit_amount','currency','address'].forEach((key) => {
      if (item[key] !== undefined && item[key] !== null) option.setAttribute(`data-${key.replace(/_/g, '-')}`, item[key]);
    });
  };

  const appendOption = (select, item, resource) => {
    let option = [...select.options].find(o => String(o.value) === String(item.id));
    if (!option) {
      option = new Option(item.text, item.id, false, false);
      select.add(option);
    } else {
      option.textContent = item.text;
    }
    if (resource === 'property') applyPropertyMeta(option, item);
    return option;
  };

  const syncResourceSelects = (resource, item, targetId) => {
    qsa(`select[data-smart-resource="${resource}"]`).forEach(select => {
      const option = appendOption(select, item, resource);
      if (select.id === targetId) {
        option.selected = true;
        if (window.jQuery && select.classList.contains('select2-hidden-accessible')) {
          window.jQuery(select).val(String(item.id)).trigger('change');
        } else {
          select.value = String(item.id);
          select.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }
    });
  };

  const submitQuickForm = async (form) => {
    const modal = form.closest('.modal');
    const errorBox = qs('[data-form-errors]', form);
    const submit = qs('button[type="submit"]', form);
    const label = qs('[data-submit-label]', submit);
    const spinner = qs('.mini-spinner', submit);
    const resource = form.dataset.resource;
    if (errorBox) { errorBox.hidden = true; errorBox.innerHTML = ''; }
    if (submit) submit.disabled = true;
    if (label) label.style.opacity = '.65';
    if (spinner) spinner.hidden = false;

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
      });
      const data = await response.json();
      if (!response.ok) {
        const errors = data.errors ? Object.values(data.errors).flat() : [data.message || T.couldNotSave || 'Could not save this record.'];
        if (errorBox) { errorBox.innerHTML = errors.map(e => `<div>${String(e)}</div>`).join(''); errorBox.hidden = false; }
        return;
      }
      syncResourceSelects(resource, data.item, modal?.dataset.quickTarget);
      toast(data.message || T.saved || 'Saved and selected.', data.created === false ? 'warning' : 'success');
      form.reset();
      closeModal(modal);
      window.RahnumaSPA?.clearCache?.();
      document.dispatchEvent(new CustomEvent('rahnuma:quick-created', { detail: { resource, item: data.item } }));
    } catch (_) {
      if (errorBox) { errorBox.textContent = T.networkError || 'Network or server error. Please try again.'; errorBox.hidden = false; }
    } finally {
      if (submit) submit.disabled = false;
      if (label) label.style.opacity = '1';
      if (spinner) spinner.hidden = true;
    }
  };

  document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');
    if (opener) {
      event.preventDefault();
      openModal(opener.dataset.modalOpen, opener.dataset.quickTarget);
      setTimeout(() => window.RahnumaSelect2?.init(document.getElementById(opener.dataset.modalOpen)), 20);
      return;
    }
    const closer = event.target.closest('[data-modal-close]');
    if (closer) {
      event.preventDefault();
      closeModal(closer.closest('.modal'));
      return;
    }

    qsa('[data-filter-menu][open]').forEach((menu) => {
      if (!menu.contains(event.target)) menu.removeAttribute('open');
    });

    const chip = event.target.closest('[data-filter-clear]');
    if (chip) {
      const form = qs('[data-filter-form]');
      const input = form?.elements?.namedItem(chip.dataset.filterClear);
      if (input) {
        input.value = '';
        form.requestSubmit();
      }
    }
  });

  document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-quick-form]');
    if (!form) return;
    event.preventDefault();
    event.stopPropagation();
    submitQuickForm(form);
  }, true);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      qsa('[data-filter-menu][open]').forEach(menu => menu.removeAttribute('open'));
    }
    if (event.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName || '')) {
      const search = qs('[data-filter-search]');
      if (search) { event.preventDefault(); search.focus(); search.select?.(); }
    }
  });

  document.addEventListener('change', (event) => {
    if (event.target.matches('[data-property-offer-type]')) updateQuickPropertyMoney(event.target);
  });

  const updateQuickPropertyMoney = (offer = qs('[data-property-offer-type]')) => {
    if (!offer) return;
    const type = offer.value;
    qsa('[data-property-money]', offer.closest('form') || document).forEach(el => {
      const kind = el.dataset.propertyMoney;
      el.classList.toggle('conditional-hidden', type === 'rent' ? kind === 'sale' : kind === 'rent');
    });
  };

  const select2 = (() => {
    const $ = window.jQuery;
    if (!$ || !$.fn?.select2) return { init() {}, refresh() {} };

    const placeholderFor = (select) => select.dataset.placeholder || select.getAttribute('aria-label') || select.querySelector('option[value=""]')?.textContent?.trim() || T.selectOption || 'Select an option';
    const dropdownParentFor = (select) => {
      // inside a pop-up the list must open inside it, or it would sit behind it
      const modal = select.closest('.modal');
      if (modal) return $(modal);
      return $(document.body);
    };

    const initOne = (select) => {
      if (!select || select.matches('.sr-only-select,[data-select2-skip],[data-plain]') || select.classList.contains('select2-hidden-accessible')) return;
      const remoteUrl = select.dataset.remoteUrl;
      const optionCount = select.options?.length || 0;
      const options = {
        width: '100%',
        dir: document.documentElement.dir || 'ltr',
        placeholder: placeholderFor(select),
        allowClear: !select.multiple && !!select.querySelector('option[value=""]'),
        dropdownParent: dropdownParentFor(select),
      };

      if (remoteUrl) {
        options.minimumInputLength = 0;
        options.ajax = {
          url: remoteUrl,
          dataType: 'json',
          delay: 140,
          cache: true,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          data: params => ({ q: params.term || '', page: params.page || 1 }),
          processResults: data => data,
        };
      } else {
        options.minimumResultsForSearch = optionCount > 7 || select.multiple ? 0 : Infinity;
      }

      $(select).select2(options);
      $(select).off('select2:select.rahnuma').on('select2:select.rahnuma', (e) => {
        if (select.dataset.smartResource === 'property') {
          const option = [...select.options].find(o => String(o.value) === String(e.params.data.id));
          applyPropertyMeta(option, e.params.data);
        }
        // Select2 announces a choice through jQuery only: tell the plain listeners of the page too
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });
      $(select).off('select2:unselect.rahnuma select2:clear.rahnuma').on('select2:unselect.rahnuma select2:clear.rahnuma', () => {
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });
    };

    const init = (context = document) => {
      if (!context) return;
      const root = context instanceof Element || context instanceof Document ? context : document;
      root.querySelectorAll('select').forEach(initOne);
    };

    const refresh = (select) => {
      if (!select) return;
      if (!select.classList.contains('select2-hidden-accessible')) initOne(select);
      $(select).trigger('change.select2');
    };

    return { init, refresh };
  })();

  const initSettings = (context = document) => {
    const nav = context.querySelector?.('[data-settings-nav]') || qs('[data-settings-nav]');
    if (!nav || nav.dataset.rahnumaReady) return;
    nav.dataset.rahnumaReady = '1';
    const links = [...nav.querySelectorAll('a[href^="#"]')];
    const sections = links.map(a => qs(a.getAttribute('href'))).filter(Boolean);
    const activate = (id) => links.forEach(a => a.classList.toggle('active', a.getAttribute('href') === `#${id}`));
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(entries => {
        const visible = entries.filter(e => e.isIntersecting).sort((a,b) => b.intersectionRatio-a.intersectionRatio)[0];
        if (visible) activate(visible.target.id);
      }, { rootMargin: '-150px 0px -55% 0px', threshold: [0,.15,.4] });
      sections.forEach(section => observer.observe(section));
    }

    const previewFile = (input, target, asBackground = false) => {
      if (!input || !target || input.dataset.previewReady) return;
      input.dataset.previewReady = '1';
      input.addEventListener('change', () => {
        const file = input.files?.[0]; if (!file) return;
        const url = URL.createObjectURL(file);
        if (asBackground) target.style.backgroundImage = `url("${url}")`;
        else { target.querySelectorAll('img, span').forEach(node => node.remove()); const img=document.createElement('img'); img.src=url; target.prepend(img); }
      });
    };
    previewFile(qs('.brand-cover-action input'), qs('.brand-cover-preview'), true);
    previewFile(qs('.brand-logo-uploader input'), qs('.brand-logo-uploader'));
    previewFile(qs('.profile-avatar-uploader input'), qs('.profile-avatar-uploader'));
  };

  const rehydrate = (context = document) => {
    select2.init(context);
    initSettings(context);
    qsa('[data-property-offer-type]', context).forEach(updateQuickPropertyMoney);
  };

  document.addEventListener('DOMContentLoaded', () => rehydrate(document));
  document.addEventListener('rahnuma:page-loaded', () => rehydrate(document));
  document.addEventListener('rahnuma:quick-created', () => select2.init(document));

  window.RahnumaSmart = { toast, openModal, closeModal, rehydrate };
  window.RahnumaSelect2 = select2;
})();
