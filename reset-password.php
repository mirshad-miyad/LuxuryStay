<?php
require_once __DIR__ . '/includes/auth.php';

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$role = $_GET['role'] ?? 'user';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm || strlen($password) < 8) {
        $error = 'Passwords must match and be at least 8 characters.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE email = ? AND role = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, $role]);
        $reset = $stmt->fetch();

        if ($reset && password_verify($token, $reset['token'])) {
            $tables = ['user' => 'users', 'owner' => 'owners', 'admin' => 'admins'];
            $table = $tables[$role] ?? 'users';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("UPDATE {$table} SET password = ? WHERE email = ?")->execute([$hash, $email]);
            $db->prepare("DELETE FROM password_resets WHERE email = ? AND role = ?")->execute([$email, $role]);
            flash('success', 'Password updated! Please login.');
            redirect(APP_URL . '/login.php');
        }
        $error = 'Invalid or expired reset link.';
    }
}

$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-dark">
                <h2 class="text-center mb-4">New <span class="text-gold">Password</span></h2>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <input type="hidden" name="email" value="<?= e($email) ?>">
                    <input type="hidden" name="role" value="<?= e($role) ?>">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
