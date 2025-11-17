# Tialo Japan Surplus POS

Modular PHP/MySQL POS & Inventory system with a Tailwind-based UI shell shared across Dashboard, POS, Inventory, Reports, and Users. The redesign follows the **Fiery Red Sunset** palette described in `reference/Final Sitemap.docx`.

## Table of Contents
- [Quick Start](#quick-start)
- [Tech Stack](#tech-stack)
- [Project Layout](#project-layout)
- [Database Dump](#database-dump)
- [Modules](#modules)
- [Front-end](#front-end)
- [Quality Checklist](#quality-checklist)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Support & License](#support--license)

## Quick Start
1. Copy `config.example.php` → `config.php` and set DB host/user/password/port (default port in `includes/db_connect.php` is `3307`).
2. Import `scripts/tialo_posdb.sql` (full schema + your latest data export).
3. Point Apache/nginx document root to the repo and open `/index.php`.
4. If you used `reset_password.php`, delete it after first login.

## Tech Stack
- PHP 8+ with mysqli helpers
- MySQL 5.7/8.0
- Tailwind CSS via CDN (`includes/tailwind-cdn.html`) with small overrides in `assets/css/tailwind-custom.css`
- Fonts: Inter (Google Fonts)
- Icons: legacy Font Awesome header + inline SVGs on refreshed screens
- Front-end logic: vanilla JS (`assets/js/pos.js`)

## Project Layout
```
tialo_pos/
├── assets/
│   ├── css/                     # Tailwind tweaks
│   ├── img/                     # product/reference images
│   └── js/                      # POS/cart logic
├── includes/                    # auth guard, DB connect, sidebar, CDN includes
├── modules/
│   ├── auth/                    # login/logout
│   ├── inventory/               # shipments + products CRUD
│   ├── pos/                     # catalog, checkout, receipts
│   ├── reports/                 # sales/installment/inventory reporting
│   └── users/                   # admin/cashier management
├── scripts/                     # SQL dump(s)
├── reference/Final Sitemap.docx # UX contract/spec
└── dashboard.php, index.php     # landing + role redirect
```

## Database Dump
- Use `scripts/tialo_posdb.sql` (phpMyAdmin export) to provision schema, constraints, and the current sample/working data.
- If you need fresh credentials, generate a bcrypt hash and update the `users` table (or use `reset_password.php` locally, then delete it): `php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"`.
- Dump was generated on MariaDB 10.4.32 (`127.0.0.1:3307`); adjust your import command if the port differs: `mysql -u <user> -p --port=3307 < scripts/tialo_posdb.sql`.

## Modules
- **Authentication & Sessions**: `modules/auth/*`, `includes/auth_check.php` (role-aware guard), `includes/sidebar.php` (collapsible nav scoped by role).
- **Point of Sale**: `modules/pos/index.php` for catalog/cart UI, `modules/pos/complete_sale.php` for checkout (writes `transactions`, `transaction_items`, `installments`, adjusts stock), `modules/pos/receipt.php` for printable receipts.
- **Inventory Management**: `modules/inventory/*` ties products to shipments; products carry status (Available/Sold/Out of Stock).
- **Reports**: `modules/reports/*` with date ranges, payment-type filters, staff filters, and CSV export.
- **User Management**: `modules/users/*` for Admin/Cashier CRUD and listing.

## Front-end
- `assets/js/pos.js` covers cart rendering, discount validation, payment selection (Cash/GCash/Installment), keyboard shortcuts (F2 search, F3–F5 payment), hold/restore cart via `localStorage`, and JSON POST to `complete_sale.php`.
- Palette: Ink `#03071E`, Wine `#370617`, Garnet `#6A040F`, Crimson `#9D0208`, Signal `#D00000`, Fire `#DC2F02`, Citrus `#E85D04`, Amber `#F48C06`, Honey `#FAA307`, Gold `#FFBA08`.
- Typography enforced globally via Inter; Tailwind comes from `includes/tailwind-cdn.html`.

## Quality Checklist
- [ ] `config.php` contains real DB creds/port.
- [ ] Admin/Cashier hashes in the imported DB updated to your team’s credentials.
- [ ] MySQL host/port in `includes/db_connect.php` match your setup.
- [ ] POS flow tested end-to-end (add → discount → checkout → receipt).
- [ ] Real product photos uploaded to `assets/img/` and linked in the `products` table.
- [ ] `reset_password.php` removed after local use.

## Deployment
- Standard LAMP: point DocumentRoot to the repo; ensure PHP sessions are writable.
- Enforce HTTPS + secure cookie flags for non-local deployments.
- If compiling Tailwind instead of CDN, swap the link in `includes/tailwind-cdn.html` for your bundled CSS.

## Troubleshooting
- **Cannot log in**: run `reset_password.php` locally to set a new bcrypt password, then delete the file. Use `debug_auth.php` to confirm hashes start with `$2y$`.
- **Tailwind styles missing**: ensure templates include `tailwind-cdn.html`; clear browser cache after swapping CDN/bundle.
- **POS checkout not working**: check browser console for fetch errors, confirm `complete_sale.php` resolves, and ensure products have stock + PHP JSON enabled.
- **CSV export empty**: verify `transactions` data exists for the selected range/payment filter and that headers are sent before output.
- **Placeholder images**: upload real photos to `assets/img/` and store filenames in `products.image`; adjust the `<img>` tag in `modules/pos/index.php` if needed.

## Support & License
- Proprietary project for Tialo Japan Surplus.
- For internal questions, ping the dev team or log an issue with module + file + line (e.g., `modules/pos/index.php:120`).
- Version: **2.1.0 Fiery Red Refresh** (November 2025).
