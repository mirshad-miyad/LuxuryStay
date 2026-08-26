<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h1 class="display-4">About <span class="text-gold">LuxuryStay</span></h1>
                <p class="lead text-muted-light">LuxuryStay is Sri Lanka's premier online accommodation booking platform, connecting travelers with exceptional hotels, villas, resorts, and guest houses across the island.</p>
                <p class="text-muted-light">From the bustling streets of Colombo to the serene beaches of Mirissa and the misty hills of Ella, we help you discover and book the perfect stay with confidence.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> Curated luxury accommodations</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> Secure booking & LKR pricing</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> Verified property owners</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-gold me-2"></i> 24/7 customer support</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="luxury-card p-4">
                    <h4 class="text-gold">Our Mission</h4>
                    <p class="text-muted-light">To elevate Sri Lankan tourism by providing a world-class digital platform that showcases the island's hospitality heritage while empowering local property owners.</p>
                    <hr class="border-secondary">
                    <div class="row text-center g-3">
                        <div class="col-4"><div class="stat-value">500+</div><small class="text-muted-light">Properties</small></div>
                        <div class="col-4"><div class="stat-value">25</div><small class="text-muted-light">Districts</small></div>
                        <div class="col-4"><div class="stat-value">10K+</div><small class="text-muted-light">Happy Guests</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
