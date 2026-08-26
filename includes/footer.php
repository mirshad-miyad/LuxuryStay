<footer class="luxury-footer mt-auto">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="text-white"><i class="bi bi-gem"></i> <?= e(APP_NAME) ?></h5>
                <p class="text-muted-light"><?= e(APP_TAGLINE) ?>. Discover hotels, villas, resorts and guest houses across the pearl of the Indian Ocean.</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.x.com" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <h6 class="footer-heading">About</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= APP_URL ?>/properties.php">Accommodations</a></li>
                    <li><a href="<?= APP_URL ?>/about.php">About Us</a></li>
                    <li><a href="<?= APP_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4 col-lg-3">
                <h6 class="footer-heading">Popular Destinations</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= APP_URL ?>/properties.php?district=Colombo">Colombo</a></li>
                    <li><a href="<?= APP_URL ?>/properties.php?district=Kandy">Kandy</a></li>
                    <li><a href="<?= APP_URL ?>/properties.php?district=Galle">Galle</a></li>
                    <li><a href="<?= APP_URL ?>/properties.php?district=Mirissa">Mirissa</a></li>
                </ul>
            </div>
            <div class="col-md-4 col-lg-3">
                <h6 class="footer-heading">Contact</h6>
                <p class="text-muted-light mb-1"><i class="bi bi-envelope me-2"></i>contact@luxurystay.lk</p>
                <p class="text-muted-light mb-1"><i class="bi bi-telephone me-2"></i>+94 11 234 5678</p>
                <p class="text-muted-light mb-1"><i class="bi bi-geo-alt me-2"></i>Colombo, Sri Lanka</p>
                <ul class="list-unstyled footer-links mt-3">
                    <li><a href="<?= APP_URL ?>/privacy.php">Privacy Policy</a></li>
                    <li><a href="<?= APP_URL ?>/terms.php">Terms</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <p class="text-center text-muted-light mb-0 small">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</p>
    </div>
</footer>

<div id="pageLoader" class="page-loader d-none">
    <div class="spinner-border text-gold" role="status"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= $js ?>"></script>
<?php endforeach; endif; ?>
<?php if (!empty($extraInlineJs)): foreach ((array) $extraInlineJs as $script): ?>
<script>
<?= $script ?>
</script>
<?php endforeach; endif; ?>
</body>
</html>
