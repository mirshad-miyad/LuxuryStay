<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

ensureUserProfileSchema($pdo);
ensureOwnerProfileSchema($pdo);
ensureAdminProfileSchema($pdo);

$adminExists = $pdo->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
$adminExists->execute(['admin@luxurystay.lk']);
if (!$adminExists->fetch()) {
    $pdo->exec("INSERT INTO admins (name, email, password, phone, address, created_at) VALUES ('System Admin', 'admin@luxurystay.lk', '" . password_hash('Admin@123', PASSWORD_DEFAULT) . "', '+94 77 100 0000', 'Colombo 07', NOW())");
}

$owners = [
    [
        'name' => 'Nimali Perera',
        'email' => 'nimali@luxurystay.lk',
        'phone' => '+94 77 123 4567',
        'address' => '25, Galle Road, Colombo 03',
        'profile_image' => 'uploads/profile/owners/nimali.jpg',
        'password' => 'Owner@123',
        'company_name' => 'Blue Horizon Hotels',
        'business_description' => 'Luxury hotel operator focused on premium city stays and business travellers.',
        'status' => 'active',
    ],
    [
        'name' => 'Sajith Fernando',
        'email' => 'sajith@luxurystay.lk',
        'phone' => '+94 71 456 7890',
        'address' => '18, Beach Road, Bentota',
        'profile_image' => 'uploads/profile/owners/sajith.jpg',
        'password' => 'Owner@123',
        'company_name' => 'Ocean Breeze Villas',
        'business_description' => 'Boutique beach villa collection with private pools and serene coastal views.',
        'status' => 'active',
    ],
    [
        'name' => 'Ruwan Jayasuriya',
        'email' => 'ruwan@luxurystay.lk',
        'phone' => '+94 76 987 6543',
        'address' => '7, Tea Estate Road, Nuwara Eliya',
        'profile_image' => 'uploads/profile/owners/ruwan.jpg',
        'password' => 'Owner@123',
        'company_name' => 'Highland Retreats',
        'business_description' => 'Mountain resort group celebrated for eco stays and scenic highland hospitality.',
        'status' => 'active',
    ],
];

$ownerIds = [];
foreach ($owners as $owner) {
    $ownerCheck = $pdo->prepare("SELECT id FROM owners WHERE email = ? LIMIT 1");
    $ownerCheck->execute([$owner['email']]);
    $existingOwner = $ownerCheck->fetch();
    if ($existingOwner) {
        $ownerIds[] = (int) $existingOwner['id'];
        continue;
    }

    $stmt = $pdo->prepare("INSERT INTO owners (name, email, phone, address, profile_image, password, company_name, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $owner['name'],
        $owner['email'],
        $owner['phone'],
        $owner['address'],
        $owner['profile_image'],
        password_hash($owner['password'], PASSWORD_DEFAULT),
        $owner['company_name'],
        $owner['status'],
    ]);
    $ownerIds[] = (int) $pdo->lastInsertId();
}

