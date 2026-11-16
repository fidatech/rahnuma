const CACHE='rahnuma-static-v9';
const STATIC=['/css/app.css','/css/app.rtl.css','/js/app.js','/icons.svg','/assets/rahnuma-smart.js','/assets/rahnuma-spa.js'];
self.addEventListener('install',event=>{self.skipWaiting();event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(STATIC)).catch(()=>{}));});
self.addEventListener('activate',event=>event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim())));
self.addEventListener('fetch',event=>{
  const req=event.request;
  if(req.method!=='GET') return;
  const url=new URL(req.url);
  if(url.origin!==location.origin) return;
  // only files that never hold private data: styles, scripts, fonts, icons and uploaded pictures
  const isStatic=['/assets/','/css/','/js/','/fonts/','/img/','/storage/'].some(p=>url.pathname.startsWith(p))||url.pathname==='/icons.svg';
  if(isStatic){
    event.respondWith(caches.match(req).then(hit=>hit||fetch(req).then(res=>{if(res.ok){const copy=res.clone();caches.open(CACHE).then(c=>c.put(req,copy));}return res;})));
  }
});
