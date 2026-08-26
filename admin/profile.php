<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$db = getDB();
ensureAdminProfileSchema($db);
$adminId = (int) $_SESSION['admin_id'];

$stmt = $db->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    logoutUser();
    flash('danger', 'Your administrator account could not be found. Please log in again.');
    redirect(APP_URL . '/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        flash('danger', 'Your session has expired. Please try again.');
        redirect(APP_URL . '/admin/profile.php');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($name === '') {
        flash('danger', 'Name is required.');
        redirect(APP_URL . '/admin/profile.php');
    }
    if (!validateEmail($email)) {
        flash('danger', 'Please enter a valid email address.');
        redirect(APP_URL . '/admin/profile.php');
    }

    $emailExists = $db->prepare('SELECT id FROM admins WHERE email = ? AND id != ? LIMIT 1');
    $emailExists->execute([$email, $adminId]);
    if ($emailExists->fetch()) {
        flash('danger', 'That email address is already in use.');
        redirect(APP_URL . '/admin/profile.php');
    }

    $passwordHash = $admin['password'];
    if ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            flash('danger', 'Complete all password fields to change your password.');
            redirect(APP_URL . '/admin/profile.php');
        }
        if (!password_verify($currentPassword, $admin['password'])) {
            flash('danger', 'Your current password is incorrect.');
            redirect(APP_URL . '/admin/profile.php');
        }
        if (strlen($newPassword) < 8) {
            flash('danger', 'Your new password must be at least 8 characters.');
            redirect(APP_URL . '/admin/profile.php');
        }
        if ($newPassword !== $confirmPassword) {
            flash('danger', 'The new password confirmation does not match.');
            redirect(APP_URL . '/admin/profile.php');
        }
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $db->prepare('UPDATE admins SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?')
        ->execute([$name, $email, $phone, $address, $passwordHash, $adminId]);
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    flash('success', 'Admin profile updated.');
    redirect(APP_URL . '/admin/profile.php');
}

$pageTitle = 'Admin Profile';
$dashRole = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <main class="col-lg-10 dashboard-content">
            <div class="dashboard-heading mb-4">
                <span class="section-label">Account</span>
                <h1 class="mb-1">Admin <span class="text-gold">Profile</span></h1>
                <p class="text-muted-light mb-0">Update your administrator details and password.</p>
            </div>

            <form method="POST" class="profile-form-panel mx-0" style="max-width: 900px;">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Name</label>
                        <input id="name" name="name" class="form-control" value="<?= e($admin['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" type="email" name="email" class="form-control" value="<?= e($admin['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone number</label>
                        <input id="phone" type="tel" name="phone" class="form-control" value="<?= e($admin['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="address">Address</label>
                        <input id="address" name="address" class="form-control" value="<?= e($admin['address'] ?? '') ?>">
                    </div>
                    <div class="col-12"><hr class="my-2"><p class="text-muted-light mb-0">Leave these fields empty to keep your current password.</p></div>
                    <div class="col-md-4">
                        <label class="form-label" for="current_password">Current password</label>
                        <input id="current_password" type="password" name="current_password" class="form-control" autocomplete="current-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="new_password">New password</label>
                        <input id="new_password" type="password" name="new_password" class="form-control" autocomplete="new-password" minlength="8">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="confirm_password">Confirm new password</label>
                        <input id="confirm_password" type="password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8">
                    </div>
                    <div class="col-12 d-flex justify-content-end"><button class="btn btn-gold" type="submit"><i class="bi bi-check2-circle me-1"></i>Save Changes</button></div>
                </div>
            </form>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