$users = [
    ['name' => 'Kavindu Senanayake', 'email' => 'kavindu@example.com', 'phone' => '+94 77 111 2233', 'password' => 'User@123', 'address' => '12, Temple Road, Kandy', 'profile_image' => 'uploads/profile/users/kavindu.jpg'],
    ['name' => 'Thilini Perera', 'email' => 'thilini@example.com', 'phone' => '+94 71 222 3344', 'password' => 'User@123', 'address' => '88, Main Street, Galle', 'profile_image' => 'uploads/profile/users/thilini.jpg'],
    ['name' => 'Malith Karunaratne', 'email' => 'malith@example.com', 'phone' => '+94 76 333 4455', 'password' => 'User@123', 'address' => '5, Lake View, Colombo', 'profile_image' => 'uploads/profile/users/malith.jpg'],
    ['name' => 'Dulani Silva', 'email' => 'dulani@example.com', 'phone' => '+94 70 444 5566', 'password' => 'User@123', 'address' => '67, Park Lane, Negombo', 'profile_image' => 'uploads/profile/users/dulani.jpg'],
    ['name' => 'Nuwan Rathnayake', 'email' => 'nuwan@example.com', 'phone' => '+94 75 555 6677', 'password' => 'User@123', 'address' => '20, Hill Street, Ella', 'profile_image' => 'uploads/profile/users/nuwan.jpg'],
    ['name' => 'Ashani Jayawardena', 'email' => 'ashani@example.com', 'phone' => '+94 78 666 7788', 'password' => 'User@123', 'address' => '44, Coral Gardens, Mirissa', 'profile_image' => 'uploads/profile/users/ashani.jpg'],
    ['name' => 'Ranil Bandara', 'email' => 'ranil@example.com', 'phone' => '+94 72 777 8899', 'password' => 'User@123', 'address' => '15, Cinnamon Lane, Jaffna', 'profile_image' => 'uploads/profile/users/ranil.jpg'],
    ['name' => 'Piumi Dias', 'email' => 'piumi@example.com', 'phone' => '+94 74 888 9900', 'password' => 'User@123', 'address' => '90, Lagoon Avenue, Batticaloa', 'profile_image' => 'uploads/profile/users/piumi.jpg'],
];

