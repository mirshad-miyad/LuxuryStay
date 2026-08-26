<?php
/**
 * LuxuryStay - Main Configuration
 */

define('APP_NAME', 'LuxuryStay');
define('APP_TAGLINE', 'Sri Lanka\'s Premier Accommodation Platform');
// Set APP_URL in your hosting environment (for example: https://example.com).
define('APP_URL', rtrim((string) (getenv('APP_URL') ?: 'http://localhost/luxurystay/'), '/'));

// Database — InfinityFree MySQL.
define('DB_HOST', (string) (getenv('DB_HOST') ?: 'localhost'));
define('DB_NAME', (string) (getenv('DB_NAME') ?: 'luxurystay'));
define('DB_USER', (string) (getenv('DB_USER') ?: 'root'));
define('DB_PASS', (string) (getenv('DB_PASS') ?: ''));

// Paths
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('PROPERTY_UPLOAD', UPLOAD_PATH . 'properties' . DIRECTORY_SEPARATOR);
define('ROOM_UPLOAD', UPLOAD_PATH . 'rooms' . DIRECTORY_SEPARATOR);
define('USER_UPLOAD', UPLOAD_PATH . 'profile' . DIRECTORY_SEPARATOR);

// Session
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
define('SESSION_NAME', 'LUXURYSTAY_SESSION');

// Security
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Districts in Sri Lanka
define('DISTRICTS', [
    'Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Matale', 'Nuwara Eliya',
    'Galle', 'Matara', 'Hambantota', 'Jaffna', 'Kilinochchi', 'Mannar',
    'Vavuniya', 'Mullaitivu', 'Batticaloa', 'Ampara', 'Trincomalee',
    'Kurunegala', 'Puttalam', 'Anuradhapura', 'Polonnaruwa', 'Badulla',
    'Monaragala', 'Ratnapura', 'Kegalle',
    'Mirissa', 'Ella', 'Dambulla', 'Weligama', 'Sigiriya', 'Unawatuna'
]);

// Provinces in Sri Lanka
define('PROVINCES', [
    'Western', 'Central', 'Southern', 'Northern', 'Eastern',
    'North Western', 'North Central', 'Uva', 'Sabaragamuwa'
]);

// Property types
define('PROPERTY_TYPES', ['Hotel', 'Villa', 'Resort', 'Guest House']);

// Booking statuses
define('BOOKING_STATUSES', ['pending', 'confirmed', 'cancelled', 'completed']);

// Timezone
date_default_timezone_set('Asia/Colombo');

// Show detailed errors only while developing locally.
$isDebug = filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL);
error_reporting($isDebug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');
