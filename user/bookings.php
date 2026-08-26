<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
$db = getDB();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $bid = (int) ($_POST['booking_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'cancel') {
        $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending','confirmed')")
            ->execute([$bid, $userId]);
        flash('success', 'Booking cancelled.');
    }
    redirect(APP_URL . '/user/bookings.php');
}

$bookings = $db->prepare("SELECT b.*, p.name as property_name, p.district, r.name as room_name,
    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as image
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC");
$bookings->execute([$userId]);
$bookings = $bookings->fetchAll();

$pageTitle = 'My Bookings';
$dashRole = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10 dashboard-content">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
                <div>
                    <span class="section-label">Reservations</span>
                    <h1 class="mb-1">My <span class="text-gold">Bookings</span></h1>
                    <p class="text-muted-light mb-0">All your LuxuryStay reservations in one clean view.</p>
                </div>
                <a href="<?= APP_URL ?>/properties.php" class="btn btn-gold btn-sm"><i class="bi bi-search me-1"></i>Find Stays</a>
            </div>

            <?php if ($bookings): ?>
            <div class="booking-list">
                <?php foreach ($bookings as $b):
                    $status = strtolower($b['status'] ?? 'pending');
                    $paymentStatus = strtolower($b['payment_status'] ?? 'pending');
                    $statusClass = [
                        'confirmed' => 'status-confirmed',
                        'completed' => 'status-completed',
                        'pending' => 'status-pending',
                        'cancelled' => 'status-cancelled',
                    ][$status] ?? 'status-pending';
                    $paymentClass = [
                        'paid' => 'payment-paid',
                        'refunded' => 'payment-refunded',
                        'pending' => 'payment-pending',
                    ][$paymentStatus] ?? 'payment-pending';
                ?>
                <article class="booking-item booking-item-large">
                    <a href="<?= APP_URL ?>/property.php?id=<?= (int) $b['property_id'] ?>" class="booking-thumb-link">
                        <img src="<?= getPropertyPrimaryImage($b['image'] ?? null) ?>" class="booking-thumb booking-thumb-lg" alt="<?= e($b['property_name']) ?>">
                    </a>
                    <div class="booking-summary">
                        <div class="booking-title-row">
                            <div>
                                <p class="booking-ref mb-1">Booking #<?= (int) $b['id'] ?></p>
                                <h5 class="mb-1"><?= e($b['property_name']) ?></h5>
                                <p class="booking-location mb-0"><i class="bi bi-geo-alt"></i> <?= e($b['district']) ?> &middot; <?= e($b['room_name']) ?></p>
                            </div>
                            <span class="status-badge <?= e($statusClass) ?>"><?= e(ucfirst($status)) ?></span>
                        </div>

                        <div class="booking-detail-grid">
                            <div>
                                <span>Check-in</span>
                                <strong><?= e(date('M j, Y', strtotime($b['check_in']))) ?></strong>
                            </div>
                            <div>
                                <span>Check-out</span>
                                <strong><?= e(date('M j, Y', strtotime($b['check_out']))) ?></strong>
                            </div>
                            <div>
                                <span>Total</span>
                                <strong class="money-text"><?= formatPrice((float) $b['total_amount']) ?></strong>
                            </div>
                            <div>
                                <span>Payment</span>
                                <strong><span class="payment-pill <?= e($paymentClass) ?>"><?= e(ucfirst($paymentStatus)) ?></span></strong>
                            </div>
                        </div>
                    </div>

                    <div class="booking-actions">
                        <a href="<?= APP_URL ?>/booking-confirmation.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-gold">View Details</a>
                        <a href="<?= APP_URL ?>/invoice.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf me-1"></i>Invoice</a>
                        <?php if ($paymentStatus === 'pending' && $status !== 'cancelled'): ?>
                        <a href="<?= APP_URL ?>/payment.php?booking_id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-gold">Pay Now</a>
                        <?php endif; ?>
                        <?php if (in_array($status, ['pending','confirmed'], true)): ?>
                        <form method="POST" class="booking-action-form" onsubmit="return confirm('Cancel booking?')">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($status === 'completed'): ?>
                        <a href="<?= APP_URL ?>/user/reviews.php?booking_id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-soft-blue">Review</a>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="content-card p-4">
                <div class="empty-state">
                    <i class="bi bi-suitcase2"></i>
                    <h5>No bookings found</h5>
                    <p>Your future reservations will appear here after checkout.</p>
                    <a href="<?= APP_URL ?>/properties.php" class="btn btn-gold btn-sm">Browse Properties</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
