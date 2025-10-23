<?php
/**
 * Configuration Example
 * Copy this file to config.php and update with your settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tialo_posdb');

// Application Settings
define('APP_NAME', 'Tialo Japan Surplus');
define('APP_URL', 'http://localhost/tialo_pos');
define('APP_ENV', 'development'); // development or production

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 6);

// Email Settings (for future use)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@gmail.com');
define('MAIL_PASS', 'your-app-password');

// Error Logging
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/logs/error.log');

// Display Errors (disable in production)
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
?>
