<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
$db = getDB();
$id = (int) ($_GET['booking_id'] ?? 0);
$stmt = $db->prepare("SELECT b.*, p.name as property_name, u.name as user_name, u.email FROM bookings b 
    JOIN properties p ON b.property_id = p.id JOIN users u ON b.user_id = u.id WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$booking = $stmt->fetch();
if (!$booking) redirect(APP_URL . '/user/bookings.php');

$pageTitle = 'Email Preview';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <h2 class="text-center mb-4">Booking Confirmation <span class="text-gold">Email</span></h2>
    <div class="email-preview">
        <div style="background:#0d1117;color:#c9a227;padding:20px;text-align:center;">
            <h2 style="margin:0;color:#c9a227;"><?= e(APP_NAME) ?></h2>
            <p style="color:#8b949e;margin:5px 0 0;">Sri Lanka's Premier Accommodation Platform</p>
        </div>
        <div style="padding:30px;">
            <p>Dear <?= e($booking['user_name']) ?>,</p>
            <p>Your booking has been received! Here are your details:</p>
            <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Booking ID</strong></td><td style="padding:8px;border-bottom:1px solid #eee;">#<?= $booking['id'] ?></td></tr>
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Property</strong></td><td style="padding:8px;border-bottom:1px solid #eee;"><?= e($booking['property_name']) ?></td></tr>
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Check-in</strong></td><td style="padding:8px;border-bottom:1px solid #eee;"><?= e($booking['check_in']) ?></td></tr>
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Check-out</strong></td><td style="padding:8px;border-bottom:1px solid #eee;"><?= e($booking['check_out']) ?></td></tr>
                <tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Total</strong></td><td style="padding:8px;border-bottom:1px solid #eee;color:#c9a227;font-weight:bold;"><?= formatPrice($booking['total_amount']) ?></td></tr>
                <tr><td style="padding:8px;"><strong>Status</strong></td><td style="padding:8px;"><?= ucfirst($booking['status']) ?></td></tr>
            </table>
            <p>Thank you for choosing LuxuryStay. We wish you a wonderful stay in Sri Lanka!</p>
            
        </div>
        <div style="background:#f5f5f5;padding:15px;text-align:center;font-size:12px;color:#666;">
            &copy; <?= date('Y') ?> LuxuryStay · contact@luxurystay.lk · Colombo, Sri Lanka
        </div>
    </div>
    <p class="text-center mt-3"><a href="<?= APP_URL ?>/user/bookings.php" class="btn btn-gold">Back to Bookings</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
