<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $role = getUserRole();
    redirect(APP_URL . '/' . ($role === 'admin' ? 'admin' : ($role === 'owner' ? 'owner' : 'user')) . '/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        $user = authenticate($email, $password, $role);
        if ($user) {
            loginUser($user, $role);
            flash('success', 'Welcome back, ' . $user['name'] . '!');
            if ($role === 'admin') redirect(APP_URL . '/admin/dashboard.php');
            if ($role === 'owner') redirect(APP_URL . '/owner/dashboard.php');
            redirect(APP_URL . '/user/dashboard.php');
        }
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-dark">
                <h2 class="text-center mb-4">Welcome <span class="text-gold">Back</span></h2>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" data-loading>
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Login as</label>
                        <select name="role" class="form-select" required>
                            <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>Customer</option>
                            <option value="owner" <?= ($_POST['role'] ?? '') === 'owner' ? 'selected' : '' ?>>Property Owner</option>
                            <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 text-end">
                        <a href="<?= APP_URL ?>/forgot-password.php" class="text-gold small">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">Login</button>
                </form>
                <p class="text-center mt-3 text-muted-light">Don't have an account? <a href="<?= APP_URL ?>/register.php" class="text-gold">Register</a></p>
                
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
