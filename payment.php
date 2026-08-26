<?php
/**
 * Payment simulation - educational/demo purposes
 */
require_once __DIR__ . '/includes/auth.php';
requireRole('user');

$db = getDB();
$userId = $_SESSION['user_id'];
$bookingId = (int) ($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);

$stmt = $db->prepare("SELECT b.*, p.name as property_name, r.name as room_name 
    FROM bookings b 
    JOIN properties p ON b.property_id = p.id 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking) {
    flash('danger', 'Booking not found.');
    redirect(APP_URL . '/user/bookings.php');
}

if ($booking['payment_status'] === 'paid') {
    redirect(APP_URL . '/booking-confirmation.php?id=' . $bookingId);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $method = $_POST['payment_method'] ?? '';
    $cardName = trim($_POST['card_name'] ?? '');

    if (!in_array($method, ['card', 'bank', 'cash'], true)) {
        $error = 'Please select a payment method.';
    } else {
        // Simulate successful payment
        $db->prepare("UPDATE bookings SET payment_status = 'paid', payment_method = ?, status = 'pending' WHERE id = ?")
            ->execute([$method, $bookingId]);

        createNotification($db, 'Payment Received', "Payment for booking #{$bookingId} received.", 'success', APP_URL . '/user/bookings.php', $userId);

        flash('success', 'Payment successful! Awaiting owner confirmation.');
        redirect(APP_URL . '/booking-confirmation.php?id=' . $bookingId);
    }
}

$pageTitle = 'Payment';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="form-dark">
                <h2 class="text-center mb-4">Secure <span class="text-gold">Payment</span></h2>
                <div class="luxury-card p-3 mb-4">
                    <p class="mb-1"><strong><?= e($booking['property_name']) ?></strong> - <?= e($booking['room_name']) ?></p>
                    <p class="text-muted-light small"><?= e($booking['check_in']) ?> to <?= e($booking['check_out']) ?> · <?= (int)$booking['guests'] ?> guests</p>
                    <h4 class="property-price mb-0"><?= formatPrice($booking['total_amount']) ?></h4>
                </div>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" data-loading>
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="card">Credit / Debit Card</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cash">Pay at Property</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cardholder Name (demo)</label>
                        <input type="text" name="card_name" class="form-control" placeholder="Name on card">
                    </div>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> This is a demo payment system. No real charges will be made.
                    </div>
                    <button type="submit" class="btn btn-gold w-100">Pay <?= formatPrice($booking['total_amount']) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
