<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');
$db = getDB();
$ownerId = $_SESSION['owner_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $bid = (int) $_POST['booking_id'];
    $action = $_POST['action'] ?? '';
    $chk = $db->prepare("SELECT b.id, b.user_id FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ? AND p.owner_id = ?");
    $chk->execute([$bid, $ownerId]);
    $booking = $chk->fetch();
    if ($booking && in_array($action, ['confirm', 'reject', 'complete'])) {
        $status = $action === 'confirm' ? 'confirmed' : ($action === 'reject' ? 'cancelled' : 'completed');
        $db->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $bid]);
        createNotification($db, 'Booking ' . ucfirst($status), "Your booking #{$bid} has been {$status}.", 'booking', APP_URL . '/user/bookings.php', $booking['user_id']);
        flash('success', 'Booking ' . $status . '.');
    }
    redirect(APP_URL . '/owner/bookings.php');
}

$bookings = $db->prepare("SELECT b.*, p.name as property_name, r.name as room_name, u.name as user_name, u.email 
    FROM bookings b JOIN properties p ON b.property_id = p.id JOIN rooms r ON b.room_id = r.id JOIN users u ON b.user_id = u.id 
    WHERE p.owner_id = ? ORDER BY b.created_at DESC");
$bookings->execute([$ownerId]);
$bookings = $bookings->fetchAll();

$pageTitle = 'Bookings';
$dashRole = 'owner';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Booking <span class="text-gold">Requests</span></h1>
            <div class="luxury-card p-4 table-responsive">
                <table class="table table-dark">
                    <thead><tr><th>#</th><th>Guest</th><th>Property</th><th>Room</th><th>Dates</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= $b['id'] ?></td>
                            <td><?= e($b['user_name']) ?><br><small class="text-muted"><?= e($b['email']) ?></small></td>
                            <td><?= e($b['property_name']) ?></td>
                            <td><?= e($b['room_name']) ?></td>
                            <td><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td>
                            <td><?= formatPrice($b['total_amount']) ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($b['status']) ?></span></td>
                            <td>
                                <a href="<?= APP_URL ?>/invoice.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-gold mb-1"><i class="bi bi-file-earmark-pdf"></i></a>
                                <?php if ($b['status'] === 'pending' && $b['payment_status'] === 'paid'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <button name="action" value="confirm" class="btn btn-sm btn-success">Accept</button>
                                    <button name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                                <?php elseif ($b['status'] === 'confirmed'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <button name="action" value="complete" class="btn btn-sm btn-gold">Complete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
