# Tialo Japan Surplus - POS System

A comprehensive Point of Sale (POS) system with inventory management, built with **PHP, MySQL, HTML, CSS, and JavaScript**, featuring a modern UI powered by **Tailwind CSS** and **Font Awesome Icons**.

## Features

### Authentication
- Secure login/logout system with modern design
- Role-based access control (Admin, Cashier)
- Password hashing with bcrypt

### POS Module
- Product search and category filtering
- Shopping cart with quantity management
- Multiple payment options (Cash, GCash, Installment)
- E-receipt generation
- Manual price discounts

### Inventory Management
- Shipment tracking (incoming deliveries)
- Product CRUD operations
- Stock level management
- Product categorization

### Dashboard & Reports
- Daily sales overview
- Low stock alerts
- Top-selling products
- Sales reports (daily/weekly/monthly)
- Installment tracking
- Inventory reports
- CSV export functionality

### User Management
- Add/edit/delete users
- Role assignment
- User activity tracking

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- XAMPP (recommended for local development)
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

### 1. Setup Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `tialo_posdb`
3. Import the SQL script from `scripts/01_create_database.sql`
4. The database will be created with all tables and a sample admin user

### 2. Configure Database Connection

Edit `includes/db_connect.php` and update the following if needed:

\`\`\`php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tialo_posdb');
\`\`\`

### 3. Place Files in XAMPP

1. Copy the entire `tialo_pos` folder to `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
2. Access the application at `http://localhost/tialo_pos/`

### 4. Setup Tailwind CSS (Frontend Framework)

The application uses **Tailwind CSS via CDN** for styling, which means no build process is required. The Tailwind CSS CDN link is already included in the HTML files.

#### If you want to use Tailwind CSS Build Process (Optional):

For production optimization, you can set up Tailwind CSS with a build process:

