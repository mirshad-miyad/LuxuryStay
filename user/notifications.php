<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
$db = getDB();
$userId = $_SESSION['user_id'];

if (isset($_GET['read'])) {
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
    redirect(APP_URL . '/user/notifications.php');
}

$notifs = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$notifs->execute([$userId]);
$notifs = $notifs->fetchAll();

$pageTitle = 'Notifications';
$dashRole = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10 dashboard-content">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
                <div>
                    <span class="section-label">Updates</span>
                    <h1 class="mb-1">Notifications</h1>
                    <p class="text-muted-light mb-0">Booking, payment, and account alerts.</p>
                </div>
                <a href="?read=1" class="btn btn-outline-gold btn-sm"><i class="bi bi-check2-all me-1"></i>Mark All Read</a>
            </div>

            <div class="notification-list">
                <?php foreach ($notifs as $n):
                    $type = strtolower($n['type'] ?? 'info');
                    $icon = [
                        'success' => 'bi-check-circle',
                        'booking' => 'bi-calendar-check',
                        'payment' => 'bi-credit-card',
                        'warning' => 'bi-exclamation-circle',
                        'info' => 'bi-bell',
                    ][$type] ?? 'bi-bell';
                    $iconTone = [
                        'success' => 'notification-success',
                        'booking' => 'notification-booking',
                        'payment' => 'notification-payment',
                        'warning' => 'notification-warning',
                        'info' => 'notification-info',
                    ][$type] ?? 'notification-info';
                ?>
                <article class="notification-item <?= $n['is_read'] ? '' : 'is-unread' ?>">
                    <?php if (!$n['is_read']): ?><span class="notification-dot"></span><?php endif; ?>
                    <div class="notification-icon <?= e($iconTone) ?>"><i class="bi <?= e($icon) ?>"></i></div>
                    <div class="notification-body">
                        <div class="notification-topline">
                            <h6><?= e($n['title']) ?></h6>
                            <time><?= e(formatRelativeTime($n['created_at'])) ?></time>
                        </div>
                        <p><?= e($n['message']) ?></p>
                        <?php if ($n['link']): ?><a href="<?= e($n['link']) ?>" class="inline-link">View</a><?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>

                <?php if (empty($notifs)): ?>
                <div class="content-card p-4">
                    <div class="empty-state">
                        <i class="bi bi-bell"></i>
                        <h5>No notifications</h5>
                        <p>New booking and payment alerts will appear here.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
