<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('owner');

$db = getDB();
ensureOwnerProfileSchema($db);
$ownerId = (int) $_SESSION['owner_id'];

$stmt = $db->prepare('SELECT * FROM owners WHERE id = ?');
$stmt->execute([$ownerId]);
$owner = $stmt->fetch();

if (!$owner) {
    logoutUser();
    flash('danger', 'Your owner account could not be found. Please log in again.');
    redirect(APP_URL . '/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        flash('danger', 'Your session has expired. Please try again.');
        redirect(APP_URL . '/owner/profile.php');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $businessDescription = trim($_POST['business_description'] ?? '');
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($name === '') {
        flash('danger', 'Name is required.');
        redirect(APP_URL . '/owner/profile.php');
    }
    if (!validateEmail($email)) {
        flash('danger', 'Please enter a valid email address.');
        redirect(APP_URL . '/owner/profile.php');
    }

    $emailExists = $db->prepare('SELECT id FROM owners WHERE email = ? AND id != ? LIMIT 1');
    $emailExists->execute([$email, $ownerId]);
    if ($emailExists->fetch()) {
        flash('danger', 'That email address is already in use.');
        redirect(APP_URL . '/owner/profile.php');
    }

    $profileImage = $owner['profile_image'] ?? null;
    if (isset($_FILES['profile_image']) && (int) ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadError = validateUploadedImage($_FILES['profile_image']);
        if ($uploadError !== null) {
            flash('danger', $uploadError);
            redirect(APP_URL . '/owner/profile.php');
        }

        $ownerUploadPath = UPLOAD_PATH . 'profile' . DIRECTORY_SEPARATOR . 'owners' . DIRECTORY_SEPARATOR . $ownerId . DIRECTORY_SEPARATOR;
        $uploadedImage = uploadImage($_FILES['profile_image'], $ownerUploadPath);
        if (!$uploadedImage) {
            flash('danger', 'Profile photo could not be uploaded.');
            redirect(APP_URL . '/owner/profile.php');
        }
        $profileImage = $uploadedImage;
    }

    $passwordHash = $owner['password'];
    if ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            flash('danger', 'Complete all password fields to change your password.');
            redirect(APP_URL . '/owner/profile.php');
        }
        if (!password_verify($currentPassword, $owner['password'])) {
            flash('danger', 'Your current password is incorrect.');
            redirect(APP_URL . '/owner/profile.php');
        }
        if (strlen($newPassword) < 8) {
            flash('danger', 'Your new password must be at least 8 characters.');
            redirect(APP_URL . '/owner/profile.php');
        }
        if ($newPassword !== $confirmPassword) {
            flash('danger', 'The new password confirmation does not match.');
            redirect(APP_URL . '/owner/profile.php');
        }
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $db->prepare('UPDATE owners SET name = ?, email = ?, phone = ?, address = ?, profile_image = ?, company_name = ?, business_description = ?, password = ? WHERE id = ?')
        ->execute([$name, $email, $phone, $address, $profileImage, $companyName, $businessDescription, $passwordHash, $ownerId]);
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    flash('success', 'Owner profile updated.');
    redirect(APP_URL . '/owner/profile.php');
}

$profileImageUrl = '';
if (!empty($owner['profile_image'])) {
    $profilePath = ROOT_PATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $owner['profile_image']);
    if (file_exists($profilePath)) {
        $profileImageUrl = APP_URL . '/' . str_replace('\\', '/', $owner['profile_image']);
    }
}
$profileInitial = strtoupper(substr($owner['name'] ?? 'O', 0, 1));

$pageTitle = 'Owner Profile';
$dashRole = 'owner';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid dashboard-wrap py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/dashboard-sidebar.php'; ?>
        <main class="col-lg-10 dashboard-content">
            <div class="dashboard-heading mb-4">
                <span class="section-label">Account</span>
                <h1 class="mb-1">Owner <span class="text-gold">Profile</span></h1>
                <p class="text-muted-light mb-0">Keep your owner and business details up to date.</p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="profile-layout">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <aside class="profile-photo-panel">
                    <label for="profileImageInput" class="profile-avatar">
                        <img id="profilePreview" src="<?= e($profileImageUrl ?: APP_URL . '/assets/images/default-avatar.svg') ?>" alt="<?= e($owner['name']) ?>" class="<?= $profileImageUrl ? '' : 'd-none' ?>">
                        <span id="profileInitials" class="profile-initials <?= $profileImageUrl ? 'd-none' : '' ?>"><?= e($profileInitial) ?></span>
                        <span class="profile-avatar-overlay"><i class="bi bi-camera"></i></span>
                    </label>
                    <input type="file" id="profileImageInput" name="profile_image" class="visually-hidden" accept="image/jpeg,image/png,image/webp" data-profile-image-input>
                    <label for="profileImageInput" class="btn btn-outline-gold btn-sm mt-3"><i class="bi bi-upload me-1"></i>Upload New Photo</label>
                    <p class="profile-photo-note">JPG, PNG, or WebP up to 2MB.</p>
                </aside>

                <section class="profile-form-panel">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="name">Name</label><input id="name" name="name" class="form-control" value="<?= e($owner['name']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" type="email" name="email" class="form-control" value="<?= e($owner['email']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label" for="phone">Phone Number</label><input id="phone" type="tel" name="phone" class="form-control" value="<?= e($owner['phone'] ?? '') ?>" placeholder="+94 7X XXX XXXX"></div>
                        <div class="col-md-6"><label class="form-label" for="company_name">Business / Company Name</label><input id="company_name" name="company_name" class="form-control" value="<?= e($owner['company_name'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label" for="address">Address</label><textarea id="address" name="address" class="form-control" rows="3"><?= e($owner['address'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label" for="business_description">Business Description</label><textarea id="business_description" name="business_description" class="form-control" rows="3" placeholder="Tell guests about your business"><?= e($owner['business_description'] ?? '') ?></textarea></div>
                        <div class="col-12"><hr class="my-2"><p class="text-muted-light mb-0">Leave these fields empty to keep your current password.</p></div>
                        <div class="col-md-4"><label class="form-label" for="current_password">Current Password</label><input id="current_password" type="password" name="current_password" class="form-control" autocomplete="current-password"></div>
                        <div class="col-md-4"><label class="form-label" for="new_password">New Password</label><input id="new_password" type="password" name="new_password" class="form-control" autocomplete="new-password" minlength="8"></div>
                        <div class="col-md-4"><label class="form-label" for="confirm_password">Confirm New Password</label><input id="confirm_password" type="password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8"></div>
                        <div class="col-12 d-flex justify-content-end"><button class="btn btn-gold btn-save-profile" type="submit"><i class="bi bi-check2-circle me-1"></i>Save Changes</button></div>
                    </div>
                </section>
            </form>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
