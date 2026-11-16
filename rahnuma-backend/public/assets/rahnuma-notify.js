/*
 | The bell of the top bar: counts the alerts, remembers when the list was last opened, and
 | fetches the list again every minute so a new request shows without reloading the page.
 */
(() => {
  const bell = document.getElementById('notifBell');
  if (!bell) return;
  const menu = document.getElementById('notifMenu');
  const badge = bell.querySelector('.notif-count');
  const KEY = 'rahnuma.notif.seen';

  const count = () => {
    const inner = menu.querySelector('.notif-inner');
    if (!inner) return;
    let seen = 0;
    try { seen = Date.parse(localStorage.getItem(KEY) || '') || 0; } catch (e) {}
    let total = parseInt(inner.dataset.countOpen || '0', 10) || 0;
    inner.querySelectorAll('.notif-item[data-at]').forEach((item) => {
      const fresh = (Date.parse(item.dataset.at) || 0) > seen;
      item.classList.toggle('unread', fresh);
      if (fresh) total += 1;
    });
    badge.textContent = total > 9 ? '9+' : String(total);
    badge.hidden = total === 0;
  };

  const refresh = async () => {
    if (document.hidden) return;
    try {
      const response = await fetch(bell.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' }, credentials: 'same-origin' });
      if (!response.ok || response.redirected) return;
      menu.innerHTML = await response.text();
      count();
    } catch (e) { /* the next minute will try again */ }
  };

  // opening the list is what "seen" means for alerts that ask for nothing
  bell.addEventListener('click', () => {
    setTimeout(() => { try { localStorage.setItem(KEY, new Date().toISOString()); } catch (e) {} count(); }, 1500);
  });

  count();
  setInterval(refresh, 60000);
  document.addEventListener('rahnuma:page-loaded', refresh);
})();
