# Rahnuma Smart Workflow V3

Apply over the existing project root:

```bash
cd /Users/mastaan/Code/rahnuma-platform
unzip -o ~/Downloads/rahnuma-smart-v3-patch.zip -d .
cd backend
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan serve
```

No database migration is required.

## Included
- Primary property owner linkage and ownership relations
- Quick-create Owner / Customer / Property modals
- AJAX save + immediate selection
- Duplicate-aware owner/customer quick create
- Context-aware transaction form
- Auto-fill property owner, offer type, currency and commercial amounts
- Sale/Rent/Mortgage conditional fields
- Live commission preview
- Auto-link seller/landlord as property owner when property has no owner
- Human-readable selectors in Leads / Visits / Payments instead of raw IDs
- Quick-create connected records from operational forms
- Smart property form owner creation and conditional commercial fields
