(() => {
  // Fast navigation without a full reload. Blade stays the source of every page: the next page is
  // fetched and only its content is put in place, so the sidebar and the top bar stay mounted.
  //
  //   panel pages    #rahnuma-shell (data-shell = workspace | platform | government) > #rahnuma-content
  //   public pages   #rahnuma-public
  //
  // Moving between two different shells is an ordinary page load.
  const pageCache = new Map();
  const quickCache = new Map();
  const CACHE_TTL = 20000;
  const MAX_CACHE = 24;
  const T = window.RAHNUMA_TEXT || {};
  let activeController = null;
  let navigating = false;

  const sameOrigin = (url) => {
    try { return new URL(url, location.href).origin === location.origin; } catch (_) { return false; }
  };

  const cacheSet = (map, key, value) => {
    if (map.size >= MAX_CACHE) map.delete(map.keys().next().value);
    map.set(key, { value, at: Date.now() });
  };
  const cacheGet = (map, key) => {
    const row = map.get(key);
    if (!row || Date.now() - row.at > CACHE_TTL) { if (row) map.delete(key); return null; }
    return row.value;
  };

  const progress = (state) => {
    const bar = document.getElementById('rahnuma-spa-progress');
    document.documentElement.classList.toggle('rahnuma-navigating', state);
    if (!bar) return;
    bar.classList.toggle('is-active', state);
    if (!state) {
      bar.classList.add('is-done');
      setTimeout(() => bar.classList.remove('is-done'), 220);
    }
  };

  const shouldHandleLink = (link, event) => {
    if (!link || !link.href || !sameOrigin(link.href)) return false;
    if (link.target && link.target !== '_self') return false;
    if (link.hasAttribute('download') || link.dataset.native !== undefined || link.dataset.noSpa !== undefined) return false;
    if (link.getAttribute('href').charAt(0) === '#' || link.hasAttribute('data-bs-toggle')) return false;
    if (event && (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button > 0)) return false;
    const url = new URL(link.href);
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return false;
    return true;
  };

  const fetchHtml = async (url, options = {}) => {
    const method = (options.method || 'GET').toUpperCase();
    if (method === 'GET') {
      const cached = cacheGet(pageCache, url);
      if (cached) return cached;
    }

    const response = await fetch(url, {
      ...options,
      headers: {
        ...(options.headers || {}),
        'Accept': 'text/html,application/xhtml+xml',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Rahnuma-SPA': '1',
      },
      credentials: 'same-origin',
    });

    const type = response.headers.get('content-type') || '';
    if (!type.includes('text/html')) throw new Error('non-html-response');
    const html = await response.text();
    const result = { html, url: response.url || url, ok: response.ok, redirected: response.redirected };
    if (method === 'GET' && response.ok) cacheSet(pageCache, result.url, result);
    return result;
  };

  const executePageScripts = (doc) => {
    const holder = doc.querySelector('#rahnuma-page-scripts');
    if (!holder) return;
    [...holder.querySelectorAll('script')].forEach((script) => {
      const fresh = document.createElement('script');
      [...script.attributes].forEach(a => fresh.setAttribute(a.name, a.value));
      if (script.src) fresh.src = script.src;
      else fresh.textContent = script.textContent;
      document.body.appendChild(fresh);
      if (!script.src) fresh.remove();
    });
  };

  const syncDocument = (html, finalUrl, { push = true, scroll = true, restoreScroll = null } = {}) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    const currentShell = document.getElementById('rahnuma-shell');
    const incomingShell = doc.getElementById('rahnuma-shell');
    const currentPublic = document.getElementById('rahnuma-public');
    const incomingPublic = doc.getElementById('rahnuma-public');

    const sameShell = Boolean(currentShell && incomingShell && currentShell.dataset.shell === incomingShell.dataset.shell);
    const samePublic = Boolean(currentPublic && incomingPublic);
    // another language needs the other style sheet (left to right or right to left)
    const sameDirection = (doc.documentElement.dir || 'ltr') === (document.documentElement.dir || 'ltr')
      && (doc.documentElement.lang || '') === (document.documentElement.lang || '');

    if ((!sameShell && !samePublic) || !sameDirection) {
      location.href = finalUrl;
      return;
    }

    const swap = () => {
      window.ERP?.closeAll?.();

      if (sameShell) {
        const currentMain = document.getElementById('rahnuma-content');
        const incomingMain = doc.getElementById('rahnuma-content');
        if (currentMain && incomingMain) currentMain.innerHTML = incomingMain.innerHTML;

        const currentMenu = document.getElementById('sidebarMenu');
        const incomingMenu = doc.getElementById('sidebarMenu');
        if (currentMenu && incomingMenu) currentMenu.innerHTML = incomingMenu.innerHTML;

        const currentTitle = document.getElementById('appbarTitle');
        const incomingTitle = doc.getElementById('appbarTitle');
        if (currentTitle && incomingTitle) currentTitle.textContent = incomingTitle.textContent;

        document.querySelectorAll('.app-tabbar a.tab-item').forEach((tab) => {
          tab.classList.toggle('active', new URL(tab.href, location.href).pathname === new URL(finalUrl, location.href).pathname);
        });
      } else {
        currentPublic.innerHTML = incomingPublic.innerHTML;
      }

      const currentFlashes = document.getElementById('rahnuma-flashes');
      const incomingFlashes = doc.getElementById('rahnuma-flashes');
      if (currentFlashes && incomingFlashes) currentFlashes.innerHTML = incomingFlashes.innerHTML;

      const currentModals = document.getElementById('rahnuma-modals');
      const incomingModals = doc.getElementById('rahnuma-modals');
      if (currentModals) currentModals.innerHTML = incomingModals?.innerHTML || '';

      document.title = doc.title || document.title;
      const currentCsrf = document.querySelector('meta[name="csrf-token"]');
      const incomingCsrf = doc.querySelector('meta[name="csrf-token"]');
      if (currentCsrf && incomingCsrf) currentCsrf.content = incomingCsrf.content;

      if (push && finalUrl !== location.href) history.pushState({ rahnuma: true, scrollY: 0 }, '', finalUrl);
      executePageScripts(doc);
      document.dispatchEvent(new CustomEvent('rahnuma:page-loaded', { detail: { url: finalUrl } }));
      if (restoreScroll !== null) requestAnimationFrame(() => window.scrollTo({ top: restoreScroll, left: 0, behavior: 'auto' }));
      else if (scroll) window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
    };

    if (document.startViewTransition) document.startViewTransition(swap);
    else swap();
  };

  const navigate = async (url, opts = {}) => {
    if (opts.push !== false) history.replaceState({ ...(history.state || {}), rahnuma: true, scrollY: window.scrollY }, '', location.href);
    if (navigating && !opts.force) activeController?.abort();
    navigating = true;
    activeController = new AbortController();
    progress(true);
    try {
      const result = await fetchHtml(url, { signal: activeController.signal });
      syncDocument(result.html, result.url, opts);
      // an address that points at a part of the page (settings#profile) goes to that part
      const hash = new URL(url, location.href).hash;
      if (hash && hash.length > 1) {
        setTimeout(() => {
          const target = document.getElementById(decodeURIComponent(hash.slice(1)));
          if (!target) return;
          history.replaceState(history.state, '', location.pathname + location.search + hash);
          target.scrollIntoView({ block: 'start' });
        }, 80);
      }
    } catch (error) {
      if (error.name !== 'AbortError') location.href = url;
    } finally {
      navigating = false;
      progress(false);
    }
  };

  const submitForm = async (form) => {
    if (form.dataset.quickForm !== undefined || form.dataset.native !== undefined || form.target) return;
    const method = (form.method || 'GET').toUpperCase();
    if (method === 'GET') {
      const url = new URL(form.action || location.href, location.href);
      url.search = new URLSearchParams(new FormData(form)).toString();
      return navigate(url.toString());
    }

    progress(true);
    form.classList.add('is-submitting');
    pageCache.clear();
    quickCache.clear();
    try {
      const result = await fetchHtml(form.action || location.href, { method, body: new FormData(form) });
      if (!result.ok) {
        // the server answered with an error page: show it as it is. Loading the address of the
        // form again would ask for it with GET and hide the real error behind "method not allowed".
        document.open(); document.write(result.html); document.close();
        return;
      }
      // an answer without a redirect belongs to the page we are on, not to the address of the form
      const stayed = !result.redirected;
      syncDocument(result.html, stayed ? location.href : result.url, { push: !stayed, scroll: true });
    } catch (_) {
      form.submit();
    } finally {
      form.classList.remove('is-submitting');
      progress(false);
    }
  };

  // ------------------------------------------------------------------ quick view
  // A row or a card marked data-quick-view opens its record in a pop-up of the template.
  const LOADING = '<div class="modal-body text-center py-5"><span class="spinner-border text-primary" role="status" aria-hidden="true"></span></div>';

  const ensureQuickSheet = () => {
    let sheet = document.getElementById('rahnuma-quick-sheet');
    if (sheet) return sheet;
    sheet = document.createElement('div');
    sheet.id = 'rahnuma-quick-sheet';
    sheet.className = 'modal fade quick-view-modal';
    sheet.tabIndex = -1;
    sheet.setAttribute('aria-hidden', 'true');
    sheet.innerHTML = `
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="quick-sheet-title"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${T.close || 'Close'}"></button>
          </div>
          <div id="quick-sheet-content"></div>
        </div>
      </div>`;
    document.body.appendChild(sheet);
    return sheet;
  };

  const closeQuick = () => {
    const sheet = document.getElementById('rahnuma-quick-sheet');
    if (sheet) window.ERP?.hideModal?.(sheet);
  };

  const openQuick = async (url, title) => {
    const sheet = ensureQuickSheet();
    sheet.querySelector('#quick-sheet-title').textContent = title || T.quickView || 'Quick view';
    const content = sheet.querySelector('#quick-sheet-content');
    content.innerHTML = LOADING;
    if (!sheet.classList.contains('show')) window.ERP?.showModal?.(sheet);

    try {
      let html = cacheGet(quickCache, url);
      if (!html) {
        const res = await fetch(url, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        if (!res.ok) throw new Error('quick-view-failed');
        html = await res.text();
        cacheSet(quickCache, url, html);
      }
      content.innerHTML = html;
      document.dispatchEvent(new CustomEvent('rahnuma:quick-view-loaded', { detail: { url } }));
    } catch (_) {
      content.innerHTML = `<div class="modal-body"><div class="alert alert-danger mb-0">${T.previewFailed || 'Could not load this preview. Open the full record instead.'}</div></div>`;
    }
  };

  const prefetch = (link) => {
    const quick = link.dataset.quickView;
    if (quick && !cacheGet(quickCache, quick)) {
      fetch(quick, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.ok ? r.text() : null).then(html => { if (html) cacheSet(quickCache, quick, html); }).catch(() => {});
      return;
    }
    if (!shouldHandleLink(link) || cacheGet(pageCache, link.href)) return;
    fetchHtml(link.href).catch(() => {});
  };

  document.addEventListener('click', (event) => {
    if (event.defaultPrevented) return;

    const quickClose = event.target.closest('[data-quick-close]');
    if (quickClose) { event.preventDefault(); closeQuick(); return; }

    const quick = event.target.closest('[data-quick-view]');
    const interactive = event.target.closest('a, button, input, select, textarea, label, form');
    if (quick && (!interactive || interactive === quick)) {
      event.preventDefault();
      event.stopPropagation();
      openQuick(quick.dataset.quickView, quick.dataset.quickViewTitle);
      return;
    }

    const link = event.target.closest('a[href]');
    if (!shouldHandleLink(link, event)) return;
    event.preventDefault();
    navigate(link.href);
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.quickForm !== undefined || form.dataset.native !== undefined || event.defaultPrevented) return;
    if (!sameOrigin(form.action || location.href)) return;
    event.preventDefault();
    submitForm(form);
  });

  document.addEventListener('pointerenter', (event) => {
    const link = event.target.closest?.('a[href], [data-quick-view]');
    if (!link) return;
    clearTimeout(link.__rahnumaPrefetch);
    link.__rahnumaPrefetch = setTimeout(() => prefetch(link), 70);
  }, true);

  window.addEventListener('popstate', (event) => navigate(location.href, { push: false, scroll: false, restoreScroll: event.state?.scrollY ?? 0, force: true }));

  if ('serviceWorker' in navigator && location.protocol !== 'file:') {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}), { once: true });
  }

  window.RahnumaSPA = { navigate, openQuick, closeQuick, clearCache: () => { pageCache.clear(); quickCache.clear(); } };
})();
