# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Rationale: Add customer name and contact to installments for better tracking

*   **Chosen Approach:** Added `customer_name` and `customer_contact` columns to the `installments` table.
*   **Reason for Decision:** The business needs to track who made an installment purchase. Storing the customer's name and contact information directly in the `installments` table is the most direct way to associate a buyer with an installment plan.

### Added
- **Database Schema:** Added `customer_name` (VARCHAR) and `customer_contact` (VARCHAR) columns to the `installments` table in `scripts/tialo_posdb.sql`.

### Changed
- **Checkout Process:** Modified `src/modules/pos/checkout.php` to include input fields for customer name and contact when the installment payment method is selected.
- **Checkout Processing:** Updated `src/modules/pos/process_checkout.php` to save the customer's name and contact information to the `installments` table.
- **Installment Report:** Updated `src/modules/reports/installment_report.php` to display the customer's name and contact information in the report.


### Rationale: Ensure accurate inventory status across the application

*   **Chosen Approach:** Implemented a multi-layered approach to ensure product status is always accurate. First, the backend processing for creating/editing products now automatically sets the status to "Available" or "Out of Stock" based on quantity. Second, the main inventory table now dynamically displays the status, overriding the database value if the quantity is zero.
*   **Reason for Decision:** This ensures data integrity and provides a consistent, accurate view of stock levels to users at all times. The backend logic prevents incorrect data from being saved, while the frontend display logic provides an immediate, real-time reflection of the stock status, preventing any discrepancies.

### Changed
-   **Inventory UI:** Modified `src/modules/inventory/index.php` to dynamically display a product's status as "Out of Stock" if its quantity is zero, regardless of the value stored in the database.
-   **Product Processing:** Updated `src/modules/inventory/process_product.php` to ignore the `status` field from the form and instead automatically set it to "Available" or "Out of Stock" based on the product's quantity.


### Rationale: Automate "Out of Stock" status

*   **Chosen Approach:** Created a database trigger that automatically updates a product's status to "Out of Stock" when its quantity reaches zero after a transaction.
*   **Reason for Decision:** Manually updating the status is inefficient and prone to error. A trigger ensures that the inventory status is always synchronized with the actual stock quantity in real-time, providing accurate information across the entire system (both POS and Inventory modules).

### Added
-   **Database:** Added an `after_transaction_item_insert` trigger to `scripts/tialo_posdb.sql`. This trigger checks the product quantity after a sale and updates its status if it drops to zero.



### Rationale: Visually indicate and disable out-of-stock products in POS

*   **Chosen Approach:** Implemented conditional styling and attributes on the product cards in the POS catalog. When a product's quantity is zero, the card is visually distinguished (grayed out, "Out of Stock" overlay) and the "Add to Cart" button is disabled.
*   **Reason for Decision:** This provides immediate, clear feedback to the user, preventing confusion and the erroneous attempt to add unavailable items to the cart. The visual changes make the stock status obvious at a glance, improving the user experience and workflow efficiency for cashiers.

### Changed
-   **POS UI:** Modified `src/modules/pos/index.php` to apply different Tailwind CSS classes to product cards based on their stock quantity. Out-of-stock items are now visually disabled and their "Add to Cart" buttons are deactivated.
-   **POS UI:** Updated the stock status label to clearly show "Out of stock" in red when quantity is zero, "Low stock" in amber for quantities below the threshold, and the exact stock count otherwise.



### Rationale: Enhance POS search to include product ID

*   **Chosen Approach:** Modified the POS search functionality to query against both the product name and the product ID.
*   **Reason for Decision:** Users wanted a faster way to find products, especially when the exact name is not known or is cumbersome to type. Allowing search by the unique product ID provides a direct and efficient way to locate specific items.

### Changed
-   **POS Backend:** The product search query in `src/modules/pos/index.php` was updated to include the `product_id` field in the `WHERE` clause.
-   **POS Frontend:** The JavaScript-based search suggestions in `public/assets/js/pos.js` were updated to filter by both product `name` and `id`, ensuring the suggestions match the backend's search logic.



### Rationale: Add functionality to mark installments as paid

*   **Chosen Approach:** Added an "Actions" column to the installment report with a "Mark as Paid" button for each unpaid installment. This button submits a POST request to a new processing script that updates the installment's status in the database.
*   **Reason for Decision:** The system already had the database structure for installments but lacked a user interface for admins to update the payment status. This approach integrates the functionality directly into the existing installment report, providing a natural and intuitive workflow for administrators to manage customer payments without needing a separate, complex interface.

