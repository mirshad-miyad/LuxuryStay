<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $id = (int) $_POST['review_id'];
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $db->prepare("SELECT property_id FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        $pid = (int) $stmt->fetchColumn();
        $db->prepare("UPDATE reviews SET status = ? WHERE id = ?")->execute([$status, $id]);
        if ($pid && $status === 'approved') updatePropertyRating($db, $pid);
        flash('success', 'Review ' . $status . '.');
    }
    redirect(APP_URL . '/admin/reviews.php');
}

$reviews = $db->query("SELECT r.*, u.name as user_name, p.name as property_name FROM reviews r 
    JOIN users u ON r.user_id = u.id JOIN properties p ON r.property_id = p.id ORDER BY r.created_at DESC")->fetchAll();

$pageTitle = 'Manage Reviews';
$dashRole = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Manage <span class="text-gold">Reviews</span></h1>
            <div class="luxury-card p-4">
                <?php foreach ($reviews as $r): ?>
                <div class="border-bottom border-secondary py-3 d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <strong><?= e($r['user_name']) ?></strong> on <em><?= e($r['property_name']) ?></em>
                        <span class="rating-stars ms-2"><?php for($i=1;$i<=5;$i++): ?><i class="bi bi-star<?= $i<=$r['rating']?'-fill':'' ?>"></i><?php endfor; ?></span>
                        <p class="text-muted-light small mb-0"><?= e($r['comment']) ?></p>
                        <span class="badge bg-secondary"><?= ucfirst($r['status']) ?></span>
                    </div>
                    <?php if ($r['status'] === 'pending'): ?>
                    <div>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                            <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                            <button name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?><p class="text-muted-light">No reviews.</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
