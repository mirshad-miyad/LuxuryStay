<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
$db = getDB();
ensureUserProfileSchema($db);
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $name = trim($firstName . ' ' . $lastName);

    if ($firstName === '') {
        flash('danger', 'First name is required.');
        redirect(APP_URL . '/user/profile.php');
    }

    if (!validateEmail($email)) {
        flash('danger', 'Please enter a valid email address.');
        redirect(APP_URL . '/user/profile.php');
    }

    $emailExists = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
    $emailExists->execute([$email, $userId]);
    if ((int) $emailExists->fetchColumn() > 0) {
        flash('danger', 'That email address is already in use.');
        redirect(APP_URL . '/user/profile.php');
    }

    $profileImage = $user['profile_image'] ?? null;
    if (isset($_FILES['profile_image']) && (int) ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadError = validateUploadedImage($_FILES['profile_image']);
        if ($uploadError !== null) {
            flash('danger', $uploadError);
            redirect(APP_URL . '/user/profile.php');
        }

        $uploadedImage = uploadImage($_FILES['profile_image'], USER_UPLOAD . $userId . DIRECTORY_SEPARATOR);
        if (!$uploadedImage) {
            flash('danger', 'Profile photo could not be uploaded.');
            redirect(APP_URL . '/user/profile.php');
        }
        $profileImage = $uploadedImage;
    }

    $passwordHash = $user['password'];
    if ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            flash('danger', 'Please complete all password fields.');
            redirect(APP_URL . '/user/profile.php');
        }
        if (!password_verify($currentPassword, $user['password'])) {
            flash('danger', 'Current password is incorrect.');
            redirect(APP_URL . '/user/profile.php');
        }
        if (strlen($newPassword) < 8) {
            flash('danger', 'New password must be at least 8 characters.');
            redirect(APP_URL . '/user/profile.php');
        }
        if ($newPassword !== $confirmPassword) {
            flash('danger', 'New password confirmation does not match.');
            redirect(APP_URL . '/user/profile.php');
        }
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, profile_image = ?, password = ? WHERE id = ?")
        ->execute([$name, $email, $phone, $address, $profileImage, $passwordHash, $userId]);
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    flash('success', 'Profile updated.');
    redirect(APP_URL . '/user/profile.php');
}

$nameParts = preg_split('/\s+/', trim($user['name'] ?? ''), 2);
$firstName = $nameParts[0] ?? '';
$lastName = $nameParts[1] ?? '';
$profileImageUrl = '';
if (!empty($user['profile_image'])) {
    $profilePath = ROOT_PATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $user['profile_image']);
    if (file_exists($profilePath)) {
        $profileImageUrl = APP_URL . '/' . str_replace('\\', '/', $user['profile_image']);
    }
}
$profileInitial = strtoupper(substr($user['name'] ?? 'U', 0, 1));

$pageTitle = 'Profile';
$dashRole = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <div class="col-lg-10 dashboard-content">
            <div class="dashboard-heading mb-4">
                <span class="section-label">Account</span>
                <h1 class="mb-1">My <span class="text-gold">Profile</span></h1>
                <p class="text-muted-light mb-0">Keep your guest details ready for faster bookings.</p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="profile-layout">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">

                <aside class="profile-photo-panel">
                    <label for="profileImageInput" class="profile-avatar">
                        <img
                            id="profilePreview"
                            src="<?= e($profileImageUrl ?: APP_URL . '/assets/images/default-avatar.svg') ?>"
                            alt="<?= e($user['name']) ?>"
                            class="<?= $profileImageUrl ? '' : 'd-none' ?>"
                        >
                        <span id="profileInitials" class="profile-initials <?= $profileImageUrl ? 'd-none' : '' ?>"><?= e($profileInitial) ?></span>
                        <span class="profile-avatar-overlay"><i class="bi bi-camera"></i></span>
                    </label>
                    <input type="file" id="profileImageInput" name="profile_image" class="visually-hidden" accept="image/jpeg,image/png,image/webp" data-profile-image-input>
                    <label for="profileImageInput" class="btn btn-outline-gold btn-sm mt-3"><i class="bi bi-upload me-1"></i>Upload New Photo</label>
                    <p class="profile-photo-note">JPG, PNG, or WebP up to 2MB.</p>
                </aside>

                <section class="profile-form-panel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= e($firstName) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= e($lastName) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+94 7X XXX XXXX">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="4" placeholder="Street address, city, district"><?= e($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-gold btn-save-profile"><i class="bi bi-check2-circle me-1"></i>Save Changes</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