$userIds = [];
foreach ($users as $user) {
    $userCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $userCheck->execute([$user['email']]);
    $existingUser = $userCheck->fetch();
    if ($existingUser) {
        $userIds[] = (int) $existingUser['id'];
        continue;
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, address, profile_image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
    $stmt->execute([
        $user['name'],
        $user['email'],
        $user['phone'],
        password_hash($user['password'], PASSWORD_DEFAULT),
        $user['address'],
        $user['profile_image'],
    ]);
    $userIds[] = (int) $pdo->lastInsertId();
}

$properties = [
    ['name' => 'Ella Eco Lodge', 'owner_index' => 2, 'description' => 'A tranquil eco stay surrounded by tea plantations and misty hills.', 'address' => '73, Ravana Road, Ella', 'city' => 'Ella', 'province' => 'Uva', 'district' => 'Badulla', 'property_type' => 'Resort', 'contact_phone' => '+94 57 555 1188', 'contact_email' => 'stay@ellalodge.lk', 'latitude' => 6.8667, 'longitude' => 81.0462, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/img_6a340ff4af41e5.00617472.webp'],
    ['name' => 'Sigiriya Nature Villa', 'owner_index' => 0, 'description' => 'A nature-inspired villa near the famous rock fortress with private dining decks.', 'address' => '14, Rock View Lane, Sigiriya', 'city' => 'Sigiriya', 'province' => 'Central', 'district' => 'Matale', 'property_type' => 'Villa', 'contact_phone' => '+94 66 223 4455', 'contact_email' => 'villa@sigiriya.lk', 'latitude' => 7.9532, 'longitude' => 80.7609, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/img_6a3488d1276ed4.86291310.webp'],
    ['name' => 'Trincomalee Ocean View Resort', 'owner_index' => 1, 'description' => 'Modern beachfront resort with ocean panoramas and sunset dining.', 'address' => '9, Coral Bay, Trincomalee', 'city' => 'Trincomalee', 'province' => 'Eastern', 'district' => 'Trincomalee', 'property_type' => 'Resort', 'contact_phone' => '+94 26 223 1188', 'contact_email' => 'hello@oceanview.lk', 'latitude' => 8.5879, 'longitude' => 81.2152, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/img_6a348a92ab12b8.17141509.jpg'],
    ['name' => 'Kandy Heritage Hotel', 'owner_index' => 0, 'description' => 'A heritage-style hotel near the hill capital with candlelit dining and culture tours.', 'address' => '4, Queen Street, Kandy', 'city' => 'Kandy', 'province' => 'Central', 'district' => 'Kandy', 'property_type' => 'Hotel', 'contact_phone' => '+94 81 220 3388', 'contact_email' => 'resv@kandyheritage.lk', 'latitude' => 7.2906, 'longitude' => 80.6337, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/img_6a348ae3118da0.49067607.jpg'],
    ['name' => 'Arugam Bay Surf Camp', 'owner_index' => 1, 'description' => 'Surf-friendly stay with easy beach access and laid-back island energy.', 'address' => '31, Main Point Road, Arugam Bay', 'city' => 'Arugam Bay', 'province' => 'Eastern', 'district' => 'Ampara', 'property_type' => 'Guest House', 'contact_phone' => '+94 63 223 4766', 'contact_email' => 'camp@arugambay.lk', 'latitude' => 6.8407, 'longitude' => 81.8368, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/img_6a348b119e0d32.36687007.jpg'],
    ['name' => 'Nuwara Eliya Tea Cottage', 'owner_index' => 2, 'description' => 'An intimate tea estate cottage with fireplace evenings and scenic breakfasts.', 'address' => '12, Tea Factory Road, Nuwara Eliya', 'city' => 'Nuwara Eliya', 'province' => 'Central', 'district' => 'Nuwara Eliya', 'property_type' => 'Guest House', 'contact_phone' => '+94 52 223 6611', 'contact_email' => 'cottage@nuwaraeliya.lk', 'latitude' => 6.9497, 'longitude' => 80.7891, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/img_6a348c4b759561.73392151.webp'],
    ['name' => 'Pasikuda Beach Resort', 'owner_index' => 1, 'description' => 'Relaxing beachfront resort ideal for family holidays and long weekends.', 'address' => '16, Lagoon Road, Pasikuda', 'city' => 'Pasikuda', 'province' => 'Eastern', 'district' => 'Batticaloa', 'property_type' => 'Resort', 'contact_phone' => '+94 65 112 3344', 'contact_email' => 'stay@pasikuda.lk', 'latitude' => 7.6861, 'longitude' => 81.6946, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/img_6a348cc0d80f04.17192664.jpg'],
    ['name' => 'Hiriketiya Boutique Villa', 'owner_index' => 1, 'description' => 'A stylish boutique villa for slow mornings, surf days and sunset dinners.', 'address' => '27, Hiriketiya Road, Dikwella', 'city' => 'Hiriketiya', 'province' => 'Southern', 'district' => 'Matara', 'property_type' => 'Villa', 'contact_phone' => '+94 47 889 3311', 'contact_email' => 'villa@hiriketiya.lk', 'latitude' => 5.9858, 'longitude' => 80.3668, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/1/e93eb03e.avif'],
    ['name' => 'Yala Safari Lodge', 'owner_index' => 0, 'description' => 'A wildlife-focused lodge with jeep safaris and open-air decks.', 'address' => '44, Safari Route, Tissamaharama', 'city' => 'Tissamaharama', 'province' => 'Southern', 'district' => 'Hambantota', 'property_type' => 'Hotel', 'contact_phone' => '+94 47 990 1188', 'contact_email' => 'lodge@yala.lk', 'latitude' => 6.3718, 'longitude' => 81.5160, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/3/e93eb03e.avif'],
    ['name' => 'Bentota River Retreat', 'owner_index' => 1, 'description' => 'Riverside accommodation with boat safaris and private spa experiences.', 'address' => '88, River Bank Road, Bentota', 'city' => 'Bentota', 'province' => 'Southern', 'district' => 'Kalutara', 'property_type' => 'Resort', 'contact_phone' => '+94 34 118 1022', 'contact_email' => 'retreat@bentota.lk', 'latitude' => 6.4207, 'longitude' => 79.9950, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/10/e93eb03e.avif'],
    ['name' => 'Galle Fort Courtyard Hotel', 'owner_index' => 0, 'description' => 'A refined heritage hotel inside Galle Fort, with elegant rooms and a peaceful inner courtyard.', 'address' => '18, Church Street, Galle Fort', 'city' => 'Galle', 'province' => 'Southern', 'district' => 'Galle', 'property_type' => 'Hotel', 'contact_phone' => '+94 91 224 5501', 'contact_email' => 'stay@gallecourtyard.lk', 'latitude' => 6.0261, 'longitude' => 80.2170, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/galle-fort-courtyard-hotel.jpg'],
    ['name' => 'Mirissa Cliffside Villa', 'owner_index' => 1, 'description' => 'An intimate ocean-view villa with an infinity pool, tropical gardens and whale-watching access.', 'address' => '42, Harbour Road, Mirissa', 'city' => 'Mirissa', 'province' => 'Southern', 'district' => 'Matara', 'property_type' => 'Villa', 'contact_phone' => '+94 41 226 8190', 'contact_email' => 'hello@mirissacliffside.lk', 'latitude' => 5.9491, 'longitude' => 80.4716, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/3/img_6a4a9d040aabc4.05966654.jpg'],
    ['name' => 'Wilpattu Wilderness Camp', 'owner_index' => 2, 'description' => 'A comfortable safari camp on the edge of Wilpattu, designed for unforgettable wildlife escapes.', 'address' => '25, Wilpattu Junction, Nochchiyagama', 'city' => 'Wilpattu', 'province' => 'North Western', 'district' => 'Puttalam', 'property_type' => 'Resort', 'contact_phone' => '+94 32 225 7144', 'contact_email' => 'reservations@wilpattucamp.lk', 'latitude' => 8.4554, 'longitude' => 80.0641, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/9/img_6a4a9f0b799364.97217717.jpg'],
    ['name' => 'Jaffna Lagoon House', 'owner_index' => 0, 'description' => 'A welcoming lagoon-side guest house where northern Sri Lankan culture and comfort meet.', 'address' => '61, Lagoon View Road, Jaffna', 'city' => 'Jaffna', 'province' => 'Northern', 'district' => 'Jaffna', 'property_type' => 'Guest House', 'contact_phone' => '+94 21 222 4678', 'contact_email' => 'stay@jaffnalagoon.lk', 'latitude' => 9.6615, 'longitude' => 80.0255, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/jaffna-lagoon-house.jpg'],
    ['name' => 'Weligama Bay Boutique Stay', 'owner_index' => 1, 'description' => 'A chic coastal retreat just steps from Weligama Bay, ideal for surf trips and relaxed getaways.', 'address' => '8, Bay View Lane, Weligama', 'city' => 'Weligama', 'province' => 'Southern', 'district' => 'Matara', 'property_type' => 'Hotel', 'contact_phone' => '+94 41 225 1092', 'contact_email' => 'book@weligamabay.lk', 'latitude' => 5.9742, 'longitude' => 80.4298, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/12/img_6a4a9ffa25b688.08760128.jpg'],
    ['name' => 'Knuckles Mountain Retreat', 'owner_index' => 2, 'description' => 'A secluded highland retreat with misty mountain views, guided hikes and fireside dining.', 'address' => '16, Riverston Road, Matale', 'city' => 'Matale', 'province' => 'Central', 'district' => 'Matale', 'property_type' => 'Resort', 'contact_phone' => '+94 66 224 7810', 'contact_email' => 'escape@knucklesretreat.lk', 'latitude' => 7.5312, 'longitude' => 80.7946, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/13/img_6a4b06f55657e9.01726926.jpg'],
    ['name' => 'Kalpitiya Kite Beach Resort', 'owner_index' => 1, 'description' => 'A breezy beach resort overlooking Kalpitiya Lagoon, perfect for kite-surfing and sunset escapes.', 'address' => '34, Lagoon Drive, Kalpitiya', 'city' => 'Kalpitiya', 'province' => 'North Western', 'district' => 'Puttalam', 'property_type' => 'Resort', 'contact_phone' => '+94 32 226 5090', 'contact_email' => 'stay@kalpitiyakite.lk', 'latitude' => 8.2332, 'longitude' => 79.7667, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/14/img_6a4b0eb6d82814.84705688.jpg'],
    ['name' => 'Haputale Tea Garden Villa', 'owner_index' => 2, 'description' => 'A cosy villa surrounded by tea gardens, with valley views and freshly prepared local cuisine.', 'address' => '10, Station Road, Haputale', 'city' => 'Haputale', 'province' => 'Uva', 'district' => 'Badulla', 'property_type' => 'Villa', 'contact_phone' => '+94 57 226 4300', 'contact_email' => 'welcome@haputalevilla.lk', 'latitude' => 6.7659, 'longitude' => 80.9518, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/15/img_6a4b2cb4eba5b9.39600898.jpg'],
    ['name' => 'Colombo Skyline Suites', 'owner_index' => 0, 'description' => 'Contemporary city suites with skyline views, rooftop dining and easy access to Colombo attractions.', 'address' => '72, Galle Road, Colombo 03', 'city' => 'Colombo', 'province' => 'Western', 'district' => 'Colombo', 'property_type' => 'Hotel', 'contact_phone' => '+94 11 245 7012', 'contact_email' => 'reservations@colomboskyline.lk', 'latitude' => 6.9061, 'longitude' => 79.8539, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/10/img_6a4a99c9c2f0c3.04528371.jpg'],
    ['name' => 'Dambulla Lakeview Resort', 'owner_index' => 0, 'description' => 'A serene lakeside resort near Dambulla, offering spacious rooms, birdwatching and sunset dining.', 'address' => '29, Kandalama Road, Dambulla', 'city' => 'Dambulla', 'province' => 'Central', 'district' => 'Matale', 'property_type' => 'Resort', 'contact_phone' => '+94 66 228 4015', 'contact_email' => 'stay@dambullalakeview.lk', 'latitude' => 7.8742, 'longitude' => 80.6511, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/1/images (1).jpg'],
    ['name' => 'Ratnapura Rainforest Lodge', 'owner_index' => 2, 'description' => 'An eco-friendly lodge surrounded by rainforest greenery, with guided walks and quiet river views.', 'address' => '45, Forest Edge Road, Ratnapura', 'city' => 'Ratnapura', 'province' => 'Sabaragamuwa', 'district' => 'Ratnapura', 'property_type' => 'Guest House', 'contact_phone' => '+94 45 223 6781', 'contact_email' => 'welcome@ratnapuralodge.lk', 'latitude' => 6.6828, 'longitude' => 80.3992, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/1/images (2).jpg'],
    ['name' => 'Batticaloa Lagoon Villa', 'owner_index' => 1, 'description' => 'A relaxed private villa overlooking Batticaloa Lagoon, with fresh seafood and peaceful water views.', 'address' => '14, Lagoon Park, Batticaloa', 'city' => 'Batticaloa', 'province' => 'Eastern', 'district' => 'Batticaloa', 'property_type' => 'Villa', 'contact_phone' => '+94 65 222 9154', 'contact_email' => 'book@batticaloalagoon.lk', 'latitude' => 7.7290, 'longitude' => 81.6976, 'status' => 'approved', 'is_active' => 1, 'image' => 'uploads/properties/batticaloa-lagoon-villa-v2.jpg'],
];

$propertyIds = [];
$roomIds = [];
$amenityIds = [1, 2, 3, 4, 5, 6, 7, 8];
foreach ($properties as $property) {
    $propertyCheck = $pdo->prepare("SELECT id FROM properties WHERE name = ? LIMIT 1");
    $propertyCheck->execute([$property['name']]);
    $existingProperty = $propertyCheck->fetch();
    if ($existingProperty) {
        $propertyId = (int) $existingProperty['id'];
        $propertyIds[] = $propertyId;
    } else {
        $stmt = $pdo->prepare("INSERT INTO properties (owner_id, name, description, address, city, province, district, property_type, contact_phone, contact_email, latitude, longitude, status, is_active, avg_rating, review_count, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW(), NOW())");
        $stmt->execute([
            $ownerIds[$property['owner_index']] ?? $ownerIds[0] ?? 0,
            $property['name'],
            $property['description'],
            $property['address'],
            $property['city'],
            $property['province'],
            $property['district'],
            $property['property_type'],
            $property['contact_phone'],
            $property['contact_email'],
            $property['latitude'],
            $property['longitude'],
            $property['status'],
            $property['is_active'],
        ]);
        $propertyId = (int) $pdo->lastInsertId();
        $propertyIds[] = $propertyId;

        $imageStmt = $pdo->prepare("INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES (?, ?, 1, 0)");
        $imageStmt->execute([$propertyId, $property['image']]);
    }

    $roomCheck = $pdo->prepare("SELECT id FROM rooms WHERE property_id = ? ORDER BY id");
    $roomCheck->execute([$propertyId]);
    $propertyRoomIds = [];
    while ($roomRow = $roomCheck->fetch(PDO::FETCH_ASSOC)) {
        $propertyRoomIds[] = (int) $roomRow['id'];
    }

    if (!$propertyRoomIds) {
        $roomPrices = [120000, 165000, 190000, 250000, 145000, 220000, 175000, 300000, 210000, 260000, 285000, 240000, 195000, 150000, 225000, 205000, 185000, 170000, 265000, 210000, 155000, 230000];
        $priceIndex = count($propertyIds) - 1;
        $roomPrice = $roomPrices[$priceIndex % count($roomPrices)];
        $roomNames = ['Standard Room', 'Deluxe Suite'];
        foreach ($roomNames as $idx => $roomName) {
            $price = $roomPrice + ($idx * 40000);
            $roomStmt = $pdo->prepare("INSERT INTO rooms (property_id, name, description, price_per_night, weekend_price, max_guests, inventory, bed_type, status) VALUES (?, ?, ?, ?, ?, ?, 2, 'King', 'active')");
            $roomStmt->execute([$propertyId, $roomName, 'Comfortable stay with premium amenities.', $price, $price + 8000, 2 + $idx]);
            $propertyRoomIds[] = (int) $pdo->lastInsertId();
        }
    }

    $roomIds = array_merge($roomIds, $propertyRoomIds);
}

$dates = [];
$start = new DateTime('2026-06-01');
$end = new DateTime('2026-07-31');
for ($date = clone $start; $date <= $end; $date->modify('+1 day')) {
    $dates[] = $date->format('Y-m-d');
}

$statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'rejected'];
$bookingCounter = 0;
$reviewRows = [];
$reviewComments = [
    'The stay was immaculate and the staff were genuinely welcoming.',
    'Excellent location and comfortable rooms for a relaxing getaway.',
    'The food and service helped make the trip special.',
    'WiFi was excellent and the entire property felt very clean.',
    'Beautiful setting though breakfast could be improved a bit.',
    'The staff were friendly, but the room could have been cleaner.',
    'Great value for money and convenient access to local attractions.',
    'The pool was lovely and the property felt very comfortable.',
];

mt_srand(42);
for ($i = 0; $i < 80; $i++) {
    $userId = $userIds[$i % max(1, count($userIds))];
    $propertyId = $propertyIds[$i % max(1, count($propertyIds))];
    $roomId = $roomIds[(($i * 2) + ($i % 3)) % max(1, count($roomIds))];
    $checkIn = $dates[array_rand($dates)];
    $nights = 2 + ($i % 5);
    $checkOut = date('Y-m-d', strtotime($checkIn . ' +' . $nights . ' days'));
    $status = $statuses[$i % count($statuses)];
    if ($status === 'completed') {
        $paymentStatus = 'paid';
    } elseif ($status === 'confirmed') {
        $paymentStatus = 'paid';
    } elseif ($status === 'cancelled') {
        $paymentStatus = 'refunded';
    } else {
        $paymentStatus = 'pending';
    }

    $roomStmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE id = ?");
    $roomStmt->execute([$roomId]);
    $roomPrice = (float) $roomStmt->fetchColumn();
    $amount = round($roomPrice * $nights + 3000 + ($i % 4) * 2500, 2);
    $createdAt = date('Y-m-d H:i:s', strtotime($checkIn . ' -' . ($i % 6) . ' days'));

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, property_id, check_in, check_out, guests, total_amount, status, payment_status, payment_method, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $roomId, $propertyId, $checkIn, $checkOut, 1 + ($i % 3), $amount, $status, $paymentStatus, 'Card', $createdAt, $createdAt]);
    $bookingId = (int) $pdo->lastInsertId();
    $bookingCounter++;

    if ($status === 'completed' && count($reviewRows) < 40) {
        $reviewRows[] = [$bookingId, $userId, $propertyId, 4 + ($i % 2), $reviewComments[$i % count($reviewComments)]];
    }
}

foreach ($reviewRows as $index => $review) {
    [$bookingId, $userId, $propertyId, $rating, $comment] = $review;
    $pdo->prepare("INSERT INTO reviews (user_id, property_id, booking_id, rating, comment, status, created_at) VALUES (?, ?, ?, ?, ?, 'approved', NOW())")->execute([$userId, $propertyId, $bookingId, $rating, $comment]);
}

$reviewCounts = $pdo->query("SELECT property_id, COUNT(*) AS total, AVG(rating) AS avg_rating FROM reviews GROUP BY property_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($reviewCounts as $row) {
    $pdo->prepare("UPDATE properties SET review_count = ?, avg_rating = ? WHERE id = ?")->execute([(int) $row['total'], round((float) $row['avg_rating'], 2), (int) $row['property_id']]);
}

$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (1, 'WiFi', 'bi-wifi') ON DUPLICATE KEY UPDATE name = VALUES(name)");
$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (2, 'Pool', 'bi-water') ON DUPLICATE KEY UPDATE name = VALUES(name)");
$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (3, 'Parking', 'bi-car-front') ON DUPLICATE KEY UPDATE name = VALUES(name)");
$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (4, 'Breakfast', 'bi-cup-hot') ON DUPLICATE KEY UPDATE name = VALUES(name)");
$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (5, 'Spa', 'bi-flower2') ON DUPLICATE KEY UPDATE name = VALUES(name)");
$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (6, 'Beach Access', 'bi-umbrella') ON DUPLICATE KEY UPDATE name = VALUES(name)");
$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (7, 'Air Conditioning', 'bi-snow') ON DUPLICATE KEY UPDATE name = VALUES(name)");
$pdo->exec("INSERT INTO amenities (id, name, icon) VALUES (8, 'Mountain View', 'bi-tree') ON DUPLICATE KEY UPDATE name = VALUES(name)");

$propertyAmenityStmt = $pdo->prepare("INSERT IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
foreach ($propertyIds as $propertyId) {
    $selected = array_slice($amenityIds, 0, 3 + ($propertyId % 3));
    foreach ($selected as $amenityId) {
        $propertyAmenityStmt->execute([$propertyId, $amenityId]);
    }
}

$pdo->exec("CREATE TABLE IF NOT EXISTS demo_seed_log (id INT AUTO_INCREMENT PRIMARY KEY, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("INSERT INTO demo_seed_log (created_at) VALUES (NOW())");

$seedSqlPath = __DIR__ . '/luxurystay_demo_seed.sql';
file_put_contents($seedSqlPath, "-- Generated demo seed data for LuxuryStay\n");
$tables = ['admins', 'owners', 'users', 'properties', 'property_images', 'rooms', 'bookings', 'reviews', 'property_amenities'];
foreach ($tables as $table) {
    $pkQuery = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $pkRow = $pkQuery->fetch(PDO::FETCH_ASSOC);
    $orderColumn = $pkRow['Column_name'] ?? null;
    $stmt = $pdo->query($orderColumn ? "SELECT * FROM `$table` ORDER BY `$orderColumn`" : "SELECT * FROM `$table`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        continue;
    }
    $columns = array_keys($rows[0]);
    foreach ($rows as $row) {
        $values = [];
        foreach ($columns as $column) {
            $value = $row[$column];
            if ($value === null) {
                $values[] = 'NULL';
            } elseif (is_int($value) || is_float($value)) {
                $values[] = (string) $value;
            } else {
                $values[] = "'" . str_replace("'", "\\'", (string) $value) . "'";
            }
        }
        file_put_contents($seedSqlPath, "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n", FILE_APPEND);
    }
}

echo "Seed data inserted and SQL saved to $seedSqlPath\n";
