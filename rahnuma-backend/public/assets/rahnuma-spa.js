(() => {
  const pageCache = new Map();
  const quickCache = new Map();
  const CACHE_TTL = 20000;
  const MAX_CACHE = 24;
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
    const result = { html, url: response.url || url };
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
    const incomingPage = doc.querySelector('#rahnuma-page');
    const currentPage = document.querySelector('#rahnuma-page');
    if (!incomingPage || !currentPage) {
      location.href = finalUrl;
      return;
    }

    const swap = () => {
      const currentWorkspace = currentPage.querySelector('.workspace-shell');
      const incomingWorkspace = incomingPage.querySelector('.workspace-shell');
      const sameWorkspaceShell = Boolean(currentWorkspace && incomingWorkspace);

      if (sameWorkspaceShell) {
        const currentMain = currentWorkspace.querySelector('.workspace-main');
        const incomingMain = incomingWorkspace.querySelector('.workspace-main');
        if (currentMain && incomingMain) {
          currentMain.className = incomingMain.className;
          currentMain.innerHTML = incomingMain.innerHTML;
        }

        const incomingLinks = new Map([...incomingWorkspace.querySelectorAll('.workspace-sidebar a[href]')].map(a => [new URL(a.href, finalUrl).pathname + new URL(a.href, finalUrl).search, a.className]));
        currentWorkspace.querySelectorAll('.workspace-sidebar a[href]').forEach((a) => {
          const key = new URL(a.href, location.href).pathname + new URL(a.href, location.href).search;
          if (incomingLinks.has(key)) a.className = incomingLinks.get(key);
        });
      } else {
        const currentHeader = document.querySelector('.site-header');
        const incomingHeader = doc.querySelector('.site-header');
        if (currentHeader && incomingHeader) currentHeader.replaceWith(incomingHeader.cloneNode(true));
        currentPage.className = incomingPage.className;
        currentPage.innerHTML = incomingPage.innerHTML;
      }

      const currentFlashes = document.getElementById('rahnuma-flashes');
      const incomingFlashes = doc.getElementById('rahnuma-flashes');
      if (currentFlashes && incomingFlashes) currentFlashes.innerHTML = incomingFlashes.innerHTML;

      const currentModals = document.getElementById('rahnuma-modals');
      const incomingModals = doc.getElementById('rahnuma-modals');
      if (currentModals) currentModals.innerHTML = incomingModals?.innerHTML || '';

      document.title = doc.title || document.title;
      document.body.className = doc.body.className;
      document.documentElement.lang = doc.documentElement.lang || document.documentElement.lang;
      document.documentElement.dir = doc.documentElement.dir || document.documentElement.dir;
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
    closeQuick();
    if (opts.push !== false) history.replaceState({ ...(history.state || {}), rahnuma: true, scrollY: window.scrollY }, '', location.href);
    if (navigating && !opts.force) activeController?.abort();
    navigating = true;
    activeController = new AbortController();
    progress(true);
    try {
      const result = await fetchHtml(url, { signal: activeController.signal });
      syncDocument(result.html, result.url, opts);
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
      syncDocument(result.html, result.url, { push: true, scroll: true });
    } catch (_) {
      form.submit();
    } finally {
      form.classList.remove('is-submitting');
      progress(false);
    }
  };

  const ensureQuickSheet = () => {
    let sheet = document.getElementById('rahnuma-quick-sheet');
    if (sheet) return sheet;
    sheet = document.createElement('div');
    sheet.id = 'rahnuma-quick-sheet';
    sheet.className = 'quick-sheet';
    sheet.setAttribute('aria-hidden', 'true');
    sheet.innerHTML = `
      <div class="quick-sheet__backdrop" data-quick-close></div>
      <aside class="quick-sheet__panel" role="dialog" aria-modal="true">
        <div class="quick-sheet__header"><div><span>Quick view</span><strong id="quick-sheet-title">Preview</strong></div><button type="button" data-quick-close aria-label="Close">×</button></div>
        <div class="quick-sheet__content" id="quick-sheet-content"><div class="quick-sheet__loading"><span></span><span></span><span></span></div></div>
      </aside>`;
    document.body.appendChild(sheet);
    return sheet;
  };

  const closeQuick = () => {
    const sheet = document.getElementById('rahnuma-quick-sheet');
    if (!sheet) return;
    sheet.classList.remove('is-open');
    sheet.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('quick-view-open');
  };

  const openQuick = async (url, title = 'Quick view') => {
    const sheet = ensureQuickSheet();
    sheet.querySelector('#quick-sheet-title').textContent = title;
    const content = sheet.querySelector('#quick-sheet-content');
    content.innerHTML = '<div class="quick-sheet__loading"><span></span><span></span><span></span></div>';
    sheet.classList.add('is-open');
    sheet.setAttribute('aria-hidden', 'false');
    document.body.classList.add('quick-view-open');

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
      content.innerHTML = '<div class="quick-sheet__error">Could not load this preview. Open the full record instead.</div>';
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
    const quickClose = event.target.closest('[data-quick-close]');
    if (quickClose) { event.preventDefault(); closeQuick(); return; }

    const quick = event.target.closest('[data-quick-view]');
    const interactive = event.target.closest('a, button, input, select, textarea, label, form');
    if (quick && (!interactive || interactive === quick)) {
      event.preventDefault();
      event.stopPropagation();
      openQuick(quick.dataset.quickView, quick.dataset.quickViewTitle || 'Quick view');
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

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeQuick();
  });

  window.addEventListener('popstate', (event) => navigate(location.href, { push: false, scroll: false, restoreScroll: event.state?.scrollY ?? 0, force: true }));

  if ('serviceWorker' in navigator && location.protocol !== 'file:') {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}), { once: true });
  }

  window.RahnumaSPA = { navigate, openQuick, closeQuick, clearCache: () => { pageCache.clear(); quickCache.clear(); } };
})();