1. **Install Node.js** from [nodejs.org](https://nodejs.org/)

2. **Initialize Node.js project** in your `tialo_pos` directory:
   \`\`\`bash
   npm init -y
   \`\`\`

3. **Install Tailwind CSS and dependencies**:
   \`\`\`bash
   npm install -D tailwindcss postcss autoprefixer
   npx tailwindcss init -p
   \`\`\`

4. **Update `tailwind.config.js`**:
   \`\`\`js
   module.exports = {
     content: [
       "./**/*.php",
       "./assets/js/**/*.js",
     ],
     theme: {
       extend: {},
     },
     plugins: [],
   }
   \`\`\`

5. **Create `assets/css/input.css`**:
   \`\`\`css
   @tailwind base;
   @tailwind components;
   @tailwind utilities;
   \`\`\`

6. **Build Tailwind CSS**:
   \`\`\`bash
   npx tailwindcss -i ./assets/css/input.css -o ./assets/css/output.css
   \`\`\`

7. **Update HTML files** - Replace the CDN link with:
   \`\`\`html
   <link rel="stylesheet" href="/assets/css/output.css">
   \`\`\`

8. **Add to `package.json` scripts** (Optional - for easier building):
   \`\`\`json
   "scripts": {
     "build:css": "tailwindcss -i ./assets/css/input.css -o ./assets/css/output.css",
     "watch:css": "tailwindcss -i ./assets/css/input.css -o ./assets/css/output.css --watch"
   }
   \`\`\`

### 5. Icon Library (Font Awesome)

The application uses **Font Awesome 6.4** for icons, loaded via CDN. Icons are already integrated in the HTML files.

To use Font Awesome icons, use the `<i>` tag with Font Awesome classes:
\`\`\`html
<i class="fas fa-store"></i>
<i class="fas fa-cash-register"></i>
<i class="fas fa-boxes"></i>
\`\`\`

Find more icons at [Font Awesome Icons](https://fontawesome.com/icons)

### 6. Login

Use the default admin credentials:
- **Email**: admin@tialo.com
- **Password**: admin123

**Important**: Change this password immediately after first login!

## Project Structure

\`\`\`
tialo_pos/
├── assets/
│   ├── css/
│   │   ├── tailwind-custom.css    (Custom Tailwind utilities)
│   │   └── [deprecated CSS files removed]
│   ├── js/
│   │   └── pos.js                 (POS cart functionality)
│   └── img/
│
├── includes/
│   ├── db_connect.php             (Database connection)
│   ├── header.php                 (Navigation bar - Tailwind)
│   ├── footer.php                 (Footer - Tailwind)
│   ├── auth_check.php             (Session & role verification)
│   └── tailwind-cdn.html          (Tailwind + Font Awesome CDN)
│
├── modules/
│   ├── auth/
│   │   ├── login.php              (Login page - Tailwind)
│   │   ├── login_process.php
│   │   └── logout.php
│   ├── pos/
│   │   ├── index.php              (Product catalog - Tailwind)
│   │   ├── checkout.php           (Checkout - Tailwind)
│   │   ├── process_checkout.php
│   │   ├── receipt.php            (E-receipt - Tailwind)
│   │   └── save_cart.php
│   ├── inventory/
│   │   ├── index.php              (Main inventory - Tailwind)
│   │   ├── shipments.php          (Shipments - Tailwind)
│   │   ├── shipment_form.php      (Shipment form - Tailwind)
│   │   ├── products.php           (Products - Tailwind)
│   │   ├── product_form.php       (Product form - Tailwind)
│   │   ├── process_shipment.php
│   │   └── process_product.php
│   ├── reports/
│   │   ├── index.php              (Reports - Tailwind)
│   │   ├── sales_report.php
│   │   ├── installment_report.php
│   │   ├── inventory_report.php
│   │   └── export.php
│   └── users/
│       ├── index.php              (User management - Tailwind)
│       ├── user_form.php          (User form - Tailwind)
│       └── process_user.php
│
├── scripts/
│   ├── 01_create_database.sql
│   └── 02_sample_data.sql
│
├── index.php                      (Entry point)
├── dashboard.php                  (Admin dashboard - Tailwind)
└── README.md                      (This file)
\`\`\`

## Tailwind CSS Classes Used

### Common Utilities
- **Spacing**: `px-4`, `py-2`, `mb-8`, `gap-6`
- **Colors**: `bg-emerald-600`, `text-slate-900`, `border-blue-500`
- **Sizing**: `w-full`, `h-48`, `max-w-7xl`
- **Typography**: `text-lg`, `font-bold`, `text-slate-600`
- **Layout**: `flex`, `grid`, `grid-cols-3`, `gap-4`
- **Responsive**: `md:flex`, `lg:col-span-3`, `sm:grid-cols-2`
- **Effects**: `shadow-md`, `hover:shadow-lg`, `transition`, `rounded-xl`
- **Displays**: `flex items-center`, `justify-between`, `space-x-2`

### Color Scheme
- **Primary**: Emerald (`emerald-600`) - for actions and highlights
- **Secondary**: Slate (`slate-900`) - for text and backgrounds
- **Accent**: Blue, Purple, Amber - for different data categories
- **Neutral**: Gray shades for borders and dividers

## User Roles & Permissions

### Admin
- Full system access
- Inventory management
- Reports and analytics
- User management
- Dashboard access

### Cashier
- POS operations only
- View inventory (read-only)
- Process transactions
- View receipts

## Database Schema

### Users Table
- user_id (Primary Key)
- name
- email (Unique)
- password (Hashed)
- role (Admin/Cashier)
- created_at

### Shipments Table
- shipment_id (Primary Key)
- date_received
- time_received
- supplier
- driver_name
- total_boxes
- created_at

### Products Table
- product_id (Primary Key)
- shipment_id (Foreign Key)
- name
- category
- quantity
- price
- status (Available/Sold/Out of Stock)
- created_at

### Transactions Table
- transaction_id (Primary Key)
- user_id (Foreign Key)
- transaction_date
- payment_type (Cash/GCash/Installment)
- total_amount

### Transaction Items Table
- item_id (Primary Key)
- transaction_id (Foreign Key)
- product_id (Foreign Key)
- quantity
- subtotal

### Installments Table
- installment_id (Primary Key)
- transaction_id (Foreign Key)
- due_date
- amount_due
- balance_remaining
- status (Paid/Unpaid)
- created_at

## Testing Checklist

### Authentication
- [ ] Login with valid credentials
- [ ] Login with invalid credentials (should fail)
- [ ] Logout functionality
- [ ] Session timeout
- [ ] Role-based access control

### POS Module
- [ ] Search products
- [ ] Filter by category
- [ ] Add items to cart
- [ ] Update cart quantities
- [ ] Remove items from cart
- [ ] Apply discounts
- [ ] Checkout with Cash payment
- [ ] Checkout with GCash payment
- [ ] Checkout with Installment payment
- [ ] Generate receipt
- [ ] Print receipt

### Inventory Management
- [ ] Add shipment
- [ ] Edit shipment
- [ ] Delete shipment
- [ ] Add product
- [ ] Edit product
- [ ] Delete product
- [ ] Update product quantity
- [ ] Change product status

### Dashboard & Reports
- [ ] View daily sales
- [ ] View low stock alerts
- [ ] View top-selling products
- [ ] Generate sales reports
- [ ] Filter reports by date range
- [ ] Filter reports by payment type
- [ ] View installment reports
- [ ] Export reports to CSV

### User Management
- [ ] Add new user
- [ ] Edit user details
- [ ] Change user role
- [ ] Delete user
- [ ] Prevent self-deletion

## Deployment Guide

### Local Development (XAMPP)

1. Start Apache and MySQL from XAMPP Control Panel
2. Access application at `http://localhost/tialo_pos/`
3. Use admin credentials to login

### Production Deployment

1. **Choose Hosting**: Select a PHP hosting provider (e.g., Bluehost, SiteGround, HostGator)
2. **Upload Files**: Use FTP/SFTP to upload all files to the server
3. **Database Setup**: Create database and import SQL script via hosting control panel
4. **Update Configuration**: Modify `includes/db_connect.php` with production database credentials
5. **Set Permissions**: Ensure proper file permissions (644 for files, 755 for directories)
6. **Enable HTTPS**: Install SSL certificate for secure transactions
7. **Backup**: Set up regular database backups
8. **Monitor**: Monitor system performance and error logs

### Security Recommendations

- [ ] Change default admin password
- [ ] Use strong passwords for all users
- [ ] Enable HTTPS/SSL
- [ ] Regularly update PHP and MySQL
- [ ] Implement regular backups
- [ ] Use environment variables for sensitive data
- [ ] Enable error logging (disable error display in production)
- [ ] Implement rate limiting for login attempts
- [ ] Use prepared statements (already implemented)
- [ ] Validate and sanitize all inputs (already implemented)

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `includes/db_connect.php`
- Ensure database `tialo_posdb` exists

### Login Issues
- Clear browser cookies and cache
- Verify user exists in database
- Check password is correct

### Cart Not Working
- Enable JavaScript in browser
- Check browser console for errors
- Verify session is active

### Tailwind Styles Not Loading
- **Using CDN**: Check internet connection and CDN availability
- **Using Build Process**: Run `npm run build:css` to regenerate output.css
- Clear browser cache (Ctrl+Shift+Delete on Windows, Cmd+Shift+Delete on Mac)

### Reports Not Showing Data
- Verify transactions exist in database
- Check date filters are correct
- Ensure user has Admin role

## Frontend Technologies

- **Tailwind CSS 3.3+** - Utility-first CSS framework
- **Font Awesome 6.4** - Icon library
- **Vanilla JavaScript** - No frameworks required
- **HTML5** - Semantic markup
- **Responsive Design** - Mobile, tablet, and desktop friendly

## Support & Maintenance

For issues or feature requests, contact the development team.

## License

This project is proprietary software for Tialo Japan Surplus.

---

**Version**: 2.0.0 (Tailwind CSS Redesign)  
**Last Updated**: 2025  
**Frontend Framework**: Tailwind CSS 3.3+  
**Icon Library**: Font Awesome 6.4
