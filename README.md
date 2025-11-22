# Tialo Japan Surplus POS

Modular PHP/MySQL POS & Inventory system with a Tailwind-based UI shell shared across Dashboard, POS, Inventory, Reports, and Users. The redesign follows the **Fiery Red Sunset** palette described in `reference/Final Sitemap.docx`.

**Version: 3.0.0 (Front Controller Refactor)**

This version introduces a robust, secure project structure using the **Front Controller Pattern**, where all requests are routed through `public/index.php`. This improves security by preventing direct web access to application logic and sensitive files.

## Table of Contents
- [Project Layout](#project-layout)
- [Local Development Setup](#local-development-setup)
- [Deployment](#deployment)
- [Tech Stack](#tech-stack)
- [Database Dump](#database-dump)
- [Modules](#modules)
- [Front-end](#front-end)
- [Troubleshooting](#troubleshooting)
- [Support & License](#support--license)

## Project Layout

The project now uses a standard Front Controller layout with a public document root for enhanced security.

```
tialo_pos/
├── public/                    # <-- WEB SERVER DOCUMENT ROOT
│   ├── index.php              # Front Controller (handles all requests)
│   ├── .htaccess              # URL Rewrite Rules
│   └── assets/                # CSS, JS, and image assets
├── src/                       # All PHP application logic (not web-accessible)
│   ├── includes/              # DB connect, auth checks, shared components
│   └── modules/               # Core application modules (auth, pos, etc.)
├── scripts/                   # SQL database dump
├── config.example.php
└── README.md
```

## Local Development Setup

Follow these steps to set up and run the project on a local XAMPP environment.

### 1. Prerequisites
- **XAMPP** installed (or any other Apache/MySQL/PHP stack).
- A text editor with administrator privileges (e.g., run Notepad or VS Code as an administrator).

### 2. Database Setup
1.  Open **phpMyAdmin**.
2.  Create a new database (e.g., `tialo_posdb`).
3.  Select the new database and go to the **Import** tab.
4.  Import the file `scripts/tialo_posdb.sql` to create the tables and add sample data.
5.  Create a copy of `config.example.php` and name it `config.php`.
6.  Edit `config.php` with your database host, username, password, and the database name you just created.

### 3. Apache & Hosts File Configuration
To run the application correctly, you must configure Apache to use the `public` directory as the web root for a custom local domain (e.g., `http://tialopos.local`).

**Step 3.1: Edit Your `hosts` File**
This step maps the custom domain to your local machine.
1.  Open your text editor **as an administrator**.
2.  Open the file: `C:\Windows\System32\drivers\etc\hosts`
3.  Add this line to the bottom of the file:
    ```
    127.0.0.1 tialopos.local
    ```
4.  Save and close the file.

**Step 3.2: Configure Apache Virtual Hosts**
This tells Apache where to find your site's files.
1.  Open the file: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
2.  Add this block of code to the end of the file:
    ```apache
    <VirtualHost *:80>
        DocumentRoot "C:/xampp/htdocs/tialo_pos/public"
        ServerName tialopos.local
        <Directory "C:/xampp/htdocs/tialo_pos/public">
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```

**Step 3.3: Enable Virtual Hosts**
1.  Open the file: `C:\xampp\apache\conf\httpd.conf`
2.  Find the line `#Include conf/extra/httpd-vhosts.conf`.
3.  Remove the `#` to uncomment it.

### 4. Restart Apache and Access
1.  Open the **XAMPP Control Panel**.
2.  **Stop** the Apache module, then **Start** it again to apply the new configuration.
3.  You're all set! Open your browser and navigate to:
    **`http://tialopos.local`**

The application should now be running correctly.

## Deployment

Deploying to a live server (like Hostinger) follows the same principle as the local setup.

1.  **Upload Files:** Upload all project folders (`public`, `src`, `scripts`, etc.) to your hosting account (e.g., into the main `public_html` directory).
2.  **Set the Document Root:** This is the most critical step. In your hosting control panel (e.g., Hostinger hPanel), find the settings for your domain or subdomain. Set the **Document Root** (or "Path") to point directly to your project's `public` directory.
    - Example Path: `public_html/tialo_pos/public`
3.  **Import Database:** Use the host's phpMyAdmin to create your database and import the `tialo_posdb.sql` script.
4.  **Configure `config.php`:** Update your `config.php` file with your live database credentials.

By setting the document root correctly, you ensure your `src` directory remains secure and inaccessible from the web.

## Tech Stack
- PHP 8+ with mysqli helpers
- MySQL 5.7/8.0
- Tailwind CSS via CDN
- Fonts: Inter (Google Fonts)
- Front-end logic: vanilla JS (`assets/js/pos.js`)

## Database Dump
- Use `scripts/tialo_posdb.sql` to provision the schema and data.
- To create a new admin password, you can generate a bcrypt hash: `php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"`.

## Modules
- **Authentication & Sessions**: `src/modules/auth/*`
- **Point of Sale**: `src/modules/pos/*`
- **Inventory Management**: `src/modules/inventory/*`
- **Reports**: `src/modules/reports/*`
- **User Management**: `src/modules/users/*`

## Front-end
- `public/assets/js/pos.js` covers cart logic, discounts, payment selection, and checkout.
- The UI palette is defined in `reference/Final Sitemap.docx`.

## Troubleshooting
- **404 Not Found or Access Forbidden Errors**: This almost always means your Document Root is not correctly configured in Apache/Nginx to point to the `/public` directory. Double-check your virtual host setup.
- **"Failed to open stream" PHP Errors**: If you see path-related errors after the refactor, it means an `include` or `require` path was missed. The paths should now be robustly defined using `__DIR__ . '/path/to/file.php'`.
- **Cannot log in**: Check your `config.php` credentials and ensure your database was imported correctly.

## Support & License
- Proprietary project for Tialo Japan Surplus.
- For internal questions, ping the dev team or log an issue.