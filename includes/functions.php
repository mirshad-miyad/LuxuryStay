<?php
/**
 * Reusable helper functions
 */

function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function formatPrice(float $amount): string
{
    return 'Rs ' . number_format($amount, 2);
}

function formatRelativeTime(?string $dateTime): string
{
    if (!$dateTime) return '';

    $timestamp = strtotime($dateTime);
    if (!$timestamp) return '';

    $diff = max(0, time() - $timestamp);
    if ($diff < 60) return 'Just now';

    $minutes = (int) floor($diff / 60);
    if ($minutes < 60) return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';

    $hours = (int) floor($minutes / 60);
    if ($hours < 24) return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';

    $days = (int) floor($hours / 24);
    if ($days < 7) return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';

    return date('M j, Y', $timestamp);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']) || !empty($_SESSION['owner_id']) || !empty($_SESSION['admin_id']);
}

function getUserRole(): ?string
{
    if (!empty($_SESSION['admin_id'])) return 'admin';
    if (!empty($_SESSION['owner_id'])) return 'owner';
    if (!empty($_SESSION['user_id'])) return 'user';
    return null;
}

function requireRole(string ...$roles): void
{
    $current = getUserRole();
    if (!$current || !in_array($current, $roles, true)) {
        flash('danger', 'Please login to access this page.');
        redirect(APP_URL . '/login.php');
    }
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function dbColumnExists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function dbTableExists(PDO $db, string $table): bool
{
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    return in_array($table, $tables, true);
}

function ensureOwnerFeatureSchema(PDO $db): void
{
    static $done = false;
    if ($done) return;

    $propertyColumns = [
        'city' => "ALTER TABLE properties ADD COLUMN city VARCHAR(100) NULL AFTER address",
        'province' => "ALTER TABLE properties ADD COLUMN province VARCHAR(100) NULL AFTER city",
        'contact_phone' => "ALTER TABLE properties ADD COLUMN contact_phone VARCHAR(30) NULL AFTER map_iframe",
        'contact_email' => "ALTER TABLE properties ADD COLUMN contact_email VARCHAR(150) NULL AFTER contact_phone",
        'latitude' => "ALTER TABLE properties ADD COLUMN latitude DECIMAL(10,8) NULL AFTER contact_email",
        'longitude' => "ALTER TABLE properties ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude",
        'is_active' => "ALTER TABLE properties ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER status",
        'deleted_at' => "ALTER TABLE properties ADD COLUMN deleted_at DATETIME NULL AFTER is_active",
    ];
    foreach ($propertyColumns as $column => $sql) {
        if (!dbColumnExists($db, 'properties', $column)) {
            $db->exec($sql);
        }
    }

    $roomColumns = [
        'weekend_price' => "ALTER TABLE rooms ADD COLUMN weekend_price DECIMAL(12,2) NULL AFTER price_per_night",
        'inventory' => "ALTER TABLE rooms ADD COLUMN inventory INT NOT NULL DEFAULT 1 AFTER max_guests",
    ];
    foreach ($roomColumns as $column => $sql) {
        if (!dbColumnExists($db, 'rooms', $column)) {
            $db->exec($sql);
        }
    }

    if (!dbColumnExists($db, 'property_images', 'sort_order')) {
        $db->exec("ALTER TABLE property_images ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER is_primary");
    }

    $db->exec("CREATE TABLE IF NOT EXISTS room_amenities (
        room_id INT NOT NULL,
        amenity_id INT NOT NULL,
        PRIMARY KEY (room_id, amenity_id),
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
        FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
    )");

    $done = true;
}

function ensureUserProfileSchema(PDO $db): void
{
    static $done = false;
    if ($done) return;

    $userColumns = [
        'address' => "ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL AFTER phone",
        'profile_image' => "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER address",
    ];

    foreach ($userColumns as $column => $sql) {
        if (!dbColumnExists($db, 'users', $column)) {
            $db->exec($sql);
        }
    }

    $done = true;
}

function ensureOwnerProfileSchema(PDO $db): void
{
    static $done = false;
    if ($done) return;

    $ownerColumns = [
        'address' => "ALTER TABLE owners ADD COLUMN address VARCHAR(255) NULL AFTER phone",
        'profile_image' => "ALTER TABLE owners ADD COLUMN profile_image VARCHAR(255) NULL AFTER address",
        'business_description' => "ALTER TABLE owners ADD COLUMN business_description TEXT NULL AFTER company_name",
    ];

    foreach ($ownerColumns as $column => $sql) {
        if (!dbColumnExists($db, 'owners', $column)) {
            $db->exec($sql);
        }
    }

    $done = true;
}

function ensureAdminProfileSchema(PDO $db): void
{
    static $done = false;
    if ($done) return;

    $adminColumns = [
        'phone' => "ALTER TABLE admins ADD COLUMN phone VARCHAR(20) NULL AFTER email",
        'address' => "ALTER TABLE admins ADD COLUMN address VARCHAR(255) NULL AFTER phone",
        'profile_image' => "ALTER TABLE admins ADD COLUMN profile_image VARCHAR(255) NULL AFTER address",
    ];

    foreach ($adminColumns as $column => $sql) {
        if (!dbColumnExists($db, 'admins', $column)) {
            $db->exec($sql);
        }
    }

    $done = true;
}

function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

function validateUploadedImage(array $file): ?string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The image is larger than the upload limit.',
            UPLOAD_ERR_NO_FILE => 'No image was selected.',
            default => 'The image could not be uploaded.',
        };
    }

    if (($file['size'] ?? 0) <= 0) {
        return 'The image file is empty.';
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        return 'Images must be 2MB or smaller.';
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return 'The uploaded image is invalid.';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        return 'Only JPG, PNG, and WebP images are allowed.';
    }

    return null;
}

