# Rahnuma Platform Source — V2 Professional Redesign

This package contains the multi-tenant Rahnama ERP, Afghanistan property marketplace, government-ready regulatory module and Flutter app, with a redesigned premium product UI.

## V2 visual direction

The first prototype used a green, generic admin treatment. V2 replaces it with a midnight/navy + cobalt + copper system. Green is used only for success states. Key web and mobile workflows have been reorganized around real operational hierarchy rather than generic cards/tables. See `DESIGN_SYSTEM.md`.

This archive contains the generated application source for:

- Laravel 13 multi-tenant Rahnama ERP
- Afghanistan public property marketplace
- Super-admin tenant/plan management
- Government-ready regulatory demonstration module
- Setta books/serial inventory and anti-duplicate Setta use
- Sale, rent and Mortgage / بیع وفا transactions
- Configurable commissions
- Landlords/owners, customers, requirements, matching, leads and visits
- Payments, expenses and double-entry accounting
- Cross-Rahnama cooperation
- REST API with Sanctum
- Flutter mobile application source with public, Rahnama and government/inspector role routing
- Offline public cache and offline property drafts
- cPanel deployment notes

## Directories

- `backend/` — Laravel application source
- `mobile/` — Flutter/Dart application source

## Backend install

See `backend/deploy/CPANEL.md`.

## Mobile install

```bash
cd mobile
chmod +x bootstrap_mobile.sh
./bootstrap_mobile.sh
flutter run --dart-define=API_BASE_URL=https://YOUR-DOMAIN/api/v1
```

The bootstrap script generates the native Android/iOS runner folders with the Flutter SDK installed on your Mac, while preserving the supplied Dart app source.

## Safety / government status

`GOVERNMENT_MODE=false` is the default. Private deployments must not represent internal records as official Ministry verification until a written government integration/authorization exists.