<?php
/**
 * Session and authentication bootstrap
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function loginUser(array $user, string $role): void
{
    session_regenerate_id(true);
    unset($_SESSION['user_id'], $_SESSION['owner_id'], $_SESSION['admin_id']);
    $_SESSION['role'] = $role;
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];

    if ($role === 'admin') {
        $_SESSION['admin_id'] = $user['id'];
    } elseif ($role === 'owner') {
        $_SESSION['owner_id'] = $user['id'];
    } else {
        $_SESSION['user_id'] = $user['id'];
    }
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function authenticate(string $email, string $password, string $role): ?array
{
    $db = getDB();
    $tables = [
        'user' => 'users',
        'owner' => 'owners',
        'admin' => 'admins',
    ];
    if (!isset($tables[$role])) return null;

    $table = $tables[$role];
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password'])) {
        return null;
    }

    if ($role === 'owner' && ($row['status'] ?? '') === 'suspended') {
        return null;
    }
    if ($role === 'user' && ($row['status'] ?? '') === 'suspended') {
        return null;
    }

    return $row;
}

function registerUser(string $name, string $email, string $phone, string $password, string $role, ?string $company = null): array
{
    $db = getDB();
    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        if ($role === 'owner') {
            $stmt = $db->prepare("INSERT INTO owners (name, email, phone, password, company_name, status) VALUES (?,?,?,?,?,'pending')");
            $stmt->execute([$name, $email, $phone, $hash, $company]);
            $id = (int) $db->lastInsertId();
            createNotification($db, 'New Owner Registration', "Owner {$name} registered and awaits approval.", 'info', null, null, null, 1);
        } else {
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $hash]);
            $id = (int) $db->lastInsertId();
        }
        return ['success' => true, 'id' => $id];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}