function uploadImage(array $file, string $destination): ?string
{
    if (validateUploadedImage($file) !== null) return null;

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg',
    };
    $filename = uniqid('img_', true) . '.' . $ext;
    $path = $destination . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return str_replace(DIRECTORY_SEPARATOR, '/', str_replace(ROOT_PATH, '', $path));
    }
    return null;
}

function deleteUploadedFile(?string $relativePath): bool
{
    if (!$relativePath) return true;

    $relativePath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    $fullPath = ROOT_PATH . $relativePath;
    if (!file_exists($fullPath)) return true;

    $realPath = realpath($fullPath);
    $uploadRoot = realpath(UPLOAD_PATH);
    if (!$realPath || !$uploadRoot || !str_starts_with($realPath, $uploadRoot)) {
        return false;
    }

    return @unlink($realPath);
}

function nightsBetween(string $checkIn, string $checkOut): int
{
    $start = new DateTime($checkIn);
    $end = new DateTime($checkOut);
    return max(0, (int) $start->diff($end)->days);
}

function getStayDates(string $checkIn, string $checkOut): array
{
    try {
        $start = new DateTimeImmutable($checkIn);
        $end = new DateTimeImmutable($checkOut);
    } catch (Exception $e) {
        return [];
    }

    if ($start >= $end) return [];

    $dates = [];
    for ($date = $start; $date < $end; $date = $date->modify('+1 day')) {
        $dates[] = $date;
    }
    return $dates;
}

