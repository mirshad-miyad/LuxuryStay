<?php
require_once __DIR__ . '/includes/auth.php';
requireRole('user');

$db = getDB();
ensureOwnerFeatureSchema($db);
$userId = $_SESSION['user_id'];
$roomId = (int) ($_GET['room_id'] ?? $_POST['room_id'] ?? 0);
$checkIn = $_GET['check_in'] ?? $_POST['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? $_POST['check_out'] ?? '';
$guests = (int) ($_GET['guests'] ?? $_POST['guests'] ?? 1);

$stmt = $db->prepare("SELECT r.*, p.id as property_id, p.name as property_name, p.district
    FROM rooms r
    JOIN properties p ON r.property_id = p.id
    WHERE r.id = ? AND r.status = 'active' AND p.status = 'approved' AND p.is_active = 1 AND p.deleted_at IS NULL");
$stmt->execute([$roomId]);
$room = $stmt->fetch();

if (!$room) {
    flash('danger', 'Room not found.');
    redirect(APP_URL . '/properties.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $checkIn = $_POST['check_in'] ?? '';
    $checkOut = $_POST['check_out'] ?? '';
    $guests = (int) ($_POST['guests'] ?? 1);
    $requests = trim($_POST['special_requests'] ?? '');

    if (!$checkIn || !$checkOut || $checkIn >= $checkOut || $checkIn < date('Y-m-d')) {
        $error = 'Please select valid check-in and check-out dates.';
    } elseif ($guests > $room['max_guests']) {
        $error = 'Maximum ' . $room['max_guests'] . ' guests allowed for this room.';
    } elseif (!isRoomAvailable($db, $roomId, $checkIn, $checkOut)) {
        $error = 'Room is not available for selected dates.';
    } else {
        $nights = nightsBetween($checkIn, $checkOut);
        $total = calculateRoomStayTotal($db, $roomId, $checkIn, $checkOut);

        $stmt = $db->prepare("INSERT INTO bookings (user_id, room_id, property_id, check_in, check_out, guests, total_amount, status, special_requests) VALUES (?,?,?,?,?,?,?,'pending',?)");
        $stmt->execute([$userId, $roomId, $room['property_id'], $checkIn, $checkOut, $guests, $total, $requests]);
        $bookingId = (int) $db->lastInsertId();

        $ownerStmt = $db->prepare("SELECT owner_id FROM properties WHERE id = ?");
        $ownerStmt->execute([$room['property_id']]);
        $ownerId = (int) $ownerStmt->fetchColumn();
        createNotification($db, 'New Booking Request', "Booking #{$bookingId} for {$room['property_name']}", 'info', APP_URL . '/admin/bookings.php', null, null, 1);
        createNotification($db, 'New Booking', "You have a new booking request for {$room['property_name']}", 'booking', APP_URL . '/owner/bookings.php', null, $ownerId);
        createNotification($db, 'Booking Created', "Your booking #{$bookingId} is awaiting confirmation.", 'info', APP_URL . '/user/bookings.php', $userId);

        redirect(APP_URL . '/payment.php?booking_id=' . $bookingId);
    }
}

$nights = ($checkIn && $checkOut) ? nightsBetween($checkIn, $checkOut) : 0;
$nightPrices = ($checkIn && $checkOut) ? getRoomNightPrices($db, $roomId, $checkIn, $checkOut) : [];
$total = array_reduce($nightPrices, function (float $sum, array $night): float {
    return $sum + (float) $night['price'];
}, 0.0);

$pageTitle = 'Book Room';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Complete Your <span class="text-gold">Booking</span></h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="form-dark">
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" data-loading>
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="room_id" value="<?= $roomId ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Check-in</label>
                            <input type="date" name="check_in" class="form-control" required value="<?= e($checkIn) ?>" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out</label>
                            <input type="date" name="check_out" class="form-control" required value="<?= e($checkOut) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guests</label>
                            <input type="number" name="guests" class="form-control" min="1" max="<?= $room['max_guests'] ?>" value="<?= $guests ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Special Requests</label>
                            <textarea name="special_requests" class="form-control" rows="3" placeholder="Early check-in, dietary requirements..."></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gold mt-4">Proceed to Payment</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="luxury-card p-4">
                <h5 class="text-gold">Booking Summary</h5>
                <hr class="border-secondary">
                <p class="mb-1"><strong><?= e($room['property_name']) ?></strong></p>
                <p class="text-muted-light small"><?= e($room['name']) ?> · <?= e($room['district']) ?></p>
                <p class="mb-1">Price: <?= formatPrice($room['price_per_night']) ?> / night</p>
                <p class="mb-1">Nights: <span id="nightCount"><?= $nights ?></span></p>
                <hr class="border-secondary">
                <h4 class="property-price">Total: <span id="totalAmount"><?= formatPrice($total) ?></span></h4>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
