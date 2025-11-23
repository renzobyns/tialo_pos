# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
