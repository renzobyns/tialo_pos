# Tialo Japan Surplus POS

A modular Point of Sale + Inventory system for Tialo Japan Surplus. The codebase is PHP/MySQL on the back end with Tailwind-driven UI patterns and a collapsible sidebar shell shared by every module (Dashboard, POS, Inventory, Reports, Users). The current sprint focused on aligning every screen with the **Fiery Red Sunset** palette plus a cleaner card-based layout described in `reference/Final Sitemap.docx`.

## Table of Contents
- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [UI System](#ui-system)
- [Directory Layout](#directory-layout)
- [Getting Started](#getting-started)
  - [Requirements](#requirements)
  - [Clone & Configure](#clone--configure)
  - [Database](#database)
  - [Run the App](#run-the-app)
- [Modules](#modules)
  - [Authentication & Sessions](#authentication--sessions)
  - [Dashboard](#dashboard)
  - [Point of Sale](#point-of-sale)
  - [Inventory Management](#inventory-management)
  - [Reports](#reports)
  - [User Management](#user-management)
- [Data & Utilities](#data--utilities)
- [Styling & Front-end](#styling--front-end)
- [Quality Checklist](#quality-checklist)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Support & License](#support--license)

## Overview
This repository delivers the full store workflow:
- **Authentication** with bcrypt-protected accounts, role gating (Admin vs Cashier) and a collapsible sidebar that adapts to the signed-in role.
- **POS** optimized for 17"/1080p laptops: catalog filtering, keyboard shortcuts (F2–F5), discount validation, Cash/GCash/Installment checkout, and digital receipts.
- **Inventory** split between shipment folders and the live product catalog, mirroring the "folder + contents" spec from the Final Sitemap.
- **Reports** for Sales, Installment, and Inventory logs with date ranges, payment-type filters, staff filters, and CSV export.
- **User Management** with CRUD + role filters so admins can isolate Cashiers quickly.

## Tech Stack
- **Language**: PHP 8+ (mysqli + procedural helpers)
- **Database**: MySQL 5.7/8.0 (DDL in `scripts/01_create_database.sql`)
- **Styling**: Tailwind CSS (CDN) + small utility overrides in `assets/css/tailwind-custom.css`
- **Icons**: Font Awesome 6.4 (legacy header) + inline Heroicons/Lucide-inspired SVGs in the new sidebar and forms
- **Fonts**: Inter via Google Fonts
- **Front-end logic**: Vanilla JS (`assets/js/pos.js`) for cart state, payment toggles, keyboard shortcuts, and async checkout
- **Session/Auth utilities**: `includes/auth_check.php`, `includes/sidebar.php`

## UI System
### Fiery Red Sunset Palette
| Token | Hex | Primary Usage |
| --- | --- | --- |
| Ink | `#03071E` | App shell background, overlays |
| Wine | `#370617` | Gradient midpoint, subtle borders |
| Garnet | `#6A040F` | Headlines, emphasis text |
| Crimson | `#9D0208` | Hover/active states |
| Signal | `#D00000` | Primary CTA buttons and badges |
| Fire | `#DC2F02` | Secondary buttons, chips |
| Citrus | `#E85D04` | Alerts and highlights |
| Amber | `#F48C06` | Focus rings, selections |
| Honey | `#FAA307` | Informational tags |
| Gold | `#FFBA08` | Breadcrumbs, accent text |

**Typography & Icons**
- Inter is enforced globally (see every `<style>* { font-family: 'Inter' }</style>` block).
- Font Awesome is still used in legacy `includes/header.php`; all modern screens rely on inline SVGs for crisp icons without extra network calls.
- `includes/sidebar.php` contains the collapsible navigation with CSS variables and localStorage-backed state.

## Directory Layout
```
tialo_pos/
├── assets/
│   ├── css/
│   │   └── tailwind-custom.css        # optional utility overrides
│   ├── img/                           # drop product/reference images here
│   └── js/
│       └── pos.js                     # cart + checkout interactions
├── includes/
│   ├── auth_check.php                 # session guard + role helper
│   ├── db_connect.php                 # mysqli connection (defaults to port 3307)
│   ├── header.php                     # legacy top nav (Font Awesome)
│   ├── sidebar.php                    # new Fiery Red sidebar shell
│   └── tailwind-cdn.html              # Tailwind + Font Awesome CDN snippet
├── modules/
│   ├── auth/                          # login/logout handlers
│   ├── inventory/                     # products + shipments + CRUD forms
│   ├── pos/                           # catalog, checkout, complete_sale API
│   ├── reports/                       # analytics hub + CSV exporter
│   └── users/                         # role-filtered directory + form
├── scripts/
│   ├── 01_create_database.sql         # schema + admin seed (update hash!)
│   └── 02_sample_data.sql             # optional demo data
├── reference/Final Sitemap.docx       # UX contract/spec used for redesign
├── dashboard.php                      # admin overview screen
├── reset_password.php                 # temporary password reset utility
├── debug_auth.php                     # diagnostics page for auth/db issues
├── DEPLOYMENT_CHECKLIST.md            # production hardening tasks
├── config.example.php                 # future config scaffold (copy to config.php)
├── package.json / pnpm-lock.yaml      # design-system artifacts from Vercel (not required to run PHP)
└── README.md
```

## Getting Started
### Requirements
- PHP 8.1+ with mysqli enabled
- MySQL 5.7+ (MariaDB works). Default port in `db_connect.php` is **3307**; change if needed.
- Apache via XAMPP/Laragon or any server that can serve PHP.
- Modern browser (Chrome, Edge, Firefox, Safari).

### Clone & Configure
1. Copy this folder to your web root (e.g., `C:\xampp\htdocs\tialo_pos`).
2. Duplicate `config.example.php` to `config.php` if you want central config constants (optional today, but ready for future refactors).
3. Open `includes/db_connect.php` and update host/user/password/database/port to match your MySQL instance.

### Database
1. Launch phpMyAdmin (or mysql CLI) and run `scripts/01_create_database.sql`.
   - **Important**: the script ships with placeholder hashes (`$2y$10$YourHashedPasswordHere`). Generate a real bcrypt hash with `php -r "echo password_hash('admin123', PASSWORD_BCRYPT), PHP_EOL;"` and replace the placeholder before importing, or immediately run `reset_password.php` after seeding.
2. (Optional) Seed demo data with `scripts/02_sample_data.sql` (same note about hashes applies).
3. If you need to reset a password later, temporarily expose `reset_password.php`, change the password, then delete/rename the file for security.

### Run the App
1. Start Apache + MySQL from XAMPP.
2. Visit `http://localhost/tialo_pos/` to reach the login page (`modules/auth/login.php`).
3. Sign in with the admin account you provisioned in the previous step and begin exploring the modules via the sidebar.

## Modules
### Authentication & Sessions
- `modules/auth/login.php` and `login_process.php` handle the Inter-styled login form and bcrypt verification.
- `includes/auth_check.php` wraps every protected page (`checkRole('Admin')` or default session guard).
- `modules/auth/logout.php` destroys the session.
- `reset_password.php` and `debug_auth.php` exist for administrators only—use them temporarily, then remove from production.

### Dashboard
- `dashboard.php` surfaces daily sales, transaction counts, low-stock alerts, and top sellers.
- Includes recent transactions and a low-stock widget sourced directly from `products`.
- Shares the same sidebar shell; cards use Tailwind utility classes plus Font Awesome icons for quick scanning.

### Point of Sale
Files: `modules/pos/index.php`, `complete_sale.php`, `checkout.php`, `receipt.php`, `save_cart.php`, `assets/js/pos.js`.
- Catalog tab supports text search, category pills, and infinite-friendly grid cards (images currently use `/placeholder.svg`).
- Right-hand column keeps the cart + payment rail visible; buttons toggle between Cash, GCash, and Installment.
- Keyboard shortcuts: `F2` focuses search, `F3` Cash, `F4` GCash, `F5` Installment.
- Discounts are validated client-side and server-side; `showDiscountError()` gives inline feedback.
- `complete_sale.php` is the JSON endpoint: validates stock, writes `transactions` + `transaction_items`, decrements inventory, and creates installment schedules when needed.
- Successful checkouts redirect to `receipt.php?transaction_id=...` for printable proof of sale.

### Inventory Management
Files: `modules/inventory/index.php`, `product_form.php`, `shipment_form.php`, `process_product.php`, `process_shipment.php`.
- Tabs switch between **Products** (POS-visible catalog) and **Shipments** (incoming deliveries acting as folders).
- Search bars exist for both tabs; results use striped tables with action buttons.
- Forms share the same design language as `modules/users/user_form.php`: label icons, red focus rings, and dual-column layouts.
- Shipment folders capture Date & Time, Supplier, Driver, and box counts; once a folder exists, products can be tied to it.
- Product form tracks status (`Available`, `Sold`, `Out of Stock`), price, quantity, and optional shipment folder.

### Reports
Files: `modules/reports/index.php`, `sales_report.php`, `installment_report.php`, `inventory_report.php`, and `export.php`.
- Tabs: **Sales**, **Installment**, **Inventory**; each inherits the same filter toolbar (period, payment type, staff pick list, custom date range).
- Overview cards compute total sales, transaction counts, and average ticket; the “Top Product” callout queries `transaction_items`.
- Installment tab lists unpaid schedules with due dates and remaining balances.
- Inventory tab includes three reports: current stock, low-stock alert, and stock movement log (recent deductions via sales).
- `export.php` streams CSV files on demand (daily/custom ranges & payment filters).

### User Management
Files: `modules/users/index.php`, `user_form.php`, `process_user.php`.
- Filter chips (`All`, `Admins`, `Cashiers`) + search box for name/email.
- Table includes role pills and inline Delete forms with confirmation prompts.
- Form handles both create/update flows with hidden `action` inputs and enforces password confirmation for new accounts.

## Data & Utilities
- **SQL scripts**: `scripts/01_create_database.sql` (schema + default admin) and `scripts/02_sample_data.sql` (demo shipments/products). Update password hashes before use.
- **Config scaffold**: `config.example.php` centralizes constants (APP_URL, SMTP placeholders). Use it when you refactor `db_connect.php` into dependency-injected config.
- **Diagnostics**: `debug_auth.php` quickly verifies DB connectivity, user counts, and bcrypt hashes—handy when login fails.
- **Docs**: `reference/Final Sitemap.docx` is the product spec; `DEPLOYMENT_CHECKLIST.md` lists hardening tasks.
- **Prototype artifacts**: `package.json` and `pnpm-lock.yaml` originate from the Vercel/Next.js design prototype (v0). They are not required to run the PHP build but can be reused if you keep iterating on that front-end.

## Styling & Front-end
- Every PHP view includes `<script src="https://cdn.tailwindcss.com"></script>` (see `includes/tailwind-cdn.html`). You can swap to a compiled build later if needed; instructions from the previous README still apply if you follow the standard Tailwind CLI workflow.
- `assets/css/tailwind-custom.css` holds custom keyframes (slide/fade) and a scrollbar theme; include it wherever you need those utilities.
- `assets/js/pos.js` is the only global script today. It tracks cart state, clamps discounts, adds payment badges, and fires the async checkout fetch.
- Sidebar collapse state is stored in `localStorage` (see script at the bottom of `includes/sidebar.php`). Delete storage or click the “Navigation” button to toggle.
- Product cards currently render placeholder images. Hook them up to real uploads by storing filenames in the `products` table and pointing the `<img>` tag to `/assets/img/...`.

## Quality Checklist
Use this as a manual QA run when shipping changes:
- [ ] Authentication: valid login, invalid login, logout, session timeout, and Admin-only guard on `/dashboard.php`, `/modules/inventory/*`, `/modules/reports/*`, `/modules/users/*`.
- [ ] POS Catalog: search, category pills, add/remove items, quantity changes, discount validation, keyboard shortcuts.
- [ ] Checkout: Cash, GCash, Installment (with schedule written to `installments`), receipt generation, stock deduction.
- [ ] Inventory: switch tabs, search shipments/products, create/edit/delete product, create/edit/delete shipment, status changes reflected in POS.
- [ ] Reports: change period (Today/Week/Month/Custom), payment filters, staff filters, review Installment and Inventory tabs, download CSV via `export.php`.
- [ ] Users: filter by role, search, create admin/cashier, edit details, delete (excluding self if that restriction is added), verify hashed password stored.
- [ ] Sidebar/UI: collapse state persists, active link styling shows the current module, Fiery Red palette respected (no stray legacy colors).

## Deployment
1. Follow `DEPLOYMENT_CHECKLIST.md` (HTTPS, backups, cron for DB dumps, etc.).
2. Move the project to your production web root and ensure file permissions are 644 (files) / 755 (directories).
3. Create a production database, import `01_create_database.sql`, then create secure admin accounts (do **not** rely on default passwords).
4. Update `includes/db_connect.php` (or migrate to `config.php`) with production credentials and disable verbose error output (`APP_ENV = 'production'`).
5. Remove helper files from the server (`reset_password.php`, `debug_auth.php`, sample SQL files) once setup is complete.
6. Enable HTTPS + HTTP/2 via your hosting panel, and point the domain to the public directory.

## Troubleshooting
- **Database connection failed**: confirm MySQL is running, port matches `db_connect.php` (change 3307 to 3306 if you use default), and credentials are correct.
- **Cannot log in**: run `reset_password.php` locally to set a new bcrypt password, then delete the file. Use `debug_auth.php` to confirm bcrypt hashes start with `$2y$`.
- **Tailwind styles missing**: ensure each template includes `tailwind-cdn.html`. If you move to a compiled CSS build, update the `<link>` tags accordingly and clear the browser cache.
- **POS checkout not working**: check browser console for fetch errors, verify `complete_sale.php` is reachable, and confirm PHP has JSON enabled. Also ensure products have sufficient stock.
- **CSV export empty**: verify there is data in `transactions` for the selected date range/payment filter, and that your PHP install allows header downloads (no premature whitespace output).
- **Placeholder images**: upload actual photos to `assets/img/` and store filenames in the `products` table. Update the `<img>` tag in `modules/pos/index.php` accordingly.

## Support & License
- Proprietary project for Tialo Japan Surplus.
- For internal questions, contact the development team or log an issue referencing the module + file + line (e.g., `modules/pos/index.php:120`).
- Version: **2.1.0 Fiery Red Refresh** (November 2025).
