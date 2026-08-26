<?php
$dashRole = $dashRole ?? getUserRole();
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="col-lg-2 dashboard-sidebar d-none d-lg-block">
    <nav class="nav flex-column">
        <?php if ($dashRole === 'user'): ?>
        <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a class="nav-link <?= $current === 'bookings.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/user/bookings.php"><i class="bi bi-calendar-check me-2"></i>My Bookings</a>
        <a class="nav-link <?= $current === 'reviews.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/user/reviews.php"><i class="bi bi-star me-2"></i>My Reviews</a>
        <a class="nav-link <?= $current === 'recent.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/user/recent.php"><i class="bi bi-clock-history me-2"></i>Recently Viewed</a>
        <a class="nav-link <?= $current === 'notifications.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/user/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a>
        <a class="nav-link <?= $current === 'profile.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/user/profile.php"><i class="bi bi-person me-2"></i>Profile</a>
        <?php elseif ($dashRole === 'owner'): ?>
        <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/owner/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a class="nav-link <?= $current === 'properties.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/owner/properties.php"><i class="bi bi-building me-2"></i>Properties</a>
        <a class="nav-link <?= $current === 'add-property.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/owner/add-property.php"><i class="bi bi-plus-circle me-2"></i>Add Property</a>
        <a class="nav-link <?= $current === 'rooms.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/owner/rooms.php"><i class="bi bi-door-open me-2"></i>Rooms</a>
        <a class="nav-link <?= $current === 'availability.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/owner/availability.php"><i class="bi bi-calendar3 me-2"></i>Availability</a>
        <a class="nav-link <?= $current === 'bookings.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/owner/bookings.php"><i class="bi bi-journal-check me-2"></i>Bookings</a>
        <a class="nav-link <?= $current === 'profile.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/owner/profile.php"><i class="bi bi-person-badge me-2"></i>Profile</a>
        <a class="nav-link <?= $current === 'report_owner.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/report_owner.php"><i class="bi bi-graph-up-arrow me-2"></i>Reports</a>
        <?php elseif ($dashRole === 'admin'): ?>
        <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a class="nav-link <?= $current === 'properties.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/properties.php"><i class="bi bi-building me-2"></i>Properties</a>
        <a class="nav-link <?= $current === 'users.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/users.php"><i class="bi bi-people me-2"></i>Users</a>
        <a class="nav-link <?= $current === 'owners.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/owners.php"><i class="bi bi-person-badge me-2"></i>Owners</a>
        <a class="nav-link <?= $current === 'bookings.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/bookings.php"><i class="bi bi-calendar-check me-2"></i>Bookings</a>
        <a class="nav-link <?= $current === 'report_admin.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/report_admin.php"><i class="bi bi-bar-chart-line me-2"></i>Reports</a>
        <a class="nav-link <?= $current === 'reviews.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/reviews.php"><i class="bi bi-star me-2"></i>Reviews</a>
        <a class="nav-link <?= $current === 'profile.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/admin/profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
        <?php endif; ?>
        <a class="nav-link" href="<?= APP_URL ?>"><i class="bi bi-house me-2"></i>Back to Site</a>
    </nav>
</div>
