<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $id = (int) $_POST['user_id'];
    $status = $_POST['status'] ?? 'active';
    $db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$status, $id]);
    flash('success', 'User updated.');
    redirect(APP_URL . '/admin/users.php');
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Manage Users';
$dashRole = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10">
            <h1 class="mb-4">Manage <span class="text-gold">Users</span></h1>
            <div class="luxury-card p-4 table-responsive">
                <table class="table table-dark">
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= e($u['name']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['phone']) ?></td>
                            <td><span class="badge bg-<?= $u['status']==='active'?'success':'danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button name="status" value="<?= $u['status']==='active'?'suspended':'active' ?>" class="btn btn-sm btn-outline-warning">
                                        <?= $u['status']==='active'?'Suspend':'Activate' ?>
                                    </button>
                                </form>
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
