<?php
if (!defined('APP_NAME')) require_once __DIR__ . '/auth.php';
$db = getDB();
$notifCount = isLoggedIn() ? getUnreadNotificationCount($db) : 0;
$role = getUserRole();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$styleVersion = filemtime(__DIR__ . '/../assets/css/style.css');
// Visiting the site root (/LuxuryStay/) has no PHP filename, but it serves index.php.
// Treat it as the home page so the Home link keeps its active state.
if ($currentPage === '' || $currentPage === '.') {
    $currentPage = 'index';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/images/favicon.svg">
    <link rel="shortcut icon" href="<?= APP_URL ?>/assets/images/favicon.svg" type="image/svg+xml">
    <meta name="app-url" content="<?= e(APP_URL) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css?v=<?= $styleVersion ?>" rel="stylesheet">
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
    <link href="<?= $css ?>" rel="stylesheet">
    <?php endforeach; endif; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<?php $flash = getFlash(); if ($flash): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div class="toast show align-items-center text-bg-<?= e($flash['type']) ?> border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"><?= e($flash['message']) ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg luxury-nav <?= e($navClass ?? '') ?>">
    <div class="container">
        <a class="navbar-brand luxury-brand" href="<?= APP_URL ?>/index.php">
            <i class="bi bi-gem text-gold"></i> <?= e(APP_NAME) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'properties' ? 'active' : '' ?>" href="<?= APP_URL ?>/properties.php">Accommodations</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="<?= APP_URL ?>/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="<?= APP_URL ?>/contact.php">Contact</a></li>
            </ul>
            <ul class="navbar-nav nav-actions">
                <?php if ($role === 'user'): ?>
                <li class="nav-item">
                    <a class="nav-link nav-icon-link position-relative" href="<?= APP_URL ?>/user/notifications.php" aria-label="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        <?php if ($notifCount > 0): ?><span class="badge-notif"><?= $notifCount ?></span><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle nav-user-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-sm"><?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?></span>
                        <span class="nav-user-name"><?= e($_SESSION['name'] ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/user/bookings.php"><i class="bi bi-calendar-check me-2"></i>My Bookings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php elseif ($role === 'owner'): ?>
                <li class="nav-item"><a class="btn btn-gold btn-sm nav-action-btn" href="<?= APP_URL ?>/owner/dashboard.php"><i class="bi bi-building-gear"></i><span>Owner Panel</span></a></li>
                <li class="nav-item"><a class="btn btn-outline-gold btn-sm nav-action-btn" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
                <?php elseif ($role === 'admin'): ?>
                <li class="nav-item"><a class="btn btn-gold btn-sm nav-action-btn" href="<?= APP_URL ?>/admin/dashboard.php"><i class="bi bi-shield-lock"></i><span>Admin Panel</span></a></li>
                <li class="nav-item"><a class="btn btn-outline-gold btn-sm nav-action-btn" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
                <?php else: ?>
                <li class="nav-item"><a class="btn btn-outline-gold btn-sm nav-action-btn" href="<?= APP_URL ?>/login.php"><i class="bi bi-box-arrow-in-right"></i><span>Login</span></a></li>
                <li class="nav-item"><a class="btn btn-gold btn-sm nav-action-btn" href="<?= APP_URL ?>/register.php"><i class="bi bi-person-plus"></i><span>Register</span></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
