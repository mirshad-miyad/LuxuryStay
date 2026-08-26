<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $id = (int) $_POST['owner_id'];
    $status = $_POST['status'] ?? 'active';
    $db->prepare("UPDATE owners SET status = ? WHERE id = ?")->execute([$status, $id]);
    createNotification($db, 'Account Updated', 'Your owner account status has been updated to ' . $status, 'info', APP_URL . '/owner/dashboard.php', null, $id);
    flash('success', 'Owner updated.');
    redirect(APP_URL . '/admin/owners.php');
}

$owners = $db->query("SELECT o.*, (SELECT COUNT(*) FROM properties WHERE owner_id = o.id) as prop_count FROM owners o ORDER BY o.created_at DESC")->fetchAll();
$pageTitle = 'Manage Owners';
$dashRole = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Manage <span class="text-gold">Owners</span></h1>
            <div class="luxury-card p-4 table-responsive">
                <table class="table table-dark">
                    <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Properties</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($owners as $o): ?>
                        <tr>
                            <td><?= e($o['name']) ?></td>
                            <td><?= e($o['email']) ?></td>
                            <td><?= e($o['company_name']) ?></td>
                            <td><?= $o['prop_count'] ?></td>
                            <td><span class="badge bg-<?= $o['status']==='active'?'success':($o['status']==='pending'?'warning':'danger') ?>"><?= ucfirst($o['status']) ?></span></td>
                            <td>
                                <?php if ($o['status'] !== 'active'): ?>
                                <form method="POST" class="d-inline"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="owner_id" value="<?= $o['id'] ?>"><button name="status" value="active" class="btn btn-sm btn-success">Approve</button></form>
                                <?php endif; ?>
                                <?php if ($o['status'] === 'active'): ?>
                                <form method="POST" class="d-inline"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="owner_id" value="<?= $o['id'] ?>"><button name="status" value="suspended" class="btn btn-sm btn-danger">Suspend</button></form>
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
