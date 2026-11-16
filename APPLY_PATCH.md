# Apply Rahnuma V8 Fast / SPA Patch

This patch is built for the V7 project currently at `/Users/mastaan/Code/rahnuma-platform`.

```bash
cd /Users/mastaan/Code/rahnuma-platform
unzip -o ~/Downloads/rahnuma-v8-fast-spa-patch.zip -d .

cd backend
php artisan migrate
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan storage:link 2>/dev/null || true
php artisan rahnuma:optimize-images
php artisan serve
```

Then hard refresh once with `Cmd + Shift + R`.

The image optimization command is safe to run after migration. If GD/WebP is unavailable it will leave existing originals untouched and the UI will fall back to them.

## What to verify

1. Click between Workspace pages: header/sidebar should remain mounted and there should be no full browser refresh.
2. Hover/click Property or Transaction rows/cards: Quick View should open in the side sheet.
3. Click Owner, Customer, Lead, Visit, Payment or Expense rows: Quick View should open without navigation.
4. Open Transaction/Create: owner/customer/property Select2 searches should query remotely instead of loading the full tenant database.
5. Marketplace/inventory cards should lazy-load thumbnails when available.
