<?php
require_once __DIR__ . '/includes/auth.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && validateEmail($email) && $message) {
        $sent = true;
        flash('success', 'Thank you! We will get back to you soon.');
    }
    if (!$sent) {
        flash('danger', 'Please complete the form with a valid email address.');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    flash('danger', 'Your session has expired. Please try again.');
}

$pageTitle = 'Contact';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-5">
            <h1>Contact <span class="text-gold">Us</span></h1>
            <p class="text-muted-light">Have questions about booking or listing your property? We're here to help.</p>
            <p class="mb-2"><i class="bi bi-envelope text-gold me-2"></i> contact@luxurystay.lk</p>
            <p class="mb-2"><i class="bi bi-telephone text-gold me-2"></i> +94 11 234 5678</p>
            <p><i class="bi bi-geo-alt text-gold me-2"></i> 123 Galle Road, Colombo 03, Sri Lanka</p>
        </div>
        <div class="col-lg-7">
            <div class="form-dark">
                <form method="POST" data-loading>
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-gold">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
