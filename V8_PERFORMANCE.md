# Rahnuma V8 — Fast Workspace / SPA-like Navigation

V8 is a performance release focused on perceived speed and query scalability without replacing Laravel/Blade with a new frontend framework.

## Browser / UI performance

- SPA-like same-origin navigation: Blade remains authoritative, but page content is fetched and swapped without full document reloads.
- Workspace shell persistence: header/sidebar remain mounted while only `.workspace-main` changes.
- Browser history and scroll restoration.
- Thin navigation progress bar and View Transitions when supported.
- Link prefetch on intent/hover with a bounded short-lived cache.
- GET filter forms and normal Laravel forms work through the navigation layer.
- Quick View drawer for properties, transactions, owners, customers, leads, visits, payments and expenses.
- Select2 is initialized once and rehydrated after partial page changes.
- Static service worker caches only local application assets, never authenticated HTML.

## Database / server performance

- Removed full owner/customer/property/transaction/user collections from creation forms.
- Remote tenant-scoped Select2 lookup endpoint with pagination.
- Aggregate dashboard / index statistics instead of repeated count queries where practical.
- Marketplace home payload cached and explicitly invalidated by publishing/promotion/status/settings changes.
- Eager-loaded only the relationships required by listing cards.
- Added database indexes for common tenant, marketplace, pipeline, finance and lookup queries.
- API listing endpoints avoid loading full property media collections.

## Image performance

- New uploads can generate WebP listing thumbnails through native GD/WebP.
- Listing cards use `thumbnail_path` when available, falling back safely to the original.
- Images use lazy loading / async decoding where appropriate.
- Full originals remain available on the property detail gallery.
- Existing property images can be backfilled with:

```bash
php artisan rahnuma:optimize-images
```

If PHP GD/WebP is unavailable, the system continues to work and simply falls back to original images.

## Production web-server performance

`backend/public/.htaccess` now includes compression and long-lived static asset caching for Apache/cPanel deployments.

For production deployment also use Laravel's normal optimized deployment flow, for example:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

## Important

V8 has passed source-level PHP and JavaScript syntax validation in the generation environment. A literal "100% performance" guarantee is not possible without runtime benchmarks on the target Mac/server, database size, PHP extensions and network. Measure actual timings after applying V8 and optimize any remaining hot paths from real evidence.