### Added
- Created `src/modules/reports/process_installment_payment.php` to handle the logic for updating an installment's status to 'Paid'.

### Changed
- Modified `src/modules/reports/installment_report.php` to include an "Actions" column and a "Mark as Paid" button on unpaid installments.

### Rationale: Remove 'Settings' tab from sidebar

*   **Chosen Approach:** The 'Settings' tab was removed from the sidebar navigation.
*   **Reason for Decision:** The application currently does not have any user-facing settings that require a dedicated navigation tab. Removing it simplifies the UI and reduces clutter.

### Removed
-   **UI:** 'Settings' tab removed from the sidebar navigation for all user roles.

### Rationale: Improve UI/UX of POS system

*   **Chosen Approach:** Implemented visual feedback for user actions, enhanced empty states, and added keyboard shortcut discoverability in the Point of Sale (POS) system.
*   **Reason for Decision:** These changes improve the overall usability, provide clearer communication to the user about their interactions, and enhance efficiency for power users through better shortcut visibility.

### Added
-   **UI/UX:** Implemented toast notifications for `addToCart` and `proceedToCheckout` actions in the POS system for clearer visual feedback.
-   **UI/UX:** Enhanced the "Cart is empty" display in the POS system with an icon and more guiding text.
-   **UI/UX:** Added a keyboard shortcuts modal in the POS system, accessible via `F1`, to improve discoverability of shortcuts.


---

## [3.0.0] - 2025-11-22

This version marks a major architectural refactoring to improve the project's security, structure, and maintainability.

### Rationale: Why a Front Controller?

During the planning phase for cleaning up the project structure, a key architectural decision was made to implement a **Front Controller Pattern** with a **Public Document Root**.

*   **Chosen Approach:** All web requests are now directed to a single entry point (`public/index.php`), which then intelligently routes the request to the appropriate application logic hidden in the non-web-accessible `src/` directory.

*   **Alternative Considered:** A simpler alternative would have been to just move files into categorized folders but leave all PHP scripts directly accessible from the web. For example, moving `modules/` into `public/modules/`.

*   **Reason for Decision:** The simpler alternative was rejected because it failed to solve the primary security risk. Exposing all application files directly to the web is a major vulnerability. The Front Controller pattern is an industry-standard best practice that provides three critical benefits:
    1.  **Security:** It makes it impossible for users to directly access sensitive files like database configurations or application logic from a browser. This is the single most important security improvement from the refactor.
    2.  **Centralized Control:** All requests go through one place, allowing for consistent handling of sessions, security checks, and routing logic.
    3.  **Flexibility & Clean URLs:** It allows for clean, user-friendly URLs and makes the application easier to maintain, as the internal file structure can be changed without breaking the external URLs that users see.

### Security
- **Implemented Front Controller Pattern:** All web server traffic is now directed to `public/index.php`. The server's document root should be pointed *only* to the `public/` directory, preventing any direct web access to PHP logic, configuration files, or other sensitive assets in the `src/` directory.

### Changed
-   **UI/UX:** Increased the size of the "remove item" icon and added padding to its button in the POS cart.

### Fixed
-   **POS Cart:** The cart now persists across page reloads and category navigation. It no longer empties unexpectedly.
**Project Structure:** The entire project was restructured into two main directories: `public/` (for web-accessible files) and `src/` (for all PHP application logic).
- **URL Handling:** All hardcoded URLs, HTML form actions, and PHP `header()` redirects were refactored to use the new router-based URL system (e.g., `/index.php?page=users`).
- **Include Paths:** All `include` and `require` statements were updated to use robust, absolute paths based on the `__DIR__` magic constant to prevent path resolution errors.
- **Session Handling:** Redundant `session_start()` calls were removed from included files, as the session is now reliably started once in the front controller.
- **Database Schema:** Added `discount_amount` column (DECIMAL(10,2) NOT NULL DEFAULT 0.00) to the `transactions` table to store discount values for each transaction.

### Added
- **`public/index.php`:** A new front controller file that acts as a central router for the entire application.
- **`public/.htaccess`:** Rules for Apache to enable URL rewriting, directing all requests to the front controller.
- **`CHANGELOG.md`:** This file was created to document major changes and architectural decisions.
- **`README.md`:** The README was significantly updated with detailed, step-by-step instructions for local setup (XAMPP) and live deployment based on the new architecture.
