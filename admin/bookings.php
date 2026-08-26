<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $id = (int) $_POST['booking_id'];
    $status = $_POST['status'] ?? '';
    if (in_array($status, BOOKING_STATUSES)) {
        $db->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash('success', 'Booking updated.');
    }
    redirect(APP_URL . '/admin/bookings.php');
}

$bookings = $db->query("SELECT b.*, p.name as property_name, u.name as user_name, r.name as room_name 
    FROM bookings b JOIN properties p ON b.property_id = p.id JOIN users u ON b.user_id = u.id JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.created_at DESC")->fetchAll();

$pageTitle = 'Manage Bookings';
$dashRole = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Manage <span class="text-gold">Bookings</span></h1>
            <div class="luxury-card p-4 table-responsive">
                <table class="table table-dark table-sm">
                    <thead><tr><th>#</th><th>User</th><th>Property</th><th>Dates</th><th>Total</th><th>Payment</th><th>Status</th><th>Change</th><th>Invoice</th></tr></thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= $b['id'] ?></td>
                            <td><?= e($b['user_name']) ?></td>
                            <td><?= e($b['property_name']) ?> / <?= e($b['room_name']) ?></td>
                            <td><?= e($b['check_in']) ?> - <?= e($b['check_out']) ?></td>
                            <td><?= formatPrice($b['total_amount']) ?></td>
                            <td><?= ucfirst($b['payment_status']) ?></td>
                            <td><?= ucfirst($b['status']) ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width:auto;">
                                        <?php foreach (BOOKING_STATUSES as $s): ?><option value="<?= $s ?>" <?= $b['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-gold">Save</button>
                                </form>
                            </td>
                            <td><a href="<?= APP_URL ?>/invoice.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-gold"><i class="bi bi-file-earmark-pdf"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
