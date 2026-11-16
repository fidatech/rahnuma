# Rahnuma Platform backend

Laravel 13 multi-tenant Afghanistan Rahnama ERP + public property marketplace + government-ready regulatory demonstration layer.

## Included domains

- Rahnama tenants and staff
- Plans/subscriptions
- Properties, photos, owners/landlords
- Customers and requirements
- Customer/property matching
- Leads, visits and marketplace inquiries
- Sale, rent and Mortgage / بیع وفا transactions
- Configurable commission calculation
- Rent schedules
- Payments and expenses
- Double-entry chart of accounts / journals / trial balance / ledger
- Cross-Rahnama cooperation requests
- Public marketplace and Rahnama profiles
- Internal transaction verification codes
- Government-ready licenses, Setta books/serials, inspections and violations
- Sanctum REST API for Flutter
- English, Dari and Pashto locale infrastructure

## Quick start

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Demo accounts (change immediately):

- admin@rahnuma.af / ChangeMe123!
- government@rahnuma.af / ChangeMe123!
- inspector@rahnuma.af / ChangeMe123!
- demo@rahnuma.af / ChangeMe123!

`GOVERNMENT_MODE=false` by default: private deployments must not represent internal records as Ministry verification.
