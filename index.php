<?php
/**
 * LuxuryStay Homepage
 */
require_once __DIR__ . '/includes/auth.php';
$db = getDB();

$pageTitle = 'Discover Sri Lanka';
$bodyClass = 'home-page';
$navClass = 'navbar-dark';

$hasPropertiesTable = dbTableExists($db, 'properties');
$hasPropertyImagesTable = dbTableExists($db, 'property_images');
$hasRoomsTable = dbTableExists($db, 'rooms');
$hasOffersTable = dbTableExists($db, 'offers');
$hasFeaturedDestinationsTable = dbTableExists($db, 'featured_destinations');

// Featured properties
$featured = [];
if ($hasPropertiesTable && $hasPropertyImagesTable && $hasRoomsTable) {
    $featured = $db->query("SELECT p.*, 
        (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as image,
        (SELECT MIN(price_per_night) FROM rooms WHERE property_id = p.id AND status = 'active') as min_price
        FROM properties p
        WHERE p.status = 'approved' AND (p.featured = 1 OR p.id IN (19, 11))
        ORDER BY (p.id IN (19, 11)) DESC, p.avg_rating DESC LIMIT 8")->fetchAll();
}

// Destinations
$destinations = [];
if ($hasFeaturedDestinationsTable) {
    $destinations = $db->query("SELECT * FROM featured_destinations ORDER BY sort_order")->fetchAll();
}
$fallbackDestinations = [
    ['title' => 'Colombo City', 'district' => 'Colombo', 'property_count' => 24],
    ['title' => 'Kandy Hills', 'district' => 'Kandy', 'property_count' => 18],
    ['title' => 'Galle Fort', 'district' => 'Galle', 'property_count' => 21],
    ['title' => 'Mirissa Beach', 'district' => 'Mirissa', 'property_count' => 16],
    ['title' => 'Ella Highlands', 'district' => 'Ella', 'property_count' => 20],
    ['title' => 'Nuwara Eliya', 'district' => 'Nuwara Eliya', 'property_count' => 14],
];
$destinationCards = array_slice($destinations, 0, 6);
$usedDestinationDistricts = array_map(fn($dest) => $dest['district'] ?? '', $destinationCards);
foreach ($fallbackDestinations as $fallbackDest) {
    if (count($destinationCards) >= 6) break;
    if (!in_array($fallbackDest['district'], $usedDestinationDistricts, true)) {
        $destinationCards[] = $fallbackDest;
        $usedDestinationDistricts[] = $fallbackDest['district'];
    }
}
$destinationCards[] = ['title' => 'Hikkaduwa Surf', 'district' => 'Hikkaduwa', 'property_count' => 12];
$destinationCards[] = ['title' => 'Yala Safari', 'district' => 'Yala', 'property_count' => 15];

// Active offers
$offers = [];
if ($hasOffersTable && $hasPropertiesTable) {
    $offers = $db->query("SELECT o.*, p.name as property_name FROM offers o 
        LEFT JOIN properties p ON o.property_id = p.id 
        WHERE o.status = 'active' AND o.valid_to >= CURDATE() LIMIT 3")->fetchAll();
}
$displayOffers = $offers ?: [
    [
        'title' => 'Weekend Special',
        'description' => 'Save on stylish city stays with breakfast and flexible check-in included.',
        'discount_percent' => 15,
        'property_name' => 'Colombo luxury collection',
    ],
    [
        'title' => 'Beach Getaway',
        'description' => 'Escape to tropical coastlines with handpicked beachfront resorts and villas.',
        'discount_percent' => 20,
        'property_name' => 'Southern coast escapes',
    ],
];
$offerImages = [
    'Weekend Special' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=1200&q=80',
    'Beach Getaway' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
    'default' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
];

require_once __DIR__ . '/includes/header.php';
?>
<meta name="app-url" content="<?= APP_URL ?>">
<script>window.APP_BASE = '<?= APP_URL ?>';</script>

<section class="hero-section" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80');">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-copy">
                    <p class="eyebrow"><i class="bi bi-geo-alt-fill me-2"></i> Sri Lanka</p>
                    <h1 class="hero-title">Discover <span>Luxury</span> Stays in Sri Lanka</h1>
                    <p class="hero-subtitle">Find hotels, villas and resorts across Sri Lanka with comfort, convenience and trusted hospitality.</p>
                    <div class="hero-actions">
                        <a href="<?= APP_URL ?>/properties.php" class="btn btn-primary btn-lg">Explore Stays</a>
                        <a href="#destinations" class="btn btn-outline-light btn-lg">View Destinations</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center" id="search">
            <div class="col-12 col-xl-11">
                <form action="<?= APP_URL ?>/properties.php" method="GET" class="booking-search-card" data-loading>
                    <div class="search-field search-field-with-separator">
                        <label>Destination</label>
                        <div class="search-input-group">
                            <i class="bi bi-geo-alt-fill"></i>
                            <input type="text" name="location" id="searchLocation" class="form-control" placeholder="Colombo, Kandy, Galle..." autocomplete="off">
                        </div>
                        <div id="searchSuggestions" class="search-suggestions d-none"></div>
                    </div>
                    <div class="search-field search-field-with-separator">
                        <label>Check In</label>
                        <div class="search-input-group">
                            <i class="bi bi-calendar-event"></i>
                            <input type="date" name="check_in" class="form-control" min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="search-field search-field-with-separator">
                        <label>Check Out</label>
                        <div class="search-input-group">
                            <i class="bi bi-calendar2-check"></i>
                            <input type="date" name="check_out" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                    </div>
                    <div class="search-field search-field-with-separator">
                        <label>Guests</label>
                        <div class="search-input-group">
                            <i class="bi bi-people-fill"></i>
                            <select name="guests" class="form-select">
                                <?php for ($g = 1; $g <= 6; $g++): ?>
                                <option value="<?= $g ?>"><?= $g ?> guest<?= $g > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="search-field search-button-field">
                        <button type="submit" class="btn btn-search w-100" title="Search">
                            <i class="bi bi-search"></i>
                            <span>Search</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="py-5 section-surface">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-12">
                <div class="section-heading d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <p class="section-label">Featured stays</p>
                        <h2 class="section-title mb-0">Curated places to stay</h2>
                    </div>
                    <a href="<?= APP_URL ?>/properties.php" class="text-primary fw-semibold">View all <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-4">
                    <?php foreach ($featured as $prop): ?>
                    <div class="col-6 col-md-6 col-xl-4">
                        <a href="<?= APP_URL ?>/property.php?id=<?= $prop['id'] ?>" class="text-decoration-none">
                            <div class="luxury-card property-card">
                                <div class="property-card__media">
                                    <img src="<?= getPropertyPrimaryImage($prop['image']) ?>" alt="<?= e($prop['name']) ?>" loading="lazy">
                                    <?php if ($prop['featured'] ?? false): ?>
                                    <span class="badge badge-featured"><i class="bi bi-stars me-1"></i>Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge badge-soft"><?= e($prop['property_type']) ?></span>
                                        <span class="property-price"><?= formatPrice($prop['min_price'] ?? 0) ?>/night</span>
                                    </div>
                                    <h5 class="card-title"><?= e($prop['name']) ?></h5>
                                    <p class="card-location"><i class="bi bi-geo-alt"></i> <?= e($prop['district']) ?></p>
                                    <div class="property-card-footer d-flex justify-content-between align-items-center mt-3">
                                        <span class="rating-stars">
                                            <?php $rating = max(0, min(5, (float) $prop['avg_rating'])); ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?= $i <= floor($rating) ? 'bi-star-fill' : ($i - 0.5 <= $rating ? 'bi-star-half' : 'bi-star') ?>"></i>
                                            <?php endfor; ?>
                                            <small class="text-muted ms-1">(<?= (int)$prop['review_count'] ?>)</small>
                                        </span>
                                        <span class="btn btn-sm btn-outline-primary">View details</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($featured)): ?>
                    <div class="col-12"><p class="text-muted-light">Import database.sql to see featured properties.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" id="destinations">
    <div class="container">
        <div class="section-heading text-center mb-4">
            <p class="section-label">Popular destinations</p>
            <h2 class="section-title">Explore the island from coast to highlands</h2>
        </div>
        <div class="row g-4">
            <?php
            $destinationImages = [
                'Colombo' => 'https://images.unsplash.com/photo-1656505992981-c0775b7dd59f?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTl8fGNvbG9tYm98ZW58MHx8MHx8fDA%3D',
                'Kandy' => 'https://images.unsplash.com/photo-1665849050332-8d5d7e59afb6?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8a2FuZHl8ZW58MHx8MHx8fDA%3D',
                'Galle' => 'https://images.unsplash.com/photo-1643896093807-15842d79b128?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTh8fGdhbGxlJTIwZm9ydHxlbnwwfHwwfHx8MA%3D%3D',
                'Mirissa' => 'https://images.unsplash.com/photo-1522310193626-604c5ef8be43?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fG1pcmlzc2F8ZW58MHx8MHx8fDA%3D',
                'Ella' => 'https://images.unsplash.com/photo-1566296314736-6eaac1ca0cb9?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8ZWxsYSUyMHNyaSUyMGxhbmthfGVufDB8fDB8fHww',
                'Nuwara Eliya' => 'https://d2r2v0jxjsbm0p.cloudfront.net/2020/12/10-Nuwara-Eliya-City-Tour-2.jpg',
                'Hikkaduwa' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSvukfND4oZKdU7hwCEjYoeYVwhQImVq0NWFIjDHi0s8kMBKSYU7VzCHjQ&s=10',
                'Yala' => 'https://media.istockphoto.com/id/534789901/photo/elephant.jpg?s=612x612&w=0&k=20&c=RenAVA9qVsmH5gdHuvFi6qRUdkfJ_7g8f_ob8UCuNc0=',
                'default' => 'https://images.unsplash.com/photo-1588258343756-c5667f79e1ac?w=500&h=500&fit=crop'
            ];
            foreach ($destinationCards as $dest): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= APP_URL ?>/properties.php?district=<?= urlencode($dest['district']) ?>" class="text-decoration-none">
                    <div class="dest-card">
                        <img src="<?= $destinationImages[$dest['district']] ?? $destinationImages['default'] ?>" alt="<?= e($dest['title']) ?>" loading="lazy">
                        <div class="dest-overlay">
                            <h6 class="mb-1"><?= e($dest['title']) ?></h6>
                            <small><?= e($dest['district']) ?></small>
                            <span class="dest-count"><?= isset($dest['property_count']) ? (int)$dest['property_count'] : 18 ?> properties</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 section-surface">
    <div class="container">
        <div class="section-heading text-center mb-4">
            <p class="section-label">Why choose LuxuryStay</p>
            <h2 class="section-title">Your trusted stay partner in Sri Lanka</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="info-card">
                    <div class="info-card__icon"><i class="bi bi-shield-check"></i></div>
                    <h5>Verified Properties</h5>
                    <p>Every stay is carefully reviewed for quality, comfort, and reliability.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="info-card">
                    <div class="info-card__icon"><i class="bi bi-lock-fill"></i></div>
                    <h5>Secure Booking</h5>
                    <p>Enjoy a seamless and protected reservation experience from start to finish.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="info-card">
                    <div class="info-card__icon"><i class="bi bi-tag-fill"></i></div>
                    <h5>Best Prices</h5>
                    <p>Find attractive rates and special offers for every budget and trip style.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="info-card">
                    <div class="info-card__icon"><i class="bi bi-flower3"></i></div>
                    <h5>Local Hospitality</h5>
                    <p>Experience genuine Sri Lankan warmth with locally curated stays and support.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="stats-panel">
            <div class="stat-item">
                <div class="stat-value" data-counter data-target="500" data-suffix="+">0</div>
                <div class="stat-label">Hotels</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-counter data-target="150" data-suffix="+">0</div>
                <div class="stat-label">Villas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-counter data-target="25" data-suffix="+">0</div>
                <div class="stat-label">Resorts</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-counter data-target="10000" data-suffix="+">0</div>
                <div class="stat-label">Happy Guests</div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 section-surface">
    <div class="container">
        <div class="section-heading text-center mb-4">
            <p class="section-label">Testimonials</p>
            <h2 class="section-title">What travelers say about LuxuryStay</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="testimonial-card">
                    <p>“The booking experience was effortless and the villa in Ella felt like a private retreat.”</p>
                    <div class="testimonial-author">
                        <strong>Sarah & James</strong>
                        <span>Honeymoon stay</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="testimonial-card">
                    <p>“Beautiful properties, responsive support, and stunning views from start to finish.”</p>
                    <div class="testimonial-author">
                        <strong>Ravi P.</strong>
                        <span>Business traveler</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="testimonial-card">
                    <p>“LuxuryStay made our family trip to Sri Lanka feel easy, comfortable, and memorable.”</p>
                    <div class="testimonial-author">
                        <strong>Mina K.</strong>
                        <span>Family getaway</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($offers)): ?>
<section class="py-5">
    <div class="container">
        <div class="section-heading d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="section-label">Special offers</p>
                <h2 class="section-title mb-0">Exclusive deals for your stay</h2>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ($offers as $offer): ?>
            <div class="col-md-4">
                <div class="offer-card">
                    <span class="badge badge-offer"><?= (int)$offer['discount_percent'] ?>% OFF</span>
                    <h5><?= e($offer['title']) ?></h5>
                    <p><?= e($offer['description']) ?></p>
                    <?php if ($offer['property_name']): ?>
                    <small><?= e($offer['property_name']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
