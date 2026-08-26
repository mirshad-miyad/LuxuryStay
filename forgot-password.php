<?php
require_once __DIR__ . '/includes/auth.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';

    if (!validateEmail($email)) {
        $error = 'Please enter a valid email.';
    } else {
        $token = generateToken();
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $db = getDB();
        $db->prepare("DELETE FROM password_resets WHERE email = ? AND role = ?")->execute([$email, $role]);
        $db->prepare("INSERT INTO password_resets (email, token, role, expires_at) VALUES (?,?,?,?)")
            ->execute([$email, password_hash($token, PASSWORD_DEFAULT), $role, $expires]);

        $resetLink = APP_URL . '/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($email) . '&role=' . $role;
        $message = 'If the email exists, a reset link has been generated. For demo: <a href="' . e($resetLink) . '" class="text-gold">Reset Password</a>';
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-dark">
                <h2 class="text-center mb-4">Reset <span class="text-gold">Password</span></h2>
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Account Type</label>
                        <select name="role" class="form-select">
                            <option value="user">Customer</option>
                            <option value="owner">Owner</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">Send Reset Link</button>
                </form>
                <p class="text-center mt-3"><a href="<?= APP_URL ?>/login.php" class="text-gold">Back to Login</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
