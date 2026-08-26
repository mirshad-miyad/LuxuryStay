<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
$db = getDB();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$stmt->execute([$userId]);
$stats['bookings'] = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed'");
$stmt->execute([$userId]);
$stats['confirmed'] = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$stats['pending'] = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE user_id = ? AND payment_status = 'paid'");
$stmt->execute([$userId]);
$stats['spent'] = (float) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'cancelled'");
$stmt->execute([$userId]);
$stats['cancelled'] = (int) $stmt->fetchColumn();

$recentBookings = $db->prepare("SELECT b.*, p.name as property_name, p.district, r.name as room_name,
    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as image
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5");
$recentBookings->execute([$userId]);
$recentBookings = $recentBookings->fetchAll();

$statCards = [
    ['label' => 'Total Bookings', 'value' => $stats['bookings'], 'icon' => 'bi-calendar2-check', 'tone' => 'blue'],
    ['label' => 'Confirmed', 'value' => $stats['confirmed'], 'icon' => 'bi-patch-check', 'tone' => 'green'],
    ['label' => 'Pending', 'value' => $stats['pending'], 'icon' => 'bi-hourglass-split', 'tone' => 'yellow'],
    ['label' => 'Total Spent', 'value' => formatPrice($stats['spent']), 'icon' => 'bi-wallet2', 'tone' => 'sky'],
    ['label' => 'Cancelled', 'value' => $stats['cancelled'], 'icon' => 'bi-x-circle', 'tone' => 'red'],
];

$pageTitle = 'User Dashboard';
$dashRole = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10 dashboard-content">
            <div class="dashboard-heading mb-4">
                <span class="section-label">Guest dashboard</span>
                <h1 class="mb-1">Welcome, <span class="text-gold"><?= e($_SESSION['name']) ?></span></h1>
                <p class="text-muted-light mb-0">Your LuxuryStay activity at a glance.</p>
            </div>

            <div class="row g-3 dashboard-stat-grid mb-4">
                <?php foreach ($statCards as $card): ?>
                <div class="col-sm-6 col-xl">
                    <div class="stat-card stat-card-<?= e($card['tone']) ?>">
                        <div class="stat-card-icon"><i class="bi <?= e($card['icon']) ?>"></i></div>
                        <div>
                            <div class="stat-label"><?= e($card['label']) ?></div>
                            <div class="stat-value"><?= e((string) $card['value']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="content-card p-4">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3 flex-wrap">
                    <div>
                        <span class="section-label">Recent activity</span>
                        <h5 class="mb-0">Recent Bookings</h5>
                    </div>
                    <a href="<?= APP_URL ?>/user/bookings.php" class="btn btn-outline-gold btn-sm">View All</a>
                </div>

                <?php if ($recentBookings): ?>
                <div class="booking-list booking-list-compact">
                    <?php foreach ($recentBookings as $b):
                        $status = strtolower($b['status'] ?? 'pending');
                        $statusClass = [
                            'confirmed' => 'status-confirmed',
                            'completed' => 'status-completed',
                            'pending' => 'status-pending',
                            'cancelled' => 'status-cancelled',
                        ][$status] ?? 'status-pending';
                    ?>
                    <article class="booking-item">
                        <a href="<?= APP_URL ?>/property.php?id=<?= (int) $b['property_id'] ?>" class="booking-thumb-link">
                            <img src="<?= getPropertyPrimaryImage($b['image'] ?? null) ?>" class="booking-thumb" alt="<?= e($b['property_name']) ?>">
                        </a>
                        <div class="booking-summary">
                            <div class="booking-title-row">
                                <div>
                                    <p class="booking-ref mb-1">Booking #<?= (int) $b['id'] ?></p>
                                    <h6 class="mb-1"><?= e($b['property_name']) ?></h6>
                                    <p class="booking-location mb-0"><i class="bi bi-geo-alt"></i> <?= e($b['district']) ?> &middot; <?= e($b['room_name']) ?></p>
                                </div>
                                <span class="status-badge <?= e($statusClass) ?>"><?= e(ucfirst($status)) ?></span>
                            </div>
                            <div class="booking-meta-row">
                                <span><i class="bi bi-calendar-event"></i> <?= e(date('M j, Y', strtotime($b['check_in']))) ?> - <?= e(date('M j, Y', strtotime($b['check_out']))) ?></span>
                                <strong class="money-text"><?= formatPrice((float) $b['total_amount']) ?></strong>
                            </div>
                        </div>
                        <div class="booking-actions">
                            <a href="<?= APP_URL ?>/booking-confirmation.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-gold">View Details</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-calendar2-plus"></i>
                    <h5>No bookings yet</h5>
                    <p>Explore Sri Lanka's most beautiful stays and reserve your first trip.</p>
                    <a href="<?= APP_URL ?>/properties.php" class="btn btn-gold btn-sm">Explore Stays</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