function isRoomAvailable(PDO $db, int $roomId, string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
{
    ensureOwnerFeatureSchema($db);
    $dates = getStayDates($checkIn, $checkOut);
    if (empty($dates)) return false;

    $roomStmt = $db->prepare("SELECT status, inventory FROM rooms WHERE id = ?");
    $roomStmt->execute([$roomId]);
    $room = $roomStmt->fetch();
    if (!$room || $room['status'] !== 'active') return false;

    $inventory = max(1, (int) ($room['inventory'] ?? 1));

    $stmt = $db->prepare("SELECT COUNT(*) FROM room_availability 
        WHERE room_id = ? AND is_available = 0 AND date >= ? AND date < ?");
    $stmt->execute([$roomId, $checkIn, $checkOut]);
    if ((int) $stmt->fetchColumn() > 0) return false;

    $sql = "SELECT COUNT(*) FROM bookings
            WHERE room_id = ? AND status IN ('pending','confirmed')
            AND check_in <= ? AND check_out > ?";
    if ($excludeBookingId) {
        $sql .= " AND id != ?";
    }
    $stmt = $db->prepare($sql);

    foreach ($dates as $date) {
        $night = $date->format('Y-m-d');
        $params = [$roomId, $night, $night];
        if ($excludeBookingId) $params[] = $excludeBookingId;
        $stmt->execute($params);
        if ((int) $stmt->fetchColumn() >= $inventory) return false;
    }

    return true;
}

function getRoomNightPrices(PDO $db, int $roomId, string $checkIn, string $checkOut): array
{
    ensureOwnerFeatureSchema($db);
    $dates = getStayDates($checkIn, $checkOut);
    if (empty($dates)) return [];

    $roomStmt = $db->prepare("SELECT price_per_night, weekend_price FROM rooms WHERE id = ?");
    $roomStmt->execute([$roomId]);
    $room = $roomStmt->fetch();
    if (!$room) return [];

    $overridesStmt = $db->prepare("SELECT date, custom_price FROM room_availability WHERE room_id = ? AND custom_price IS NOT NULL AND date >= ? AND date < ?");
    $overridesStmt->execute([$roomId, $checkIn, $checkOut]);
    $overrides = [];
    foreach ($overridesStmt->fetchAll() as $row) {
        $overrides[$row['date']] = (float) $row['custom_price'];
    }

    $basePrice = (float) $room['price_per_night'];
    $weekendPrice = $room['weekend_price'] !== null ? (float) $room['weekend_price'] : null;
    $prices = [];

    foreach ($dates as $date) {
        $night = $date->format('Y-m-d');
        $isWeekend = in_array((int) $date->format('N'), [6, 7], true);
        $price = $basePrice;
        $source = 'Base';

        if ($isWeekend && $weekendPrice !== null && $weekendPrice > 0) {
            $price = $weekendPrice;
            $source = 'Weekend';
        }
        if (array_key_exists($night, $overrides)) {
            $price = $overrides[$night];
            $source = 'Custom';
        }

        $prices[] = ['date' => $night, 'price' => $price, 'source' => $source];
    }

    return $prices;
}

function calculateRoomStayTotal(PDO $db, int $roomId, string $checkIn, string $checkOut): float
{
    return array_reduce(getRoomNightPrices($db, $roomId, $checkIn, $checkOut), function (float $total, array $night): float {
        return $total + (float) $night['price'];
    }, 0.0);
}

function ownerPolicyLabels(): array
{
    return [
        'check_in' => 'Check-in policy',
        'check_out' => 'Check-out policy',
        'cancellation' => 'Cancellation policy',
        'child' => 'Child policy',
        'pet' => 'Pet policy',
        'rules' => 'Other rules',
    ];
}

function buildPolicyText(array $fields): string
{
    $lines = [];
    foreach (ownerPolicyLabels() as $key => $label) {
        $value = trim((string) ($fields[$key] ?? ''));
        if ($value !== '') {
            $lines[] = $label . ': ' . $value;
        }
    }
    return implode("\n", $lines);
}

function parsePolicyText(?string $text): array
{
    $fields = array_fill_keys(array_keys(ownerPolicyLabels()), '');
    $labels = ownerPolicyLabels();
    $reverse = [];
    foreach ($labels as $key => $label) {
        $reverse[strtolower($label)] = $key;
    }

    foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
        $line = trim($line);
        if ($line === '') continue;

        $matched = false;
        foreach ($reverse as $label => $key) {
            if (str_starts_with(strtolower($line), $label . ':')) {
                $fields[$key] = trim(substr($line, strlen($label) + 1));
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            $fields['rules'] = trim($fields['rules'] . "\n" . $line);
        }
    }

    return $fields;
}

function validateCoordinate(?string $value, float $min, float $max): float|false|null
{
    $value = trim((string) $value);
    if ($value === '') return null;
    if (!is_numeric($value)) return false;
    $number = (float) $value;
    return ($number >= $min && $number <= $max) ? $number : false;
}

function updatePropertyRating(PDO $db, int $propertyId): void
{
    $stmt = $db->prepare("SELECT AVG(rating), COUNT(*) FROM reviews WHERE property_id = ? AND status = 'approved'");
    $stmt->execute([$propertyId]);
    $row = $stmt->fetch();
    $db->prepare("UPDATE properties SET avg_rating = ?, review_count = ? WHERE id = ?")
        ->execute([round($row[0] ?? 0, 2), (int) ($row[1] ?? 0), $propertyId]);
}

function createNotification(PDO $db, string $title, string $message, string $type = 'info', ?string $link = null, ?int $userId = null, ?int $ownerId = null, ?int $adminId = null): void
{
    $stmt = $db->prepare("INSERT INTO notifications (user_id, owner_id, admin_id, title, message, type, link) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$userId, $ownerId, $adminId, $title, $message, $type, $link]);
}

function getUnreadNotificationCount(PDO $db): int
{
    $role = getUserRole();
    if ($role === 'user' && !empty($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
    } elseif ($role === 'owner' && !empty($_SESSION['owner_id'])) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE owner_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['owner_id']]);
    } elseif ($role === 'admin' && !empty($_SESSION['admin_id'])) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE admin_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['admin_id']]);
    } else {
        return 0;
    }
    return (int) $stmt->fetchColumn();
}

function recordRecentlyViewed(PDO $db, int $userId, int $propertyId): void
{
    $stmt = $db->prepare("INSERT INTO recently_viewed (user_id, property_id) VALUES (?,?)
        ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
    $stmt->execute([$userId, $propertyId]);
}

function paginate(int $total, int $page, int $perPage = ITEMS_PER_PAGE): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    return ['page' => $page, 'per_page' => $perPage, 'offset' => $offset, 'total' => $total, 'total_pages' => $totalPages];
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken(16);
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getPropertyPrimaryImage(?string $path): string
{
    if ($path && file_exists(ROOT_PATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path))) {
        return APP_URL . '/' . str_replace('\\', '/', $path);
    }
    return APP_URL . '/assets/images/placeholder-property.svg';
}
