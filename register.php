<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) redirect(APP_URL . '/index.php');

$error = '';
$success = '';
$role = $_GET['role'] ?? 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $company = trim($_POST['company_name'] ?? '');

        if (strlen($name) < 2) $error = 'Name is required.';
        elseif ($password !== $confirm) $error = 'Passwords do not match.';
        else {
            $result = registerUser($name, $email, $phone, $password, $role, $company ?: null);
            if ($result['success']) {
                flash('success', $role === 'owner' ? 'Registration submitted! Await admin approval.' : 'Account created! Please login.');
                redirect(APP_URL . '/login.php');
            }
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-dark">
                <h2 class="text-center mb-4">Create <span class="text-gold">Account</span></h2>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" data-loading>
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Register as</label>
                        <select name="role" id="regRole" class="form-select">
                            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>Customer</option>
                            <option value="owner" <?= $role === 'owner' ? 'selected' : '' ?>>Property Owner</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3" id="companyField" style="<?= $role !== 'owner' ? 'display:none' : '' ?>">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+94 7X XXX XXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">Register</button>
                </form>
                <p class="text-center mt-3 text-muted-light">Already have an account? <a href="<?= APP_URL ?>/login.php" class="text-gold">Login</a></p>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('regRole').addEventListener('change', function() {
    document.getElementById('companyField').style.display = this.value === 'owner' ? 'block' : 'none';
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
