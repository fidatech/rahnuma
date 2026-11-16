# cPanel / Production Deployment

Recommended layout:

```text
/home/USER/rahnuma-app      Laravel application
/home/USER/public_html      domain document root (or point the domain directly to rahnuma-app/public)
```

Preferred: set the domain document root to `/home/USER/rahnuma-app/public`.

## Install / update

```bash
cd /home/USER/rahnuma-app
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan rahnuma:optimize-images
php artisan optimize
```

Use `APP_ENV=production`, `APP_DEBUG=false`, a real database, HTTPS, and a production cache/session driver appropriate for the server.

## Scheduler

```cron
* * * * * cd /home/USER/rahnuma-app && php artisan schedule:run >> /dev/null 2>&1
```

## Static performance

V8 includes `public/.htaccess` with Apache compression and browser caching for CSS, JavaScript and images. Confirm `mod_deflate` / `mod_expires` are available on the target cPanel server.

## Government mode

Keep `GOVERNMENT_MODE=false` until a written Ministry authorization/integration exists.
