<?php
require_once __DIR__ . '/includes/auth.php';
requireRole('user');

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT b.*, p.name as property_name, p.address, p.district, r.name as room_name 
    FROM bookings b JOIN properties p ON b.property_id = p.id JOIN rooms r ON b.room_id = r.id 
    WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) redirect(APP_URL . '/user/bookings.php');

$pageTitle = 'Booking Confirmed';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5 text-center">
    <div class="luxury-card p-5 mx-auto" style="max-width:600px;">
        <i class="bi bi-check-circle-fill text-gold display-1"></i>
        <h1 class="mt-3">Booking Confirmed!</h1>
        <p class="text-muted-light">Reference #<?= $booking['id'] ?></p>
        <hr class="border-secondary">
        <p><strong><?= e($booking['property_name']) ?></strong></p>
        <p class="text-muted-light"><?= e($booking['room_name']) ?></p>
        <p><?= e($booking['check_in']) ?> → <?= e($booking['check_out']) ?></p>
        <p class="property-price"><?= formatPrice($booking['total_amount']) ?></p>
        <p>Status: <span class="badge bg-warning text-dark"><?= ucfirst($booking['status']) ?></span></p>
        <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
            <a href="<?= APP_URL ?>/user/bookings.php" class="btn btn-gold">My Bookings</a>
            <a href="<?= APP_URL ?>/user/email-preview.php?booking_id=<?= $id ?>" class="btn btn-outline-gold">View Email</a>
            <a href="<?= APP_URL ?>/invoice.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-file-earmark-pdf me-1"></i>Download Invoice</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
